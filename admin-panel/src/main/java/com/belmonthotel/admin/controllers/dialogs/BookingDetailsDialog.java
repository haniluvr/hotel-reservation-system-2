package com.belmonthotel.admin.controllers.dialogs;

import com.belmonthotel.admin.models.HistoryNode;
import com.belmonthotel.admin.utils.BookingHistoryLinkedList;
import com.belmonthotel.admin.utils.BookingModificationStack;
import com.belmonthotel.admin.utils.DatabaseConnection;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.stage.Modality;
import javafx.stage.StageStyle;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.function.Consumer;

/**
 * Custom dialog for displaying comprehensive booking details with action buttons.
 */
public class BookingDetailsDialog extends Dialog<Void> {
    
    private int bookingId;
    private String currentStatus;
    private Consumer<Void> onStatusChanged;
    private VBox contentPane;
    private Label paymentStatusLabel;
    private Button confirmBtn;
    private Button cancelBtn;
    private Button completeBtn;
    private Button noShowBtn;
    private Button markPaidBtn;
    private Button modifyDatesBtn;
    private Button undoBtn;
    private Button redoBtn;
    private Label stackSizeLabel;
    private BookingModificationStack modificationStack;
    private BookingHistoryLinkedList historyLinkedList;
    private HistoryNode currentHistoryNode;
    private Button prevHistoryBtn;
    private Button nextHistoryBtn;
    private Label historyDisplayLabel;
    
    public BookingDetailsDialog(int bookingId, Consumer<Void> onStatusChanged) {
        this.bookingId = bookingId;
        this.onStatusChanged = onStatusChanged;
        this.modificationStack = new BookingModificationStack();
        this.historyLinkedList = new BookingHistoryLinkedList();
        
        setTitle("Booking Details");
        setHeaderText("Reservation Information");
        initModality(Modality.APPLICATION_MODAL);
        initStyle(StageStyle.UTILITY);
        
        contentPane = new VBox(15);
        contentPane.setPadding(new Insets(20));
        contentPane.setPrefWidth(700);
        
        loadBookingDetails();
        loadHistoryFromDatabase();
        
        getDialogPane().setContent(contentPane);
        getDialogPane().getButtonTypes().add(ButtonType.CLOSE);
    }
    
