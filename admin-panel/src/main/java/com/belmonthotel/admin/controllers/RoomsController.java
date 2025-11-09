package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.Room;
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
 * Controller for the rooms management module.
 */
public class RoomsController implements Initializable {
    @FXML
    private TableView<Room> roomsTable;

    @FXML
    private TableColumn<Room, Integer> idColumn;

    @FXML
    private TableColumn<Room, String> hotelColumn;

    @FXML
    private TableColumn<Room, String> roomTypeColumn;

    @FXML
    private TableColumn<Room, String> priceColumn;

    @FXML
    private TableColumn<Room, Integer> quantityColumn;

    @FXML
    private TableColumn<Room, Integer> availableColumn;

    @FXML
    private TableColumn<Room, Integer> maxGuestsColumn;

    @FXML
    private TableColumn<Room, String> statusColumn;

    @FXML
    private TextField searchField;

    @FXML
    private Button addBtn;

    @FXML
    private Button editBtn;

    @FXML
    private Button deleteBtn;

    @FXML
    private Button refreshBtn;

    private ObservableList<Room> roomsList;
    private ObservableList<String> hotelNames;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        roomsList = FXCollections.observableArrayList();
        hotelNames = FXCollections.observableArrayList();

        // Initialize table columns
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        hotelColumn.setCellValueFactory(new PropertyValueFactory<>("hotelName"));
        roomTypeColumn.setCellValueFactory(new PropertyValueFactory<>("roomType"));
        priceColumn.setCellValueFactory(new PropertyValueFactory<>("pricePerNight"));
        quantityColumn.setCellValueFactory(new PropertyValueFactory<>("quantity"));
        availableColumn.setCellValueFactory(new PropertyValueFactory<>("availableQuantity"));
        maxGuestsColumn.setCellValueFactory(new PropertyValueFactory<>("maxGuests"));
        statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));

        // Load hotel names for filter
        loadHotelNames();

        // Set button actions
        addBtn.setOnAction(e -> showRoomForm(null));
        editBtn.setOnAction(e -> editSelectedRoom());
        deleteBtn.setOnAction(e -> deleteSelectedRoom());
        refreshBtn.setOnAction(e -> loadRooms());

        // Set filter actions
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());

        // Load initial data
        loadRooms();
    }

    /**
     * Load hotel names for filter dropdown.
     * Since there's only one hotel, no filter needed.
     */
    private void loadHotelNames() {
        hotelNames.clear();
        // Only one hotel, so no filter needed
    }

    /**
     * Load rooms from database.
     */
    private void loadRooms() {
        roomsList.clear();
        
        String query = "SELECT r.id, r.room_type, r.price_per_night, r.quantity, r.available_quantity, " +
                      "r.max_guests, r.is_active, h.name as hotel_name " +
                      "FROM rooms r " +
                      "JOIN hotels h ON r.hotel_id = h.id " +
                      "ORDER BY h.name, r.room_type";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                Room room = new Room();
                room.setId(rs.getInt("id"));
                room.setHotelName(rs.getString("hotel_name"));
                room.setRoomType(rs.getString("room_type"));
                room.setPricePerNight(String.format("₱%.2f", rs.getDouble("price_per_night")));
                room.setQuantity(rs.getInt("quantity"));
                room.setAvailableQuantity(rs.getInt("available_quantity"));
                room.setMaxGuests(rs.getInt("max_guests"));
                room.setStatus(rs.getBoolean("is_active") ? "Active" : "Inactive");
                
                roomsList.add(room);
            }

            applyFilters();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load rooms: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Apply filters to the rooms list.
     */
    private void applyFilters() {
        ObservableList<Room> filtered = FXCollections.observableArrayList();
        String searchText = searchField.getText().toLowerCase();

        for (Room room : roomsList) {
            // Search filter
            if (!searchText.isEmpty()) {
                if (!room.getRoomType().toLowerCase().contains(searchText)) {
                    continue;
                }
            }

            filtered.add(room);
        }

        roomsTable.setItems(filtered);
    }

    /**
     * Show room form for add/edit.
     */
    private void showRoomForm(Room room) {
        // Get the only hotel (Belmont Hotel)
        final int[] hotelIdArray = {1}; // Since there's only one hotel
        String[] hotelNameArray = {"Belmont Hotel"};
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement("SELECT id, name FROM hotels LIMIT 1");
             ResultSet rs = stmt.executeQuery()) {
            
            if (rs.next()) {
                hotelIdArray[0] = rs.getInt("id");
                hotelNameArray[0] = rs.getString("name");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        
        final int hotelId = hotelIdArray[0];
        final String hotelName = hotelNameArray[0];

        Dialog<Room> dialog = new Dialog<>();
        dialog.setTitle(room == null ? "Add Room" : "Edit Room");
        dialog.setHeaderText(null);

        // No hotel combo needed since there's only one hotel
        Label hotelLabel = new Label(hotelName);
        hotelLabel.setStyle("-fx-font-weight: bold;");
        TextField roomTypeField = new TextField();
        TextArea descriptionField = new TextArea();
        TextField priceField = new TextField();
        Spinner<Integer> quantitySpinner = new Spinner<>(1, 100, 1);
        Spinner<Integer> maxGuestsSpinner = new Spinner<>(1, 10, 2);
        CheckBox isActiveCheckBox = new CheckBox("Active");

        if (room != null) {
            // Set existing values
            roomTypeField.setText(room.getRoomType());
            priceField.setText(room.getPricePerNight().replace("₱", ""));
            quantitySpinner.getValueFactory().setValue(room.getQuantity());
            maxGuestsSpinner.getValueFactory().setValue(room.getMaxGuests());
            isActiveCheckBox.setSelected(room.getStatus().equals("Active"));
        }

        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);
        grid.setPadding(new Insets(20));

        grid.add(new Label("Hotel:"), 0, 0);
        grid.add(hotelLabel, 1, 0);
        grid.add(new Label("Room Type:"), 0, 1);
        grid.add(roomTypeField, 1, 1);
        grid.add(new Label("Description:"), 0, 2);
        grid.add(descriptionField, 1, 2);
        grid.add(new Label("Price per Night:"), 0, 3);
        grid.add(priceField, 1, 3);
        grid.add(new Label("Quantity:"), 0, 4);
        grid.add(quantitySpinner, 1, 4);
        grid.add(new Label("Max Guests:"), 0, 5);
        grid.add(maxGuestsSpinner, 1, 5);
        grid.add(isActiveCheckBox, 1, 6);

        dialog.getDialogPane().setContent(grid);
        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        dialog.setResultConverter(dialogButton -> {
            if (dialogButton == ButtonType.OK) {
                Room result = room != null ? room : new Room();
                result.setRoomType(roomTypeField.getText());
                result.setPricePerNight("₱" + priceField.getText());
                result.setQuantity(quantitySpinner.getValue());
                result.setMaxGuests(maxGuestsSpinner.getValue());
                result.setStatus(isActiveCheckBox.isSelected() ? "Active" : "Inactive");
                
                // Set hotel ID (only one hotel)
                result.setHotelId(hotelId);
                
                return result;
            }
            return null;
        });

        dialog.showAndWait().ifPresent(result -> {
            if (room == null) {
                addRoom(result);
            } else {
                updateRoom(result);
            }
        });
    }

    /**
     * Add a new room.
     */
    private void addRoom(Room room) {
        String query = "INSERT INTO rooms (hotel_id, room_type, price_per_night, quantity, available_quantity, " +
                      "max_guests, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            double price = Double.parseDouble(room.getPricePerNight().replace("₱", ""));
            stmt.setInt(1, room.getHotelId());
            stmt.setString(2, room.getRoomType());
            stmt.setDouble(3, price);
            stmt.setInt(4, room.getQuantity());
            stmt.setInt(5, room.getQuantity()); // available_quantity = quantity initially
            stmt.setInt(6, room.getMaxGuests());
            stmt.setBoolean(7, room.getStatus().equals("Active"));
            
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                showAlert(Alert.AlertType.INFORMATION, "Success", "Room added successfully.");
                loadRooms();
            }
        } catch (SQLException | NumberFormatException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to add room: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Update an existing room.
     */
    private void updateRoom(Room room) {
        String query = "UPDATE rooms SET room_type = ?, price_per_night = ?, quantity = ?, " +
                      "max_guests = ?, is_active = ?, updated_at = NOW() WHERE id = ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            double price = Double.parseDouble(room.getPricePerNight().replace("₱", ""));
            stmt.setString(1, room.getRoomType());
            stmt.setDouble(2, price);
            stmt.setInt(3, room.getQuantity());
            stmt.setInt(4, room.getMaxGuests());
            stmt.setBoolean(5, room.getStatus().equals("Active"));
            stmt.setInt(6, room.getId());
            
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                showAlert(Alert.AlertType.INFORMATION, "Success", "Room updated successfully.");
                loadRooms();
            }
        } catch (SQLException | NumberFormatException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to update room: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Edit the selected room.
     */
    private void editSelectedRoom() {
        Room selected = roomsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a room to edit.");
            return;
        }

        // Load full room data
        String query = "SELECT * FROM rooms WHERE id = ?";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setInt(1, selected.getId());
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                Room room = new Room();
                room.setId(rs.getInt("id"));
                room.setHotelId(rs.getInt("hotel_id"));
                room.setRoomType(rs.getString("room_type"));
                room.setPricePerNight(String.format("₱%.2f", rs.getDouble("price_per_night")));
                room.setQuantity(rs.getInt("quantity"));
                room.setAvailableQuantity(rs.getInt("available_quantity"));
                room.setMaxGuests(rs.getInt("max_guests"));
                room.setStatus(rs.getBoolean("is_active") ? "Active" : "Inactive");
                
                showRoomForm(room);
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load room: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Delete the selected room.
     */
    private void deleteSelectedRoom() {
        Room selected = roomsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a room to delete.");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Delete Room");
        confirm.setHeaderText(null);
        confirm.setContentText("Are you sure you want to delete this room? This action cannot be undone.");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            String query = "DELETE FROM rooms WHERE id = ?";
            
            try (Connection conn = DatabaseConnection.getConnection();
                 PreparedStatement stmt = conn.prepareStatement(query)) {
                
                stmt.setInt(1, selected.getId());
                int rows = stmt.executeUpdate();
                
                if (rows > 0) {
                    showAlert(Alert.AlertType.INFORMATION, "Success", "Room deleted successfully.");
                    loadRooms();
                }
            } catch (SQLException e) {
                showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to delete room: " + e.getMessage());
                e.printStackTrace();
            }
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

