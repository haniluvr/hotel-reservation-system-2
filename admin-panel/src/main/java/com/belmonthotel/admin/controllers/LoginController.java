package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.utils.DatabaseConnection;
import com.belmonthotel.admin.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Controller for the login screen.
 */
public class LoginController {
    @FXML
    private TextField emailField;

    @FXML
    private PasswordField passwordField;

    @FXML
    private ImageView logoImageView;

    @FXML
    private void initialize() {
        // Load logo image if available
        loadLogo();
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
     * Handle login button click.
     */
    @FXML
    private void handleLogin() {
        String email = emailField.getText().trim();
        String password = passwordField.getText();

        if (email.isEmpty() || password.isEmpty()) {
            showAlert(Alert.AlertType.ERROR, "Error", "Please enter both email and password.");
            return;
        }

        try {
            // Authenticate user
            if (authenticateUser(email, password)) {
                // Load dashboard
                loadDashboard();
            } else {
                showAlert(Alert.AlertType.ERROR, "Login Failed", "Invalid email or password.");
                passwordField.clear();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to connect to database: " + e.getMessage());
            e.printStackTrace();
        } catch (IOException e) {
            showAlert(Alert.AlertType.ERROR, "Error", "Failed to load dashboard: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Authenticate user against database.
     * @param email User email
     * @param password User password (plain text, will be checked against hashed password)
     * @return true if authentication successful
     * @throws SQLException if database error occurs
     */
    private boolean authenticateUser(String email, String password) throws SQLException {
        String query = "SELECT id, name, email, password FROM users WHERE email = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setString(1, email);
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                String hashedPassword = rs.getString("password");
                
                // Check if password matches (using Laravel's bcrypt hash verification)
                // For now, we'll use a simple check. In production, use BCrypt library
                // Since Laravel uses bcrypt, we need to verify using BCrypt
                if (verifyPassword(password, hashedPassword)) {
                    // Check if user is admin (by email for now, or add is_admin field)
                    boolean isAdmin = email.equals("admin@belmonthotel.com") || 
                                    email.toLowerCase().contains("admin");
                    
                    // Set session
                    SessionManager.getInstance().setSession(
                        rs.getInt("id"),
                        rs.getString("name"),
                        rs.getString("email"),
                        isAdmin
                    );
                    
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Verify password against Laravel's bcrypt hash.
     * @param plainPassword Plain text password
     * @param hashedPassword Hashed password from database
     * @return true if password matches
     */
    private boolean verifyPassword(String plainPassword, String hashedPassword) {
        try {
            // Laravel uses bcrypt with $2y$ prefix, jBCrypt uses $2a$
            // Convert $2y$ to $2a$ for jBCrypt compatibility
            String compatibleHash = hashedPassword;
            if (hashedPassword.startsWith("$2y$")) {
                compatibleHash = "$2a$" + hashedPassword.substring(4);
            }
            
            // Use jBCrypt to verify password
            return org.mindrot.jbcrypt.BCrypt.checkpw(plainPassword, compatibleHash);
        } catch (Exception e) {
            // If BCrypt verification fails, log and return false
            System.err.println("Password verification error: " + e.getMessage());
            return false;
        }
    }

    /**
     * Load the dashboard screen.
     */
    private void loadDashboard() throws IOException {
        Stage stage = (Stage) emailField.getScene().getWindow();
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/dashboard.fxml"));
        Scene scene = new Scene(loader.load(), 1400, 900);
        scene.getStylesheets().add(getClass().getResource("/css/styles.css").toExternalForm());
        
        stage.setTitle("Belmont Hotel - Admin Dashboard");
        stage.setScene(scene);
        stage.setResizable(true);
        stage.centerOnScreen();
    }

    /**
     * Show an alert dialog.
     */
    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

