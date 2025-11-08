package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.Hotel;
import com.belmonthotel.admin.utils.DatabaseConnection;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.GridPane;
import javafx.geometry.Insets;

import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ResourceBundle;

/**
 * Controller for the hotels management module.
 */
public class HotelsController implements Initializable {
    @FXML
    private TableView<Hotel> hotelsTable;

    @FXML
    private TableColumn<Hotel, Integer> idColumn;

    @FXML
    private TableColumn<Hotel, String> nameColumn;

    @FXML
    private TableColumn<Hotel, String> cityColumn;

    @FXML
    private TableColumn<Hotel, String> countryColumn;

    @FXML
    private TableColumn<Hotel, Integer> starRatingColumn;

    @FXML
    private TableColumn<Hotel, String> statusColumn;

    @FXML
    private TextField searchField;

    @FXML
    private Button addBtn;

    @FXML
    private Button editBtn;

    @FXML
    private Button deleteBtn;

    @FXML
    private Button toggleStatusBtn;

    @FXML
    private Button refreshBtn;

    private ObservableList<Hotel> hotelsList;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        hotelsList = FXCollections.observableArrayList();

        // Initialize table columns
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        nameColumn.setCellValueFactory(new PropertyValueFactory<>("name"));
        cityColumn.setCellValueFactory(new PropertyValueFactory<>("city"));
        countryColumn.setCellValueFactory(new PropertyValueFactory<>("country"));
        starRatingColumn.setCellValueFactory(new PropertyValueFactory<>("starRating"));
        statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));

        // Set button actions
        addBtn.setOnAction(e -> showHotelForm(null));
        editBtn.setOnAction(e -> editSelectedHotel());
        deleteBtn.setOnAction(e -> deleteSelectedHotel());
        toggleStatusBtn.setOnAction(e -> toggleHotelStatus());
        refreshBtn.setOnAction(e -> loadHotels());

        // Set search filter
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applySearchFilter());

        // Load initial data
        loadHotels();
    }

    /**
     * Load hotels from database.
     */
    private void loadHotels() {
        hotelsList.clear();
        
        String query = "SELECT id, name, city, country, star_rating, is_active FROM hotels ORDER BY name";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                Hotel hotel = new Hotel();
                hotel.setId(rs.getInt("id"));
                hotel.setName(rs.getString("name"));
                hotel.setCity(rs.getString("city"));
                hotel.setCountry(rs.getString("country"));
                hotel.setStarRating(rs.getInt("star_rating"));
                hotel.setStatus(rs.getBoolean("is_active") ? "Active" : "Inactive");
                
                hotelsList.add(hotel);
            }

            hotelsTable.setItems(hotelsList);
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load hotels: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Apply search filter.
     */
    private void applySearchFilter() {
        String searchText = searchField.getText().toLowerCase();
        ObservableList<Hotel> filtered = FXCollections.observableArrayList();

        for (Hotel hotel : hotelsList) {
            if (hotel.getName().toLowerCase().contains(searchText) ||
                hotel.getCity().toLowerCase().contains(searchText) ||
                hotel.getCountry().toLowerCase().contains(searchText)) {
                filtered.add(hotel);
            }
        }

        hotelsTable.setItems(filtered);
    }

    /**
     * Show hotel form for add/edit.
     */
    private void showHotelForm(Hotel hotel) {
        // Create dialog for hotel form
        Dialog<Hotel> dialog = new Dialog<>();
        dialog.setTitle(hotel == null ? "Add Hotel" : "Edit Hotel");
        dialog.setHeaderText(null);

        // Create form fields
        TextField nameField = new TextField();
        TextArea descriptionField = new TextArea();
        TextField addressField = new TextField();
        TextField cityField = new TextField();
        TextField countryField = new TextField();
        TextField latitudeField = new TextField();
        TextField longitudeField = new TextField();
        Spinner<Integer> starRatingSpinner = new Spinner<>(1, 5, 3);
        CheckBox isActiveCheckBox = new CheckBox("Active");

        if (hotel != null) {
            nameField.setText(hotel.getName());
            cityField.setText(hotel.getCity());
            countryField.setText(hotel.getCountry());
            starRatingSpinner.getValueFactory().setValue(hotel.getStarRating());
            isActiveCheckBox.setSelected(hotel.getStatus().equals("Active"));
        }

        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);
        grid.setPadding(new Insets(20));

        grid.add(new Label("Name:"), 0, 0);
        grid.add(nameField, 1, 0);
        grid.add(new Label("Description:"), 0, 1);
        grid.add(descriptionField, 1, 1);
        grid.add(new Label("Address:"), 0, 2);
        grid.add(addressField, 1, 2);
        grid.add(new Label("City:"), 0, 3);
        grid.add(cityField, 1, 3);
        grid.add(new Label("Country:"), 0, 4);
        grid.add(countryField, 1, 4);
        grid.add(new Label("Latitude:"), 0, 5);
        grid.add(latitudeField, 1, 5);
        grid.add(new Label("Longitude:"), 0, 6);
        grid.add(longitudeField, 1, 6);
        grid.add(new Label("Star Rating:"), 0, 7);
        grid.add(starRatingSpinner, 1, 7);
        grid.add(isActiveCheckBox, 1, 8);

        dialog.getDialogPane().setContent(grid);
        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        dialog.setResultConverter(dialogButton -> {
            if (dialogButton == ButtonType.OK) {
                Hotel result = hotel != null ? hotel : new Hotel();
                result.setName(nameField.getText());
                result.setCity(cityField.getText());
                result.setCountry(countryField.getText());
                result.setStarRating(starRatingSpinner.getValue());
                result.setStatus(isActiveCheckBox.isSelected() ? "Active" : "Inactive");
                return result;
            }
            return null;
        });

        dialog.showAndWait().ifPresent(result -> {
            if (hotel == null) {
                addHotel(result);
            } else {
                updateHotel(result);
            }
        });
    }

    /**
     * Add a new hotel.
     */
    private void addHotel(Hotel hotel) {
        String query = "INSERT INTO hotels (name, city, country, star_rating, is_active, created_at, updated_at) " +
                      "VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setString(1, hotel.getName());
            stmt.setString(2, hotel.getCity());
            stmt.setString(3, hotel.getCountry());
            stmt.setInt(4, hotel.getStarRating());
            stmt.setBoolean(5, hotel.getStatus().equals("Active"));
            
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                showAlert(Alert.AlertType.INFORMATION, "Success", "Hotel added successfully.");
                loadHotels();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to add hotel: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Update an existing hotel.
     */
    private void updateHotel(Hotel hotel) {
        String query = "UPDATE hotels SET name = ?, city = ?, country = ?, star_rating = ?, " +
                      "is_active = ?, updated_at = NOW() WHERE id = ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setString(1, hotel.getName());
            stmt.setString(2, hotel.getCity());
            stmt.setString(3, hotel.getCountry());
            stmt.setInt(4, hotel.getStarRating());
            stmt.setBoolean(5, hotel.getStatus().equals("Active"));
            stmt.setInt(6, hotel.getId());
            
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                showAlert(Alert.AlertType.INFORMATION, "Success", "Hotel updated successfully.");
                loadHotels();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to update hotel: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Edit the selected hotel.
     */
    private void editSelectedHotel() {
        Hotel selected = hotelsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a hotel to edit.");
            return;
        }

        // Load full hotel data
        String query = "SELECT * FROM hotels WHERE id = ?";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setInt(1, selected.getId());
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                Hotel hotel = new Hotel();
                hotel.setId(rs.getInt("id"));
                hotel.setName(rs.getString("name"));
                hotel.setCity(rs.getString("city"));
                hotel.setCountry(rs.getString("country"));
                hotel.setStarRating(rs.getInt("star_rating"));
                hotel.setStatus(rs.getBoolean("is_active") ? "Active" : "Inactive");
                
                showHotelForm(hotel);
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load hotel: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Delete the selected hotel.
     */
    private void deleteSelectedHotel() {
        Hotel selected = hotelsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a hotel to delete.");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Delete Hotel");
        confirm.setHeaderText(null);
        confirm.setContentText("Are you sure you want to delete this hotel? This action cannot be undone.");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            String query = "DELETE FROM hotels WHERE id = ?";
            
            try (Connection conn = DatabaseConnection.getConnection();
                 PreparedStatement stmt = conn.prepareStatement(query)) {
                
                stmt.setInt(1, selected.getId());
                int rows = stmt.executeUpdate();
                
                if (rows > 0) {
                    showAlert(Alert.AlertType.INFORMATION, "Success", "Hotel deleted successfully.");
                    loadHotels();
                }
            } catch (SQLException e) {
                showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to delete hotel: " + e.getMessage());
                e.printStackTrace();
            }
        }
    }

    /**
     * Toggle hotel active status.
     */
    private void toggleHotelStatus() {
        Hotel selected = hotelsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a hotel.");
            return;
        }

        boolean newStatus = !selected.getStatus().equals("Active");
        String query = "UPDATE hotels SET is_active = ?, updated_at = NOW() WHERE id = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setBoolean(1, newStatus);
            stmt.setInt(2, selected.getId());
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                showAlert(Alert.AlertType.INFORMATION, "Success", 
                         "Hotel status updated to " + (newStatus ? "Active" : "Inactive") + ".");
                loadHotels();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to update hotel status: " + e.getMessage());
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

