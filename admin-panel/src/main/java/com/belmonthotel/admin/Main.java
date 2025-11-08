package com.belmonthotel.admin;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.image.Image;
import javafx.stage.Stage;
import com.belmonthotel.admin.utils.DatabaseConnection;

import java.io.IOException;
import java.io.InputStream;

/**
 * Main entry point for the Belmont Hotel Admin Panel application.
 */
public class Main extends Application {
    @Override
    public void start(Stage stage) throws IOException {
        // Initialize database connection
        try {
            DatabaseConnection.getInstance();
        } catch (Exception e) {
            System.err.println("Failed to initialize database connection: " + e.getMessage());
            e.printStackTrace();
        }

        // Load login screen
        FXMLLoader fxmlLoader = new FXMLLoader(Main.class.getResource("/fxml/login.fxml"));
        Scene scene = new Scene(fxmlLoader.load(), 900, 600);
        
        // Apply CSS styles
        scene.getStylesheets().add(getClass().getResource("/css/styles.css").toExternalForm());
        
        // Set application icon
        try {
            InputStream iconStream = getClass().getResourceAsStream("/images/logo.png");
            if (iconStream != null) {
                Image icon = new Image(iconStream);
                stage.getIcons().add(icon);
            }
        } catch (Exception e) {
            System.out.println("Could not load application icon: " + e.getMessage());
        }
        
        stage.setTitle("Belmont Hotel - Admin Panel");
        stage.setScene(scene);
        stage.setResizable(false);
        stage.show();

        // Close database connection on application exit
        stage.setOnCloseRequest(e -> {
            DatabaseConnection.closeConnection();
        });
    }

    public static void main(String[] args) {
        launch();
    }
}

