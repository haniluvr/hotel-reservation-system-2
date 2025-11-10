package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.TaskItem;
import com.belmonthotel.admin.utils.DatabaseConnection;
import com.belmonthotel.admin.utils.SessionManager;
import com.belmonthotel.admin.utils.StatisticsService;
import com.belmonthotel.admin.utils.TaskQueueService;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Scene;
import javafx.scene.chart.*;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.stage.Stage;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Map;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/**
 * Controller for the main dashboard.
 */
public class DashboardController {
    @FXML
    private BorderPane mainContainer;

    @FXML
    private Label userNameLabel;

    @FXML
    private Button dashboardBtn;

    @FXML
    private Button bookingsBtn;

    @FXML
    private Button roomsBtn;

    @FXML
    private Button usersBtn;

    @FXML
    private Button paymentsBtn;

    @FXML
    private Button reportsBtn;

    @FXML
    private VBox overviewContent;

    @FXML
    private Label totalBookingsLabel;

    @FXML
    private Label pendingBookingsLabel;

    @FXML
    private Label confirmedBookingsLabel;

    @FXML
    private Label totalRevenueLabel;

    @FXML
    private Label activeHotelsLabel;

    @FXML
    private Label activeUsersLabel;

    @FXML
    private Label todaysBookingsLabel;

    @FXML
    private Label todaysRevenueLabel;

    @FXML
    private Label upcomingCheckInsLabel;

    @FXML
    private Label upcomingCheckOutsLabel;

    @FXML
    private Label occupancyPercentageLabel;

    @FXML
    private Label averageBookingValueLabel;

    @FXML
    private Label lastUpdatedLabel;

    @FXML
    private Button refreshBtn;

    @FXML
    private CheckBox autoRefreshCheckBox;

    @FXML
    private ImageView logoImageView;

    @FXML
    private VBox chartsContainer;

    @FXML
    private VBox recentActivityContainer;

    @FXML
    private Label pendingTasksLabel;

    @FXML
    private Label currentTaskLabel;

    @FXML
    private Button processQueueBtn;

    @FXML
    private TableView<TaskItem> taskHistoryTable;

    @FXML
    private TableColumn<TaskItem, String> taskTypeCol;

    @FXML
    private TableColumn<TaskItem, String> taskPriorityCol;

    @FXML
    private TableColumn<TaskItem, String> taskStatusCol;

    @FXML
    private TableColumn<TaskItem, String> taskDescriptionCol;

    @FXML
    private TableColumn<TaskItem, String> taskResultCol;

    private ScheduledExecutorService scheduler;
    private boolean autoRefreshEnabled = false;
    private TaskQueueService taskQueueService;