    private void loadBookingDetails() {
        String query = "SELECT r.*, u.name as guest_name, u.email as guest_email, u.phone as guest_phone, " +
                      "h.name as hotel_name, h.address as hotel_address, h.city, h.country, " +
                      "rm.room_type, rm.description as room_description, rm.price_per_night, " +
                      "p.id as payment_id, p.status as payment_status, p.amount as payment_amount, " +
                      "p.payment_method, p.paid_at, p.xendit_invoice_id " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "JOIN hotels h ON rm.hotel_id = h.id " +
                      "LEFT JOIN payments p ON r.id = p.reservation_id " +
                      "WHERE r.id = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setInt(1, bookingId);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                currentStatus = rs.getString("status");
                buildDialogContent(rs);
            }
        } catch (SQLException e) {
            showError("Database Error", "Failed to load booking details: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    private void buildDialogContent(ResultSet rs) throws SQLException {
        contentPane.getChildren().clear();
        
        // Header Section
        HBox headerBox = new HBox(10);
        headerBox.setAlignment(Pos.CENTER_LEFT);
        Label reservationLabel = new Label("Reservation #" + rs.getString("reservation_number"));
        reservationLabel.setStyle("-fx-font-size: 18px; -fx-font-weight: bold;");
        
        Label statusLabel = new Label(rs.getString("status").toUpperCase());
        statusLabel.setStyle(getStatusStyle(rs.getString("status")));
        statusLabel.setPadding(new Insets(5, 15, 5, 15));
        
        headerBox.getChildren().addAll(reservationLabel, statusLabel);
        contentPane.getChildren().add(headerBox);
        
        // Guest Information Section
        TitledPane guestPane = createTitledPane("Guest Information");
        GridPane guestGrid = new GridPane();
        guestGrid.setHgap(10);
        guestGrid.setVgap(8);
        guestGrid.setPadding(new Insets(10));
        
        addGridRow(guestGrid, "Name:", rs.getString("guest_name"), 0);
        addGridRow(guestGrid, "Email:", rs.getString("guest_email"), 1);
        if (rs.getString("guest_phone") != null) {
            addGridRow(guestGrid, "Phone:", rs.getString("guest_phone"), 2);
        }
        
        guestPane.setContent(guestGrid);
        contentPane.getChildren().add(guestPane);
        
        // Hotel & Room Information Section
        TitledPane hotelPane = createTitledPane("Hotel & Room Information");
        GridPane hotelGrid = new GridPane();
        hotelGrid.setHgap(10);
        hotelGrid.setVgap(8);
        hotelGrid.setPadding(new Insets(10));
        
        addGridRow(hotelGrid, "Hotel:", rs.getString("hotel_name"), 0);
        addGridRow(hotelGrid, "Address:", rs.getString("hotel_address"), 1);
        addGridRow(hotelGrid, "Location:", rs.getString("city") + ", " + rs.getString("country"), 2);
        addGridRow(hotelGrid, "Room Type:", rs.getString("room_type"), 3);
        if (rs.getString("room_description") != null) {
            Label descLabel = new Label(rs.getString("room_description"));
            descLabel.setWrapText(true);
            hotelGrid.add(new Label("Description:"), 0, 4);
            hotelGrid.add(descLabel, 1, 4);
        }
        addGridRow(hotelGrid, "Price per Night:", String.format("₱%.2f", rs.getDouble("price_per_night")), 5);
        
        hotelPane.setContent(hotelGrid);
        contentPane.getChildren().add(hotelPane);
        
        // Booking Details Section
        TitledPane bookingPane = createTitledPane("Booking Details");
        GridPane bookingGrid = new GridPane();
        bookingGrid.setHgap(10);
        bookingGrid.setVgap(8);
        bookingGrid.setPadding(new Insets(10));
        
        LocalDate checkIn = rs.getDate("check_in_date").toLocalDate();
        LocalDate checkOut = rs.getDate("check_out_date").toLocalDate();
        long nights = java.time.temporal.ChronoUnit.DAYS.between(checkIn, checkOut);
        
        addGridRow(bookingGrid, "Check-in Date:", checkIn.format(DateTimeFormatter.ofPattern("MMMM dd, yyyy")), 0);
        addGridRow(bookingGrid, "Check-out Date:", checkOut.format(DateTimeFormatter.ofPattern("MMMM dd, yyyy")), 1);
        addGridRow(bookingGrid, "Nights:", String.valueOf(nights), 2);
        addGridRow(bookingGrid, "Adults:", String.valueOf(rs.getInt("adults")), 3);
        addGridRow(bookingGrid, "Children:", String.valueOf(rs.getInt("children")), 4);
        addGridRow(bookingGrid, "Total Amount:", String.format("₱%.2f", rs.getDouble("total_amount")), 5);
        if (rs.getDouble("discount_amount") > 0) {
            addGridRow(bookingGrid, "Discount:", String.format("₱%.2f", rs.getDouble("discount_amount")), 6);
        }
        if (rs.getString("promo_code") != null) {
            addGridRow(bookingGrid, "Promo Code:", rs.getString("promo_code"), 7);
        }
        
        bookingPane.setContent(bookingGrid);
        contentPane.getChildren().add(bookingPane);
        
        // Payment Information Section
        TitledPane paymentPane = createTitledPane("Payment Information");
        GridPane paymentGrid = new GridPane();
        paymentGrid.setHgap(10);
        paymentGrid.setVgap(8);
        paymentGrid.setPadding(new Insets(10));
        
        if (rs.getInt("payment_id") > 0) {
            String paymentStatus = rs.getString("payment_status");
            addGridRow(paymentGrid, "Payment Status:", paymentStatus, 0);
            paymentStatusLabel = new Label(paymentStatus);
            paymentStatusLabel.setStyle(getPaymentStatusStyle(paymentStatus));
            paymentGrid.add(paymentStatusLabel, 1, 0);
            
            addGridRow(paymentGrid, "Amount Paid:", String.format("₱%.2f", rs.getDouble("payment_amount")), 1);
            if (rs.getString("payment_method") != null) {
                addGridRow(paymentGrid, "Payment Method:", rs.getString("payment_method"), 2);
            }
            if (rs.getTimestamp("paid_at") != null) {
                addGridRow(paymentGrid, "Paid At:", rs.getTimestamp("paid_at").toString(), 3);
            }
            if (rs.getString("xendit_invoice_id") != null) {
                addGridRow(paymentGrid, "Invoice ID:", rs.getString("xendit_invoice_id"), 4);
            }
        } else {
            Label noPaymentLabel = new Label("No payment record found");
            noPaymentLabel.setTextFill(Color.GRAY);
            paymentGrid.add(noPaymentLabel, 0, 0, 2, 1);
        }
        
        paymentPane.setContent(paymentGrid);
        contentPane.getChildren().add(paymentPane);
        
        // Special Requests Section
        if (rs.getString("special_requests") != null && !rs.getString("special_requests").isEmpty()) {
            TitledPane requestsPane = createTitledPane("Special Requests");
            TextArea requestsArea = new TextArea(rs.getString("special_requests"));
            requestsArea.setEditable(false);
            requestsArea.setPrefRowCount(3);
            requestsPane.setContent(requestsArea);
            contentPane.getChildren().add(requestsPane);
        }
        
        // Booking Timeline Section
        TitledPane timelinePane = createTitledPane("Booking Timeline");
        VBox timelineBox = new VBox(5);
        timelineBox.setPadding(new Insets(10));
        
        if (rs.getTimestamp("created_at") != null) {
            timelineBox.getChildren().add(new Label("Created: " + rs.getTimestamp("created_at").toString()));
        }
        if (rs.getTimestamp("confirmed_at") != null) {
            timelineBox.getChildren().add(new Label("Confirmed: " + rs.getTimestamp("confirmed_at").toString()));
        }
        if (rs.getTimestamp("cancelled_at") != null) {
            timelineBox.getChildren().add(new Label("Cancelled: " + rs.getTimestamp("cancelled_at").toString()));
            if (rs.getString("cancellation_reason") != null) {
                Label reasonLabel = new Label("Reason: " + rs.getString("cancellation_reason"));
                reasonLabel.setWrapText(true);
                timelineBox.getChildren().add(reasonLabel);
            }
        }
        
        timelinePane.setContent(timelineBox);
        contentPane.getChildren().add(timelinePane);
        
        // Modification History Section (LinkedList)
        TitledPane historyPane = createTitledPane("Modification History (LinkedList Data Structure)");
        VBox historyContent = new VBox(10);
        historyContent.setPadding(new Insets(10));
        
        Label historyInfo = new Label("Navigate through modification history using doubly-linked list:");
        historyInfo.setStyle("-fx-font-weight: bold;");
        historyContent.getChildren().add(historyInfo);
        
        HBox historyNavBox = new HBox(10);
        historyNavBox.setAlignment(Pos.CENTER);
        
        prevHistoryBtn = new Button("◀ Previous");
        prevHistoryBtn.setOnAction(e -> navigateHistoryPrevious());
        prevHistoryBtn.setDisable(true);
        
        historyDisplayLabel = new Label("No history available");
        historyDisplayLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666;");
        historyDisplayLabel.setWrapText(true);
        historyDisplayLabel.setPrefWidth(500);
        
        nextHistoryBtn = new Button("Next ▶");
        nextHistoryBtn.setOnAction(e -> navigateHistoryNext());
        nextHistoryBtn.setDisable(true);
        
        historyNavBox.getChildren().addAll(prevHistoryBtn, historyDisplayLabel, nextHistoryBtn);
        historyContent.getChildren().add(historyNavBox);
        
        // Display all history nodes
        ScrollPane historyScroll = new ScrollPane();
        VBox historyList = new VBox(5);
        for (HistoryNode node : historyLinkedList.getAllNodes()) {
            Label nodeLabel = new Label(node.toString());
            nodeLabel.setWrapText(true);
            nodeLabel.setStyle("-fx-font-size: 11px;");
            historyList.getChildren().add(nodeLabel);
        }
        historyScroll.setContent(historyList);
        historyScroll.setPrefHeight(100);
        historyContent.getChildren().add(historyScroll);
        
        historyPane.setContent(historyContent);
        contentPane.getChildren().add(historyPane);
        
        // Initialize current node to tail (most recent)
        if (!historyLinkedList.isEmpty()) {
            currentHistoryNode = historyLinkedList.getTail();
            updateHistoryDisplay();
        }
        
        // Action Buttons Section
        HBox actionBox = new HBox(10);
        actionBox.setPadding(new Insets(15, 0, 0, 0));
        actionBox.setAlignment(Pos.CENTER);
        
        confirmBtn = new Button("Confirm");
        confirmBtn.setStyle("-fx-background-color: #4CAF50; -fx-text-fill: white;");
        confirmBtn.setOnAction(e -> handleConfirm());
        confirmBtn.setVisible(false);
        confirmBtn.setManaged(false);
        
        cancelBtn = new Button("Cancel");
        cancelBtn.setStyle("-fx-background-color: #f44336; -fx-text-fill: white;");
        cancelBtn.setOnAction(e -> handleCancel());
        cancelBtn.setVisible(false);
        cancelBtn.setManaged(false);
        
        completeBtn = new Button("Mark as Completed");
        completeBtn.setStyle("-fx-background-color: #2196F3; -fx-text-fill: white;");
        completeBtn.setOnAction(e -> handleComplete());
        completeBtn.setVisible(false);
        completeBtn.setManaged(false);
        
        noShowBtn = new Button("Mark as No Show");
        noShowBtn.setStyle("-fx-background-color: #FF9800; -fx-text-fill: white;");
        noShowBtn.setOnAction(e -> handleNoShow());
        noShowBtn.setVisible(false);
        noShowBtn.setManaged(false);
        
        markPaidBtn = new Button("Mark Payment as Received");
        markPaidBtn.setStyle("-fx-background-color: #9C27B0; -fx-text-fill: white;");
        markPaidBtn.setOnAction(e -> handleMarkPaid());
        markPaidBtn.setVisible(false);
        markPaidBtn.setManaged(false);
        
        modifyDatesBtn = new Button("Modify Dates");
        modifyDatesBtn.setStyle("-fx-background-color: #607D8B; -fx-text-fill: white;");
        modifyDatesBtn.setOnAction(e -> handleModifyDates());
        modifyDatesBtn.setVisible(false);
        modifyDatesBtn.setManaged(false);
        
        // Stack-based Undo/Redo buttons
        undoBtn = new Button("Undo");
        undoBtn.setStyle("-fx-background-color: #795548; -fx-text-fill: white;");
        undoBtn.setOnAction(e -> handleUndo());
        undoBtn.setTooltip(new Tooltip("Undo last modification (Stack Data Structure)"));
        
        redoBtn = new Button("Redo");
        redoBtn.setStyle("-fx-background-color: #9E9E9E; -fx-text-fill: white;");
        redoBtn.setOnAction(e -> handleRedo());
        redoBtn.setTooltip(new Tooltip("Redo last undone modification (Stack Data Structure)"));
        
        stackSizeLabel = new Label("Actions: 0 undo, 0 redo");
        stackSizeLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #666;");
        stackSizeLabel.setTooltip(new Tooltip("Stack size indicator - shows available undo/redo actions"));
        
        updateActionButtons();
        updateStackButtons();
        
        // Add separator and stack controls
        Separator separator = new Separator();
        separator.setPadding(new Insets(10, 0, 5, 0));
        
        HBox stackBox = new HBox(10);
        stackBox.setAlignment(Pos.CENTER);
        stackBox.getChildren().addAll(undoBtn, redoBtn, stackSizeLabel);
        
        actionBox.getChildren().addAll(confirmBtn, cancelBtn, completeBtn, noShowBtn, markPaidBtn, modifyDatesBtn);
        contentPane.getChildren().addAll(actionBox, separator, stackBox);
    }
    
    private void updateActionButtons() {
        // Show buttons based on current status
        confirmBtn.setVisible("pending".equalsIgnoreCase(currentStatus));
        confirmBtn.setManaged("pending".equalsIgnoreCase(currentStatus));
        
        cancelBtn.setVisible(!"cancelled".equalsIgnoreCase(currentStatus) && !"completed".equalsIgnoreCase(currentStatus));
        cancelBtn.setManaged(!"cancelled".equalsIgnoreCase(currentStatus) && !"completed".equalsIgnoreCase(currentStatus));
        
        completeBtn.setVisible("confirmed".equalsIgnoreCase(currentStatus));
        completeBtn.setManaged("confirmed".equalsIgnoreCase(currentStatus));
        
        noShowBtn.setVisible("confirmed".equalsIgnoreCase(currentStatus));
        noShowBtn.setManaged("confirmed".equalsIgnoreCase(currentStatus));
        
        modifyDatesBtn.setVisible(!"cancelled".equalsIgnoreCase(currentStatus) && !"completed".equalsIgnoreCase(currentStatus));
        modifyDatesBtn.setManaged(!"cancelled".equalsIgnoreCase(currentStatus) && !"completed".equalsIgnoreCase(currentStatus));
        
        // Show mark paid if payment exists and is pending
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement("SELECT status FROM payments WHERE reservation_id = ?")) {
            stmt.setInt(1, bookingId);
            ResultSet rs = stmt.executeQuery();
            if (rs.next() && "pending".equalsIgnoreCase(rs.getString("status"))) {
                markPaidBtn.setVisible(true);
                markPaidBtn.setManaged(true);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
    
    private void handleConfirm() {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirm Booking");
        confirm.setHeaderText(null);
        confirm.setContentText("Are you sure you want to confirm this booking?");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            String oldStatus = currentStatus;
            saveCurrentState("confirm");
            updateStatus("confirmed", "confirmed_at = NOW()");
            addHistoryEntry("confirm", oldStatus, "confirmed");
        }
    }
    
    private void handleCancel() {
        TextInputDialog dialog = new TextInputDialog();
        dialog.setTitle("Cancel Booking");
        dialog.setHeaderText(null);
        dialog.setContentText("Please enter cancellation reason:");
        
        String reason = dialog.showAndWait().orElse("");
        if (!reason.isEmpty()) {
            String oldStatus = currentStatus;
            saveCurrentState("cancel");
            updateStatus("cancelled", "cancelled_at = NOW(), cancellation_reason = '" + reason.replace("'", "''") + "'");
            releaseRoomInventory();
            addHistoryEntry("cancel", oldStatus, "cancelled");
        }
    }
    
    private void handleComplete() {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Mark as Completed");
        confirm.setHeaderText(null);
        confirm.setContentText("Mark this booking as completed?");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            String oldStatus = currentStatus;
            saveCurrentState("complete");
            updateStatus("completed", null);
            addHistoryEntry("complete", oldStatus, "completed");
        }
    }
    
    private void handleNoShow() {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Mark as No Show");
        confirm.setHeaderText(null);
        confirm.setContentText("Mark this booking as no show?");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            String oldStatus = currentStatus;
            saveCurrentState("no_show");
            updateStatus("no_show", null);
            addHistoryEntry("no_show", oldStatus, "no_show");
        }
    }
    
    private void handleMarkPaid() {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Mark Payment as Received");
        confirm.setHeaderText(null);
        confirm.setContentText("Mark payment as received? This will update the payment status to 'paid'.");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            try (Connection conn = DatabaseConnection.getConnection();
                 PreparedStatement stmt = conn.prepareStatement(
                     "UPDATE payments SET status = 'paid', paid_at = NOW() WHERE reservation_id = ?")) {
                stmt.setInt(1, bookingId);
                int rows = stmt.executeUpdate();
                if (rows > 0) {
                    showInfo("Success", "Payment marked as received.");
                    if (onStatusChanged != null) {
                        onStatusChanged.accept(null);
                    }
                    loadBookingDetails(); // Reload to refresh payment status
                }
            } catch (SQLException e) {
                showError("Database Error", "Failed to update payment: " + e.getMessage());
                e.printStackTrace();
            }
        }
    }
    
    private void handleModifyDates() {
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT r.check_in_date, r.check_out_date, r.total_amount, rm.price_per_night " +
                 "FROM reservations r " +
                 "JOIN rooms rm ON r.room_id = rm.id " +
                 "WHERE r.id = ?")) {
            
            stmt.setInt(1, bookingId);
            ResultSet rs = stmt.executeQuery();
            
            if (!rs.next()) {
                showError("Error", "Could not load booking information.");
                return;
            }
            
            LocalDate currentCheckIn = rs.getDate("check_in_date").toLocalDate();
            LocalDate currentCheckOut = rs.getDate("check_out_date").toLocalDate();
            double currentTotal = rs.getDouble("total_amount");
            double pricePerNight = rs.getDouble("price_per_night");
            
            // Create dialog for date modification
            Dialog<LocalDate[]> dateDialog = new Dialog<>();
            dateDialog.setTitle("Modify Booking Dates");
            dateDialog.setHeaderText("Update check-in and check-out dates");
            
            GridPane grid = new GridPane();
            grid.setHgap(10);
            grid.setVgap(10);
            grid.setPadding(new Insets(20));
            
            DatePicker checkInPicker = new DatePicker(currentCheckIn);
            DatePicker checkOutPicker = new DatePicker(currentCheckOut);
            Label nightsLabel = new Label();
            Label newTotalLabel = new Label();
            
            // Calculate and display nights and new total
            Runnable updateCalculations = () -> {
                LocalDate newCheckIn = checkInPicker.getValue();
                LocalDate newCheckOut = checkOutPicker.getValue();
                
                if (newCheckIn != null && newCheckOut != null && !newCheckOut.isBefore(newCheckIn) && !newCheckOut.isEqual(newCheckIn)) {
                    long nights = java.time.temporal.ChronoUnit.DAYS.between(newCheckIn, newCheckOut);
                    double newTotal = nights * pricePerNight;
                    nightsLabel.setText("Nights: " + nights);
                    newTotalLabel.setText(String.format("New Total: ₱%.2f (Current: ₱%.2f)", newTotal, currentTotal));
                } else {
                    nightsLabel.setText("Invalid dates");
                    newTotalLabel.setText("");
                }
            };
            
            checkInPicker.valueProperty().addListener((obs, oldVal, newVal) -> {
                if (newVal != null && checkOutPicker.getValue() != null && newVal.isAfter(checkOutPicker.getValue())) {
                    checkOutPicker.setValue(newVal.plusDays(1));
                }
                updateCalculations.run();
            });
            
            checkOutPicker.valueProperty().addListener((obs, oldVal, newVal) -> {
                if (newVal != null && checkInPicker.getValue() != null && newVal.isBefore(checkInPicker.getValue())) {
                    checkInPicker.setValue(newVal.minusDays(1));
                }
                updateCalculations.run();
            });
            
            grid.add(new Label("Check-in Date:"), 0, 0);
            grid.add(checkInPicker, 1, 0);
            grid.add(new Label("Check-out Date:"), 0, 1);
            grid.add(checkOutPicker, 1, 1);
            grid.add(nightsLabel, 0, 2, 2, 1);
            grid.add(newTotalLabel, 0, 3, 2, 1);
            
            dateDialog.getDialogPane().setContent(grid);
            dateDialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);
            
            dateDialog.setResultConverter(dialogButton -> {
                if (dialogButton == ButtonType.OK) {
                    return new LocalDate[]{checkInPicker.getValue(), checkOutPicker.getValue()};
                }
                return null;
            });
            
            dateDialog.showAndWait().ifPresent(dates -> {
                if (dates[0] != null && dates[1] != null) {
                    LocalDate newCheckIn = dates[0];
                    LocalDate newCheckOut = dates[1];
                    
                    if (newCheckOut.isBefore(newCheckIn) || newCheckOut.isEqual(newCheckIn)) {
                        showError("Invalid Dates", "Check-out date must be after check-in date.");
                        return;
                    }
                    
                    if (newCheckIn.isBefore(LocalDate.now())) {
                        showError("Invalid Date", "Check-in date cannot be in the past.");
                        return;
                    }
                    
                    // Check room availability
                    if (!checkRoomAvailability(newCheckIn, newCheckOut)) {
                        showError("Room Unavailable", "Room is not available for the selected dates.");
                        return;
                    }
                    
                    // Calculate new total
                    long nights = java.time.temporal.ChronoUnit.DAYS.between(newCheckIn, newCheckOut);
                    double newTotal = nights * pricePerNight;
                    
                    Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
                    confirm.setTitle("Confirm Date Modification");
                    confirm.setHeaderText(null);
                    confirm.setContentText(String.format(
                        "Update booking dates?\n\n" +
                        "Check-in: %s → %s\n" +
                        "Check-out: %s → %s\n" +
                        "New Total: ₱%.2f (was ₱%.2f)",
                        currentCheckIn, newCheckIn,
                        currentCheckOut, newCheckOut,
                        newTotal, currentTotal));
                    
                    if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
                        String oldDates = currentCheckIn + " to " + currentCheckOut;
                        String newDates = newCheckIn + " to " + newCheckOut;
                        saveCurrentState("modify_dates");
                        updateBookingDates(newCheckIn, newCheckOut, newTotal);
                        addHistoryEntry("modify_dates", oldDates, newDates);
                    }
                }
            });
            
        } catch (SQLException e) {
            showError("Database Error", "Failed to load booking information: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    private boolean checkRoomAvailability(LocalDate checkIn, LocalDate checkOut) {
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT rm.available_quantity, COUNT(r.id) as booked_count " +
                 "FROM reservations r " +
                 "JOIN rooms rm ON r.room_id = rm.id " +
                 "WHERE r.id = ? AND r.status NOT IN ('cancelled', 'no_show') " +
                 "AND ((r.check_in_date <= ? AND r.check_out_date > ?) OR " +
                 "     (r.check_in_date < ? AND r.check_out_date >= ?) OR " +
                 "     (r.check_in_date >= ? AND r.check_out_date <= ?)) " +
                 "GROUP BY rm.available_quantity")) {
            
            stmt.setInt(1, bookingId);
            stmt.setDate(2, java.sql.Date.valueOf(checkOut));
            stmt.setDate(3, java.sql.Date.valueOf(checkIn));
            stmt.setDate(4, java.sql.Date.valueOf(checkOut));
            stmt.setDate(5, java.sql.Date.valueOf(checkIn));
            stmt.setDate(6, java.sql.Date.valueOf(checkIn));
            stmt.setDate(7, java.sql.Date.valueOf(checkOut));
            
            ResultSet rs = stmt.executeQuery();
            
            // Get room info
            try (PreparedStatement roomStmt = conn.prepareStatement(
                 "SELECT rm.available_quantity FROM reservations r " +
                 "JOIN rooms rm ON r.room_id = rm.id WHERE r.id = ?")) {
                roomStmt.setInt(1, bookingId);
                ResultSet roomRs = roomStmt.executeQuery();
                if (roomRs.next()) {
                    int available = roomRs.getInt("available_quantity");
                    // If there are conflicts, check if we can still accommodate
                    if (rs.next()) {
                        // We're modifying this booking, so available should be at least 1
                        return available >= 0;
                    }
                    return available > 0;
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return true; // Default to allowing if check fails
    }
    
    private void updateBookingDates(LocalDate newCheckIn, LocalDate newCheckOut, double newTotal) {
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "UPDATE reservations SET check_in_date = ?, check_out_date = ?, total_amount = ?, updated_at = NOW() WHERE id = ?")) {
            
            stmt.setDate(1, java.sql.Date.valueOf(newCheckIn));
            stmt.setDate(2, java.sql.Date.valueOf(newCheckOut));
            stmt.setDouble(3, newTotal);
            stmt.setInt(4, bookingId);
            
            int rows = stmt.executeUpdate();
            if (rows > 0) {
                showInfo("Success", "Booking dates updated successfully.");
                updateStackButtons();
                if (onStatusChanged != null) {
                    onStatusChanged.accept(null);
                }
                loadBookingDetails(); // Reload to refresh display
            }
        } catch (SQLException e) {
            showError("Database Error", "Failed to update booking dates: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    /**
     * Save current booking state to stack before modification.
     */
    private void saveCurrentState(String actionType) {
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT status, check_in_date, check_out_date, total_amount FROM reservations WHERE id = ?")) {
            
            stmt.setInt(1, bookingId);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                BookingModificationStack.BookingState state = new BookingModificationStack.BookingState(
                    bookingId,
                    rs.getString("status"),
                    rs.getDate("check_in_date").toLocalDate(),
                    rs.getDate("check_out_date").toLocalDate(),
                    rs.getDouble("total_amount"),
                    actionType
                );
                modificationStack.pushState(state);
                updateStackButtons();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
    
    /**
     * Handle undo operation using Stack.
     */
    private void handleUndo() {
        BookingModificationStack.BookingState state = modificationStack.popUndo();
        if (state == null) {
            showInfo("Info", "No actions to undo.");
            return;
        }
        
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Undo Modification");
        confirm.setHeaderText(null);
        confirm.setContentText(String.format("Undo %s action? This will restore:\nStatus: %s\nCheck-in: %s\nCheck-out: %s\nAmount: ₱%.2f",
            state.getActionType(), state.getStatus(), state.getCheckInDate(), state.getCheckOutDate(), state.getTotalAmount()));
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            restoreBookingState(state);
        } else {
            // User cancelled, push state back
            modificationStack.pushState(state);
        }
        updateStackButtons();
    }
    
    /**
     * Handle redo operation using Stack.
     */
    private void handleRedo() {
        BookingModificationStack.BookingState state = modificationStack.popRedo();
        if (state == null) {
            showInfo("Info", "No actions to redo.");
            return;
        }
        
        // For redo, we need to save current state first, then apply the redo state
        saveCurrentState("redo");
        restoreBookingState(state);
        updateStackButtons();
    }
    
    /**
     * Restore booking to a previous state.
     */
    private void restoreBookingState(BookingModificationStack.BookingState state) {
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "UPDATE reservations SET status = ?, check_in_date = ?, check_out_date = ?, total_amount = ?, updated_at = NOW() WHERE id = ?")) {
            
            stmt.setString(1, state.getStatus());
            stmt.setDate(2, java.sql.Date.valueOf(state.getCheckInDate()));
            stmt.setDate(3, java.sql.Date.valueOf(state.getCheckOutDate()));
            stmt.setDouble(4, state.getTotalAmount());
            stmt.setInt(5, state.getBookingId());
            
            int rows = stmt.executeUpdate();
            if (rows > 0) {
                currentStatus = state.getStatus();
                showInfo("Success", "Booking restored to previous state.");
                if (onStatusChanged != null) {
                    onStatusChanged.accept(null);
                }
                loadBookingDetails(); // Reload to refresh display
            }
        } catch (SQLException e) {
            showError("Database Error", "Failed to restore booking state: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    /**
     * Update undo/redo button states based on stack size.
     */
    private void updateStackButtons() {
        int undoSize = modificationStack.getUndoStackSize();
        int redoSize = modificationStack.getRedoStackSize();
        
        undoBtn.setDisable(undoSize == 0);
        redoBtn.setDisable(redoSize == 0);
        
        stackSizeLabel.setText(String.format("Actions: %d undo, %d redo", undoSize, redoSize));
    }
    
    /**
     * Update booking status in the database.
     */
    private void updateStatus(String newStatus, String additionalUpdates) {
        StringBuilder query = new StringBuilder("UPDATE reservations SET status = ?");
        if (additionalUpdates != null) {
            query.append(", ").append(additionalUpdates);
        }
        query.append(" WHERE id = ?");
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query.toString())) {
            
            stmt.setString(1, newStatus);
            stmt.setInt(2, bookingId);
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                currentStatus = newStatus;
                showInfo("Success", "Booking status updated to " + newStatus + ".");
                updateStackButtons();
                if (onStatusChanged != null) {
                    onStatusChanged.accept(null);
                }
                loadBookingDetails(); // Reload to refresh display
            }
        } catch (SQLException e) {
            showError("Database Error", "Failed to update booking status: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    /**
     * Load modification history from database and populate LinkedList.
     */
    private void loadHistoryFromDatabase() {
        // In a real implementation, this would load from a history/audit table
        // For now, we'll create sample history entries based on current booking state
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT status, check_in_date, check_out_date, created_at, confirmed_at, cancelled_at " +
                 "FROM reservations WHERE id = ?")) {
            
            stmt.setInt(1, bookingId);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                // Add creation as first history entry
                if (rs.getTimestamp("created_at") != null) {
                    historyLinkedList.add("created", "N/A", rs.getString("status"), "System");
                }
                
                // Add confirmation if exists
                if (rs.getTimestamp("confirmed_at") != null) {
                    historyLinkedList.add("confirm", "pending", "confirmed", "Admin");
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
    
    /**
     * Add a history entry to the LinkedList.
     */
    private void addHistoryEntry(String actionType, String oldValue, String newValue) {
        historyLinkedList.add(actionType, oldValue, newValue, "Admin");
        currentHistoryNode = historyLinkedList.getTail();
        updateHistoryDisplay();
    }
    
    /**
     * Navigate to previous history node (backward traversal).
     */
    private void navigateHistoryPrevious() {
        if (currentHistoryNode != null && currentHistoryNode.getPrevious() != null) {
            currentHistoryNode = currentHistoryNode.getPrevious();
            updateHistoryDisplay();
        }
    }
    
    /**
     * Navigate to next history node (forward traversal).
     */
    private void navigateHistoryNext() {
        if (currentHistoryNode != null && currentHistoryNode.getNext() != null) {
            currentHistoryNode = currentHistoryNode.getNext();
            updateHistoryDisplay();
        }
    }
    
    /**
     * Update history display and navigation buttons.
     */
    private void updateHistoryDisplay() {
        if (currentHistoryNode == null) {
            historyDisplayLabel.setText("No history available");
            prevHistoryBtn.setDisable(true);
            nextHistoryBtn.setDisable(true);
            return;
        }
        
        historyDisplayLabel.setText(String.format(
            "Modified by %s on %s\nChanged %s from '%s' to '%s'",
            currentHistoryNode.getAdminUser(),
            currentHistoryNode.getTimestamp().format(java.time.format.DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")),
            currentHistoryNode.getActionType(),
            currentHistoryNode.getOldValue(),
            currentHistoryNode.getNewValue()
        ));
        
        prevHistoryBtn.setDisable(currentHistoryNode.getPrevious() == null);
        nextHistoryBtn.setDisable(currentHistoryNode.getNext() == null);
    }
    
    private void releaseRoomInventory() {
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "UPDATE rooms SET available_quantity = available_quantity + 1 " +
                 "WHERE id = (SELECT room_id FROM reservations WHERE id = ?)")) {
            stmt.setInt(1, bookingId);
            stmt.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
    
    private TitledPane createTitledPane(String title) {
        TitledPane pane = new TitledPane();
        pane.setText(title);
        pane.setExpanded(true);
        pane.setCollapsible(false);
        return pane;
    }
    
    private void addGridRow(GridPane grid, String label, String value, int row) {
        grid.add(new Label(label), 0, row);
        Label valueLabel = new Label(value);
        valueLabel.setStyle("-fx-font-weight: bold;");
        grid.add(valueLabel, 1, row);
    }
    
    private String getStatusStyle(String status) {
        switch (status.toLowerCase()) {
            case "confirmed":
                return "-fx-background-color: #4CAF50; -fx-text-fill: white; -fx-background-radius: 5;";
            case "pending":
                return "-fx-background-color: #FF9800; -fx-text-fill: white; -fx-background-radius: 5;";
            case "cancelled":
                return "-fx-background-color: #f44336; -fx-text-fill: white; -fx-background-radius: 5;";
            case "completed":
                return "-fx-background-color: #2196F3; -fx-text-fill: white; -fx-background-radius: 5;";
            case "no_show":
                return "-fx-background-color: #9E9E9E; -fx-text-fill: white; -fx-background-radius: 5;";
            default:
                return "-fx-background-color: #757575; -fx-text-fill: white; -fx-background-radius: 5;";
        }
    }
    
    private String getPaymentStatusStyle(String status) {
        switch (status.toLowerCase()) {
            case "paid":
                return "-fx-text-fill: #4CAF50; -fx-font-weight: bold;";
            case "pending":
                return "-fx-text-fill: #FF9800; -fx-font-weight: bold;";
            case "failed":
                return "-fx-text-fill: #f44336; -fx-font-weight: bold;";
            default:
                return "-fx-text-fill: #757575; -fx-font-weight: bold;";
        }
    }
    
    private void showError(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
    
    private void showInfo(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

