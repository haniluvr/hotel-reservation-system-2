package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.User;
import com.belmonthotel.admin.utils.DatabaseConnection;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;

import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.format.DateTimeFormatter;
import java.util.ResourceBundle;

/**
 * Controller for the users management module.
 */
public class UsersController implements Initializable {
    @FXML
    private TableView<User> usersTable;

    @FXML
    private TableColumn<User, Integer> idColumn;

    @FXML
    private TableColumn<User, String> nameColumn;

    @FXML
    private TableColumn<User, String> emailColumn;

    @FXML
    private TableColumn<User, String> roleColumn;

    @FXML
    private TableColumn<User, String> statusColumn;

    @FXML
    private TableColumn<User, String> createdAtColumn;

    @FXML
    private TextField searchField;

    @FXML
    private ComboBox<String> roleFilter;

    @FXML
    private Button refreshBtn;

    @FXML
    private Button viewDetailsBtn;

    private ObservableList<User> usersList;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        usersList = FXCollections.observableArrayList();

        // Initialize table columns
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        nameColumn.setCellValueFactory(new PropertyValueFactory<>("name"));
        emailColumn.setCellValueFactory(new PropertyValueFactory<>("email"));
        roleColumn.setCellValueFactory(new PropertyValueFactory<>("role"));
        statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));
        createdAtColumn.setCellValueFactory(new PropertyValueFactory<>("createdAt"));

        // Set role filter options
        roleFilter.getItems().addAll("All", "Admin", "Customer");
        roleFilter.setValue("All");

        // Set button actions
        refreshBtn.setOnAction(e -> loadUsers());
        viewDetailsBtn.setOnAction(e -> viewUserDetails());

        // Set filter actions
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        roleFilter.setOnAction(e -> applyFilters());

        // Load initial data
        loadUsers();
    }

    /**
     * Load users from database.
     */
    private void loadUsers() {
        usersList.clear();
        
        String query = "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                User user = new User();
                user.setId(rs.getInt("id"));
                user.setName(rs.getString("name"));
                user.setEmail(rs.getString("email"));
                
                // Determine role (admin if email contains "admin")
                boolean isAdmin = rs.getString("email").toLowerCase().contains("admin");
                user.setRole(isAdmin ? "Admin" : "Customer");
                user.setStatus("Active"); // Default status
                
                // Format created date
                if (rs.getTimestamp("created_at") != null) {
                    user.setCreatedAt(rs.getTimestamp("created_at").toLocalDateTime()
                        .format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm")));
                }
                
                usersList.add(user);
            }

            applyFilters();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load users: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Apply filters to the users list.
     */
    private void applyFilters() {
        ObservableList<User> filtered = FXCollections.observableArrayList();
        String searchText = searchField.getText().toLowerCase();
        String role = roleFilter.getValue();

        for (User user : usersList) {
            // Role filter
            if (role != null && !role.equals("All")) {
                if (!user.getRole().equals(role)) {
                    continue;
                }
            }

            // Search filter
            if (!searchText.isEmpty()) {
                if (!user.getName().toLowerCase().contains(searchText) &&
                    !user.getEmail().toLowerCase().contains(searchText)) {
                    continue;
                }
            }

            filtered.add(user);
        }

        usersTable.setItems(filtered);
    }

    /**
     * View details of the selected user.
     */
    private void viewUserDetails() {
        User selected = usersTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a user to view details.");
            return;
        }

        // Load user details and booking history
        String userQuery = "SELECT * FROM users WHERE id = ?";
        String bookingsQuery = "SELECT COUNT(*) as count, SUM(total_amount) as total FROM reservations WHERE user_id = ?";

        try (Connection conn = DatabaseConnection.getConnection()) {
            // Get user details
            StringBuilder details = new StringBuilder();
            try (PreparedStatement stmt = conn.prepareStatement(userQuery)) {
                stmt.setInt(1, selected.getId());
                ResultSet rs = stmt.executeQuery();

                if (rs.next()) {
                    details.append("User Information:\n");
                    details.append("  ID: ").append(rs.getInt("id")).append("\n");
                    details.append("  Name: ").append(rs.getString("name")).append("\n");
                    details.append("  Email: ").append(rs.getString("email")).append("\n");
                    details.append("  Created: ").append(rs.getTimestamp("created_at")).append("\n\n");
                }
            }

            // Get booking statistics
            try (PreparedStatement stmt = conn.prepareStatement(bookingsQuery)) {
                stmt.setInt(1, selected.getId());
                ResultSet rs = stmt.executeQuery();

                if (rs.next()) {
                    details.append("Booking Statistics:\n");
                    details.append("  Total Bookings: ").append(rs.getInt("count")).append("\n");
                    double total = rs.getDouble("total");
                    if (rs.wasNull()) {
                        details.append("  Total Spent: ₱0.00\n");
                    } else {
                        details.append("  Total Spent: ").append(String.format("₱%.2f", total)).append("\n");
                    }
                }
            }

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("User Details");
            alert.setHeaderText(null);
            alert.setContentText(details.toString());
            alert.showAndWait();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load user details: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