    @FXML
    private void initialize() {
        // Load logo image if available
        loadLogo();

        // Set user name
        SessionManager session = SessionManager.getInstance();
        if (session.isLoggedIn()) {
            userNameLabel.setText("Welcome, " + session.getUserName());
        }

        // Set active button
        setActiveButton(dashboardBtn);

        // Initialize scheduler for auto-refresh
        scheduler = Executors.newScheduledThreadPool(1);

        // Initialize Task Queue Service (PriorityQueue)
        taskQueueService = TaskQueueService.getInstance();
        initializeTaskQueue();

        // Set button actions
        if (refreshBtn != null) {
            refreshBtn.setOnAction(e -> loadOverviewDataAsync());
        }
        if (autoRefreshCheckBox != null) {
            autoRefreshCheckBox.setOnAction(e -> {
                autoRefreshEnabled = autoRefreshCheckBox.isSelected();
                if (autoRefreshEnabled) {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            });
        }
        if (processQueueBtn != null) {
            processQueueBtn.setOnAction(e -> taskQueueService.processQueue());
        }

        dashboardBtn.setOnAction(e -> showOverview());
        bookingsBtn.setOnAction(e -> loadModule("/fxml/bookings.fxml"));
        roomsBtn.setOnAction(e -> loadModule("/fxml/rooms.fxml"));
        usersBtn.setOnAction(e -> loadModule("/fxml/users.fxml"));
        paymentsBtn.setOnAction(e -> loadModule("/fxml/payments.fxml"));
        reportsBtn.setOnAction(e -> loadModule("/fxml/reports.fxml"));

        // Load overview data asynchronously
        loadOverviewDataAsync();
        loadCharts();
        loadRecentActivity();
        
        // Add sample tasks to queue
        addSampleTasks();
    }
    
    /**
     * Initialize Task Queue UI and set up callbacks.
     */
    private void initializeTaskQueue() {
        if (taskHistoryTable == null) return;
        
        // Configure table columns
        taskTypeCol.setCellValueFactory(cellData -> 
            new javafx.beans.property.SimpleStringProperty(cellData.getValue().getType().getDisplayName()));
        taskPriorityCol.setCellValueFactory(cellData -> 
            new javafx.beans.property.SimpleStringProperty(cellData.getValue().getPriority().name()));
        taskStatusCol.setCellValueFactory(cellData -> 
            new javafx.beans.property.SimpleStringProperty(cellData.getValue().getStatus().name()));
        taskDescriptionCol.setCellValueFactory(cellData -> 
            new javafx.beans.property.SimpleStringProperty(cellData.getValue().getDescription()));
        taskResultCol.setCellValueFactory(cellData -> {
            String result = cellData.getValue().getResult();
            return new javafx.beans.property.SimpleStringProperty(result != null ? result : "");
        });
        
        // Bind task history to table
        taskHistoryTable.setItems(taskQueueService.getTaskHistory());
        
        // Set up callback for task completion
        taskQueueService.setOnTaskCompleted(task -> {
            Platform.runLater(() -> {
                updateTaskQueueUI();
            });
        });
        
        // Update UI periodically
        scheduler.scheduleWithFixedDelay(() -> {
            Platform.runLater(() -> {
                updateTaskQueueUI();
            });
        }, 0, 1, TimeUnit.SECONDS);
    }
    
    /**
     * Update Task Queue UI labels.
     */
    private void updateTaskQueueUI() {
        if (pendingTasksLabel != null) {
            pendingTasksLabel.setText("Pending Tasks: " + taskQueueService.getPendingTaskCount());
        }
        if (currentTaskLabel != null) {
            TaskItem current = taskQueueService.getCurrentTask();
            if (current != null) {
                currentTaskLabel.setText("Current Task: " + current.getType().getDisplayName() + 
                    " [" + current.getPriority().name() + "]");
            } else {
                currentTaskLabel.setText("Current Task: None");
            }
        }
    }
    
    /**
     * Add sample tasks to the queue for demonstration.
     */
    private void addSampleTasks() {
        // Check for today's check-ins (URGENT)
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT COUNT(*) as count FROM reservations WHERE DATE(check_in_date) = CURDATE() AND status = 'confirmed'")) {
            ResultSet rs = stmt.executeQuery();
            if (rs.next() && rs.getInt("count") > 0) {
                taskQueueService.addTask(new TaskItem(
                    TaskItem.TaskType.CHECK_IN_REMINDER,
                    TaskItem.TaskPriority.URGENT,
                    "Send check-in reminders for " + rs.getInt("count") + " guest(s) checking in today"
                ));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        
        // Add normal priority tasks
        taskQueueService.addTask(new TaskItem(
            TaskItem.TaskType.STATISTICS_UPDATE,
            TaskItem.TaskPriority.NORMAL,
            "Update dashboard statistics"
        ));
        
        taskQueueService.addTask(new TaskItem(
            TaskItem.TaskType.DATA_SYNC,
            TaskItem.TaskPriority.LOW,
            "Synchronize data with external systems"
        ));
        
        // Update UI
        updateTaskQueueUI();
    }

    /**
     * Load overview data asynchronously using JavaFX Task.
     */
    private void loadOverviewDataAsync() {
        Task<Void> loadTask = new Task<Void>() {
            @Override
            protected Void call() throws Exception {
                // Load all statistics
                loadOverviewData();
                return null;
            }

            @Override
            protected void succeeded() {
                updateLastUpdatedLabel();
            }

            @Override
            protected void failed() {
                Throwable exception = getException();
                exception.printStackTrace();
                Platform.runLater(() -> {
                    showAlert("Error", "Failed to load dashboard data: " + exception.getMessage());
                });
            }
        };

        new Thread(loadTask).start();
    }

    /**
     * Start auto-refresh (every 30 seconds).
     */
    private void startAutoRefresh() {
        scheduler.scheduleAtFixedRate(() -> {
            Platform.runLater(() -> loadOverviewDataAsync());
        }, 30, 30, TimeUnit.SECONDS);
    }

    /**
     * Stop auto-refresh.
     */
    private void stopAutoRefresh() {
        // Scheduler will continue but we check the flag
    }

    /**
     * Load logo image if it exists.
     */
    private void loadLogo() {
        try {
            java.io.InputStream logoStream = getClass().getResourceAsStream("/images/logo.png");
            if (logoStream != null) {
                Image logo = new Image(logoStream);
                if (!logo.isError()) {
                    logoImageView.setImage(logo);
                    logoImageView.setVisible(true);
                    logoImageView.setManaged(true);
                    System.out.println("Logo loaded successfully");
                } else {
                    System.err.println("Logo image failed to load: " + logo.getException());
                }
                logoStream.close();
            } else {
                System.out.println("Logo image file not found at /images/logo.png");
                System.out.println("Please add the logo image to: admin-panel/src/main/resources/images/logo.png");
            }
        } catch (Exception e) {
            // Logo not found, keep it hidden
            System.err.println("Error loading logo: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Load overview data and display metrics.
     */
    private void loadOverviewData() {
        Platform.runLater(() -> {
            try (Connection conn = DatabaseConnection.getConnection()) {
                // Total bookings
                String totalBookingsQuery = "SELECT COUNT(*) as count FROM reservations";
                try (PreparedStatement stmt = conn.prepareStatement(totalBookingsQuery);
                     ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        totalBookingsLabel.setText(String.valueOf(rs.getInt("count")));
                    }
                }

                // Pending bookings
                String pendingQuery = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
                try (PreparedStatement stmt = conn.prepareStatement(pendingQuery);
                     ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        pendingBookingsLabel.setText(String.valueOf(rs.getInt("count")));
                    }
                }

                // Confirmed bookings
                String confirmedQuery = "SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'";
                try (PreparedStatement stmt = conn.prepareStatement(confirmedQuery);
                     ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        confirmedBookingsLabel.setText(String.valueOf(rs.getInt("count")));
                    }
                }

                // Total revenue (from confirmed/completed reservations)
                String revenueQuery = "SELECT COALESCE(SUM(total_amount), 0) as total FROM reservations WHERE status IN ('confirmed', 'completed')";
                try (PreparedStatement stmt = conn.prepareStatement(revenueQuery);
                     ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        double revenue = rs.getDouble("total");
                        totalRevenueLabel.setText(String.format("₱%.2f", revenue));
                    }
                }

                // Total rooms
                String roomsQuery = "SELECT COUNT(*) as count FROM rooms WHERE is_active = 1";
                try (PreparedStatement stmt = conn.prepareStatement(roomsQuery);
                     ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        activeHotelsLabel.setText(String.valueOf(rs.getInt("count")));
                    }
                }

                // Active users
                String usersQuery = "SELECT COUNT(*) as count FROM users";
                try (PreparedStatement stmt = conn.prepareStatement(usersQuery);
                     ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        activeUsersLabel.setText(String.valueOf(rs.getInt("count")));
                    }
                }

                // Today's bookings
                if (todaysBookingsLabel != null) {
                    todaysBookingsLabel.setText(String.valueOf(StatisticsService.getTodaysBookings()));
                }

                // Today's revenue
                if (todaysRevenueLabel != null) {
                    todaysRevenueLabel.setText(String.format("₱%.2f", StatisticsService.getTodaysRevenue()));
                }

                // Upcoming check-ins
                if (upcomingCheckInsLabel != null) {
                    upcomingCheckInsLabel.setText(String.valueOf(StatisticsService.getUpcomingCheckIns()));
                }

                // Upcoming check-outs
                if (upcomingCheckOutsLabel != null) {
                    upcomingCheckOutsLabel.setText(String.valueOf(StatisticsService.getUpcomingCheckOuts()));
                }

                // Occupancy percentage
                if (occupancyPercentageLabel != null) {
                    double occupancy = StatisticsService.getOccupancyPercentage();
                    occupancyPercentageLabel.setText(String.format("%.1f%%", occupancy));
                }

                // Average booking value
                if (averageBookingValueLabel != null) {
                    double avg = StatisticsService.getAverageBookingValue();
                    averageBookingValueLabel.setText(String.format("₱%.2f", avg));
                }

                // Load charts
                loadCharts();

                // Load recent activity
                loadRecentActivity();

            } catch (SQLException e) {
                e.printStackTrace();
                // Set default values on error
                totalBookingsLabel.setText("0");
                pendingBookingsLabel.setText("0");
                confirmedBookingsLabel.setText("0");
                totalRevenueLabel.setText("₱0.00");
                activeHotelsLabel.setText("0");
                activeUsersLabel.setText("0");
            }
        });
    }

    /**
     * Load and display charts.
     */
    private void loadCharts() {
        if (chartsContainer == null) return;

        Platform.runLater(() -> {
            chartsContainer.getChildren().clear();

            // Revenue Chart
            LineChart<String, Number> revenueChart = createRevenueChart();
            if (revenueChart != null) {
                TitledPane revenuePane = new TitledPane("Revenue Trends (Last 30 Days)", revenueChart);
                revenuePane.setExpanded(true);
                chartsContainer.getChildren().add(revenuePane);
            }

            // Booking Status Pie Chart
            PieChart statusChart = createStatusPieChart();
            if (statusChart != null) {
                TitledPane statusPane = new TitledPane("Booking Status Distribution", statusChart);
                statusPane.setExpanded(true);
                chartsContainer.getChildren().add(statusPane);
            }
        });
    }

    /**
     * Create revenue line chart.
     */
    private LineChart<String, Number> createRevenueChart() {
        try {
            CategoryAxis xAxis = new CategoryAxis();
            NumberAxis yAxis = new NumberAxis();
            yAxis.setLabel("Revenue (₱)");
            LineChart<String, Number> chart = new LineChart<>(xAxis, yAxis);
            chart.setTitle("Daily Revenue");
            chart.setLegendVisible(false);

            XYChart.Series<String, Number> series = new XYChart.Series<>();
            List<Map<String, Object>> data = StatisticsService.getRevenueChartData(30);

            for (Map<String, Object> point : data) {
                java.sql.Date date = (java.sql.Date) point.get("date");
                double revenue = (Double) point.get("revenue");
                String dateStr = date.toLocalDate().format(DateTimeFormatter.ofPattern("MM/dd"));
                series.getData().add(new XYChart.Data<>(dateStr, revenue));
            }

            chart.getData().add(series);
            chart.setPrefHeight(300);
            return chart;
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }

    /**
     * Create booking status pie chart.
     */
    private PieChart createStatusPieChart() {
        try {
            PieChart chart = new PieChart();
            Map<String, Integer> distribution = StatisticsService.getBookingStatusDistribution();

            for (Map.Entry<String, Integer> entry : distribution.entrySet()) {
                PieChart.Data slice = new PieChart.Data(
                    entry.getKey().substring(0, 1).toUpperCase() + entry.getKey().substring(1),
                    entry.getValue()
                );
                chart.getData().add(slice);
            }

            chart.setPrefHeight(300);
            return chart;
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }

    /**
     * Load recent activity section.
     */
    private void loadRecentActivity() {
        if (recentActivityContainer == null) return;

        Platform.runLater(() -> {
            recentActivityContainer.getChildren().clear();

            // Recent Bookings
            Label bookingsTitle = new Label("Recent Bookings");
            bookingsTitle.setStyle("-fx-font-size: 16px; -fx-font-weight: bold;");
            recentActivityContainer.getChildren().add(bookingsTitle);

            TableView<RecentBooking> bookingsTable = new TableView<>();
            bookingsTable.setPrefHeight(200);

            TableColumn<RecentBooking, String> resNumCol = new TableColumn<>("Reservation #");
            resNumCol.setCellValueFactory(new PropertyValueFactory<>("reservationNumber"));
            resNumCol.setPrefWidth(150);

            TableColumn<RecentBooking, String> guestCol = new TableColumn<>("Guest");
            guestCol.setCellValueFactory(new PropertyValueFactory<>("guestName"));
            guestCol.setPrefWidth(150);

            TableColumn<RecentBooking, String> checkInCol = new TableColumn<>("Check-in");
            checkInCol.setCellValueFactory(new PropertyValueFactory<>("checkInDate"));
            checkInCol.setPrefWidth(100);

            TableColumn<RecentBooking, String> statusCol = new TableColumn<>("Status");
            statusCol.setCellValueFactory(new PropertyValueFactory<>("status"));
            statusCol.setPrefWidth(100);

            bookingsTable.getColumns().addAll(resNumCol, guestCol, checkInCol, statusCol);

            javafx.collections.ObservableList<RecentBooking> bookings = javafx.collections.FXCollections.observableArrayList();
            List<Map<String, Object>> recentBookings = StatisticsService.getRecentBookings(10);

            for (Map<String, Object> booking : recentBookings) {
                RecentBooking rb = new RecentBooking();
                rb.setReservationNumber((String) booking.get("reservation_number"));
                rb.setGuestName((String) booking.get("guest_name"));
                rb.setCheckInDate(((java.sql.Date) booking.get("check_in_date")).toLocalDate()
                    .format(DateTimeFormatter.ofPattern("yyyy-MM-dd")));
                rb.setStatus((String) booking.get("status"));
                bookings.add(rb);
            }

            bookingsTable.setItems(bookings);
            recentActivityContainer.getChildren().add(bookingsTable);

            // Recent Payments
            Label paymentsTitle = new Label("Recent Payments");
            paymentsTitle.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-padding: 20 0 0 0;");
            recentActivityContainer.getChildren().add(paymentsTitle);

            TableView<RecentPayment> paymentsTable = new TableView<>();
            paymentsTable.setPrefHeight(200);

            TableColumn<RecentPayment, String> resNumCol2 = new TableColumn<>("Reservation #");
            resNumCol2.setCellValueFactory(new PropertyValueFactory<>("reservationNumber"));
            resNumCol2.setPrefWidth(150);

            TableColumn<RecentPayment, String> amountCol = new TableColumn<>("Amount");
            amountCol.setCellValueFactory(new PropertyValueFactory<>("amount"));
            amountCol.setPrefWidth(120);

            TableColumn<RecentPayment, String> statusCol2 = new TableColumn<>("Status");
            statusCol2.setCellValueFactory(new PropertyValueFactory<>("status"));
            statusCol2.setPrefWidth(100);

            TableColumn<RecentPayment, String> paidAtCol = new TableColumn<>("Paid At");
            paidAtCol.setCellValueFactory(new PropertyValueFactory<>("paidAt"));
            paidAtCol.setPrefWidth(150);

            paymentsTable.getColumns().addAll(resNumCol2, amountCol, statusCol2, paidAtCol);

            javafx.collections.ObservableList<RecentPayment> payments = javafx.collections.FXCollections.observableArrayList();
            List<Map<String, Object>> recentPayments = StatisticsService.getRecentPayments(10);

            for (Map<String, Object> payment : recentPayments) {
                RecentPayment rp = new RecentPayment();
                rp.setReservationNumber((String) payment.get("reservation_number"));
                rp.setAmount(String.format("₱%.2f", (Double) payment.get("amount")));
                rp.setStatus((String) payment.get("status"));
                if (payment.get("paid_at") != null) {
                    rp.setPaidAt(payment.get("paid_at").toString());
                } else {
                    rp.setPaidAt("N/A");
                }
                payments.add(rp);
            }

            paymentsTable.setItems(payments);
            recentActivityContainer.getChildren().add(paymentsTable);
        });
    }

    /**
     * Update last updated label.
     */
    private void updateLastUpdatedLabel() {
        if (lastUpdatedLabel != null) {
            Platform.runLater(() -> {
                lastUpdatedLabel.setText("Last updated: " + 
                    java.time.LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")));
            });
        }
    }

    /**
     * Simple class for recent bookings table.
     */
    public static class RecentBooking {
        private String reservationNumber;
        private String guestName;
        private String checkInDate;
        private String status;

        public String getReservationNumber() { return reservationNumber; }
        public void setReservationNumber(String reservationNumber) { this.reservationNumber = reservationNumber; }

        public String getGuestName() { return guestName; }
        public void setGuestName(String guestName) { this.guestName = guestName; }

        public String getCheckInDate() { return checkInDate; }
        public void setCheckInDate(String checkInDate) { this.checkInDate = checkInDate; }

        public String getStatus() { return status; }
        public void setStatus(String status) { this.status = status; }
    }

    /**
     * Simple class for recent payments table.
     */
    public static class RecentPayment {
        private String reservationNumber;
        private String amount;
        private String status;
        private String paidAt;

        public String getReservationNumber() { return reservationNumber; }
        public void setReservationNumber(String reservationNumber) { this.reservationNumber = reservationNumber; }

        public String getAmount() { return amount; }
        public void setAmount(String amount) { this.amount = amount; }

        public String getStatus() { return status; }
        public void setStatus(String status) { this.status = status; }

        public String getPaidAt() { return paidAt; }
        public void setPaidAt(String paidAt) { this.paidAt = paidAt; }
    }

    /**
     * Show overview panel.
     */
    private void showOverview() {
        setActiveButton(dashboardBtn);
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/overview.fxml"));
            loader.setController(this); // Set this controller so FXML elements are injected
            Node overviewNode = loader.load();
            mainContainer.setCenter(overviewNode);
            // Reload data after showing overview
            loadOverviewDataAsync();
            loadCharts();
            loadRecentActivity();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Error", "Failed to load dashboard overview: " + e.getMessage());
        }
    }

    /**
     * Load a module FXML file into the center area.
     */
    private void loadModule(String fxmlPath) {
        if (mainContainer == null) {
            showAlert("Error", "Dashboard not properly initialized. Please restart the application.");
            return;
        }
        try {
            FXMLLoader loader = new FXMLLoader(DashboardController.class.getResource(fxmlPath));
            Node moduleNode = loader.load();
            mainContainer.setCenter(moduleNode);

            // Update active button based on module
            Button activeBtn = null;
            if (fxmlPath.contains("bookings")) activeBtn = bookingsBtn;
            else if (fxmlPath.contains("rooms")) activeBtn = roomsBtn;
            else if (fxmlPath.contains("users")) activeBtn = usersBtn;
            else if (fxmlPath.contains("payments")) activeBtn = paymentsBtn;
            else if (fxmlPath.contains("reports")) activeBtn = reportsBtn;

            if (activeBtn != null) {
                setActiveButton(activeBtn);
            }
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Error", "Failed to load module: " + e.getMessage());
        }
    }
    
    private void showAlert(String title, String message) {
        javafx.scene.control.Alert alert = new javafx.scene.control.Alert(javafx.scene.control.Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    /**
     * Set the active button style.
     */
    private void setActiveButton(Button button) {
        // Remove active class from all buttons
        dashboardBtn.getStyleClass().remove("active");
        bookingsBtn.getStyleClass().remove("active");
        roomsBtn.getStyleClass().remove("active");
        usersBtn.getStyleClass().remove("active");
        paymentsBtn.getStyleClass().remove("active");
        reportsBtn.getStyleClass().remove("active");

        // Add active class to selected button
        if (button != null) {
            button.getStyleClass().add("active");
        }
    }

    /**
     * Handle logout action.
     */
    @FXML
    private void handleLogout() {
        SessionManager.getInstance().clearSession();
        
        try {
            // Get stage from any node in the scene
            Stage stage = null;
            if (mainContainer != null && mainContainer.getScene() != null) {
                stage = (Stage) mainContainer.getScene().getWindow();
            } else if (userNameLabel != null && userNameLabel.getScene() != null) {
                stage = (Stage) userNameLabel.getScene().getWindow();
            } else {
                showAlert("Error", "Cannot determine application window. Please close and restart.");
                return;
            }
            FXMLLoader loader = new FXMLLoader(DashboardController.class.getResource("/fxml/login.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            scene.getStylesheets().add(DashboardController.class.getResource("/css/styles.css").toExternalForm());
            
            stage.setTitle("Belmont Hotel - Admin Panel");
            stage.setScene(scene);
            stage.setResizable(false);
            stage.centerOnScreen();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Error", "Failed to logout: " + e.getMessage());
        }
    }
}

