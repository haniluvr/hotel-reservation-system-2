package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.utils.DatabaseConnection;
import com.belmonthotel.admin.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

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
    private ImageView logoImageView;

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

        // Load overview data
        loadOverviewData();

        // Set button actions
        dashboardBtn.setOnAction(e -> showOverview());
        bookingsBtn.setOnAction(e -> loadModule("/fxml/bookings.fxml"));
        roomsBtn.setOnAction(e -> loadModule("/fxml/rooms.fxml"));
        usersBtn.setOnAction(e -> loadModule("/fxml/users.fxml"));
        paymentsBtn.setOnAction(e -> loadModule("/fxml/payments.fxml"));
        reportsBtn.setOnAction(e -> loadModule("/fxml/reports.fxml"));
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
    }

    /**
     * Show overview panel.
     */
    private void showOverview() {
        setActiveButton(dashboardBtn);
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/overview.fxml"));
            Node overviewNode = loader.load();
            mainContainer.setCenter(overviewNode);
        } catch (IOException e) {
            e.printStackTrace();
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

