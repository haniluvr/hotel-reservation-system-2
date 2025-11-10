package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.GraphEdge;
import com.belmonthotel.admin.models.GraphNode;
import com.belmonthotel.admin.models.Room;
import com.belmonthotel.admin.utils.DatabaseConnection;
import com.belmonthotel.admin.utils.RoomDependencyGraph;
import com.belmonthotel.admin.utils.SortAlgorithms;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import javafx.geometry.Insets;
import javafx.geometry.Pos;

import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;
import java.util.Map;
import java.util.NavigableMap;
import java.util.NavigableSet;
import java.util.ResourceBundle;
import java.util.TreeMap;
import java.util.TreeSet;
import javafx.stage.Modality;

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

    @FXML
    private Button viewAvailabilityBtn;

    @FXML
    private Button viewDependenciesBtn;

    @FXML
    private ComboBox<String> sortAlgorithmCombo;

    @FXML
    private ComboBox<String> sortFieldCombo;

    @FXML
    private Button sortBtn;

    @FXML
    private Label sortMetricsLabel;

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

        // Initialize sort algorithm options (Merge Sort for rooms)
        if (sortAlgorithmCombo != null) {
            sortAlgorithmCombo.getItems().addAll("Quick Sort", "Merge Sort", "Heap Sort");
            sortAlgorithmCombo.setValue("Merge Sort");
        }
        
        // Initialize sort field options
        if (sortFieldCombo != null) {
            sortFieldCombo.getItems().addAll("Price", "Availability", "Room Type", "Quantity");
            sortFieldCombo.setValue("Price");
        }

        // Set button actions
        addBtn.setOnAction(e -> showRoomForm(null));
        editBtn.setOnAction(e -> editSelectedRoom());
        deleteBtn.setOnAction(e -> deleteSelectedRoom());
        refreshBtn.setOnAction(e -> loadRooms());
        viewAvailabilityBtn.setOnAction(e -> viewRoomAvailability());
        if (viewDependenciesBtn != null) {
            viewDependenciesBtn.setOnAction(e -> viewRoomDependencies());
        }
        if (sortBtn != null) {
            sortBtn.setOnAction(e -> performSort());
        }

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
        
        String query = "SELECT r.id, r.room_type, r.description, r.price_per_night, r.quantity, r.available_quantity, " +
                      "r.max_guests, r.max_adults, r.max_children, r.amenities, r.is_active " +
                      "FROM rooms r " +
                      "ORDER BY r.room_type";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                Room room = new Room();
                room.setId(rs.getInt("id"));
                room.setHotelName("Belmont Hotel");
                room.setRoomType(rs.getString("room_type"));
                if (rs.getString("description") != null) {
                    room.setDescription(rs.getString("description"));
                }
                room.setPricePerNight(String.format("₱%.2f", rs.getDouble("price_per_night")));
                room.setQuantity(rs.getInt("quantity"));
                room.setAvailableQuantity(rs.getInt("available_quantity"));
                room.setMaxGuests(rs.getInt("max_guests"));
                room.setMaxAdults(rs.getInt("max_adults"));
                room.setMaxChildren(rs.getInt("max_children"));
                if (rs.getString("amenities") != null) {
                    // Parse JSON array to comma-separated string
                    String amenitiesJson = rs.getString("amenities");
                    if (amenitiesJson.startsWith("[") && amenitiesJson.endsWith("]")) {
                        amenitiesJson = amenitiesJson.substring(1, amenitiesJson.length() - 1);
                        amenitiesJson = amenitiesJson.replace("\"", "").replace(" ", "");
                        room.setAmenities(amenitiesJson.replace(",", ", "));
                    } else {
                        room.setAmenities(amenitiesJson);
                    }
                }
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
     * Perform sorting using selected algorithm (Merge Sort for rooms).
     */
    private void performSort() {
        ObservableList<Room> currentList = roomsTable.getItems();
        if (currentList.isEmpty()) {
            showAlert(Alert.AlertType.WARNING, "No Data", "No rooms to sort.");
            return;
        }
        
        String algorithm = sortAlgorithmCombo != null ? sortAlgorithmCombo.getValue() : "Merge Sort";
        String sortField = sortFieldCombo != null ? sortFieldCombo.getValue() : "Price";
        
        // Create comparator based on sort field
        Comparator<Room> comparator = getRoomComparator(sortField);
        
        // Convert to ArrayList for sorting
        List<Room> listToSort = new ArrayList<>(currentList);
        
        // Perform sort based on selected algorithm
        SortAlgorithms.SortResult<Room> result;
        switch (algorithm) {
            case "Quick Sort":
                result = SortAlgorithms.quickSort(listToSort, comparator);
                break;
            case "Merge Sort":
                result = SortAlgorithms.mergeSort(listToSort, comparator);
                break;
            case "Heap Sort":
                result = SortAlgorithms.heapSort(listToSort, comparator);
                break;
            default:
                result = SortAlgorithms.mergeSort(listToSort, comparator);
        }
        
        // Update table with sorted data
        ObservableList<Room> sortedList = FXCollections.observableArrayList(result.getSortedData());
        roomsTable.setItems(sortedList);
        
        // Update metrics label
        if (sortMetricsLabel != null) {
            sortMetricsLabel.setText(String.format("%s: %d ms, %d comparisons", 
                result.getAlgorithmName(), result.getExecutionTime(), result.getComparisons()));
            sortMetricsLabel.setTooltip(new Tooltip("Algorithm performance metrics"));
        }
        
        showAlert(Alert.AlertType.INFORMATION, "Sort Complete", 
            String.format("Sorted %d rooms using %s\nTime: %d ms\nComparisons: %d",
                listToSort.size(), result.getAlgorithmName(), 
                result.getExecutionTime(), result.getComparisons()));
    }
    
    /**
     * Get comparator for room based on sort field.
     */
    private Comparator<Room> getRoomComparator(String sortField) {
        switch (sortField) {
            case "Price":
                return Comparator.comparing(r -> {
                    try {
                        return Double.parseDouble(r.getPricePerNight().replace("₱", "").replace(",", ""));
                    } catch (NumberFormatException e) {
                        return 0.0;
                    }
                });
            case "Availability":
                return Comparator.comparing(Room::getAvailableQuantity).reversed();
            case "Room Type":
                return Comparator.comparing(Room::getRoomType, String.CASE_INSENSITIVE_ORDER);
            case "Quantity":
                return Comparator.comparing(Room::getQuantity).reversed();
            default:
                return Comparator.comparing(r -> {
                    try {
                        return Double.parseDouble(r.getPricePerNight().replace("₱", "").replace(",", ""));
                    } catch (NumberFormatException e) {
                        return 0.0;
                    }
                });
        }
    }

    /**
     * Show room form for add/edit.
     */
    private void showRoomForm(Room room) {
        // Hotels table removed, using hardcoded hotel name
        final int hotelId = 0; // hotel_id is now nullable, set to NULL
        final String hotelName = "Belmont Hotel";

        Dialog<Room> dialog = new Dialog<>();
        dialog.setTitle(room == null ? "Add Room" : "Edit Room");
        dialog.setHeaderText(null);

        // No hotel combo needed since there's only one hotel
        Label hotelLabel = new Label(hotelName);
        hotelLabel.setStyle("-fx-font-weight: bold;");
        TextField roomTypeField = new TextField();
        TextArea descriptionField = new TextArea();
        descriptionField.setPrefRowCount(3);
        descriptionField.setWrapText(true);
        TextField priceField = new TextField();
        Spinner<Integer> quantitySpinner = new Spinner<>(1, 100, 1);
        Spinner<Integer> maxGuestsSpinner = new Spinner<>(1, 20, 2);
        Spinner<Integer> maxAdultsSpinner = new Spinner<>(1, 20, 2);
        Spinner<Integer> maxChildrenSpinner = new Spinner<>(0, 10, 0);
        TextField amenitiesField = new TextField();
        amenitiesField.setPromptText("Comma-separated (e.g., WiFi, TV, AC, Mini Bar)");
        CheckBox isActiveCheckBox = new CheckBox("Active");

        if (room != null) {
            // Set existing values
            roomTypeField.setText(room.getRoomType());
            if (room.getDescription() != null) {
                descriptionField.setText(room.getDescription());
            }
            priceField.setText(room.getPricePerNight().replace("₱", "").replace(",", ""));
            quantitySpinner.getValueFactory().setValue(room.getQuantity());
            maxGuestsSpinner.getValueFactory().setValue(room.getMaxGuests());
            if (room.getMaxAdults() > 0) {
                maxAdultsSpinner.getValueFactory().setValue(room.getMaxAdults());
            }
            if (room.getMaxChildren() > 0) {
                maxChildrenSpinner.getValueFactory().setValue(room.getMaxChildren());
            }
            if (room.getAmenities() != null) {
                amenitiesField.setText(room.getAmenities());
            }
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
        grid.add(new Label("Max Adults:"), 0, 6);
        grid.add(maxAdultsSpinner, 1, 6);
        grid.add(new Label("Max Children:"), 0, 7);
        grid.add(maxChildrenSpinner, 1, 7);
        grid.add(new Label("Amenities:"), 0, 8);
        grid.add(amenitiesField, 1, 8);
        grid.add(isActiveCheckBox, 1, 9);

        dialog.getDialogPane().setContent(grid);
        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        dialog.setResultConverter(dialogButton -> {
            if (dialogButton == ButtonType.OK) {
                // Validate required fields
                if (roomTypeField.getText().trim().isEmpty()) {
                    showAlert(Alert.AlertType.ERROR, "Validation Error", "Room type is required.");
                    return null;
                }
                try {
                    Double.parseDouble(priceField.getText());
                } catch (NumberFormatException e) {
                    showAlert(Alert.AlertType.ERROR, "Validation Error", "Invalid price format.");
                    return null;
                }
                
                Room result = room != null ? room : new Room();
                result.setRoomType(roomTypeField.getText().trim());
                result.setDescription(descriptionField.getText().trim());
                result.setPricePerNight("₱" + priceField.getText());
                result.setQuantity(quantitySpinner.getValue());
                result.setMaxGuests(maxGuestsSpinner.getValue());
                result.setMaxAdults(maxAdultsSpinner.getValue());
                result.setMaxChildren(maxChildrenSpinner.getValue());
                result.setAmenities(amenitiesField.getText().trim());
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
        String query = "INSERT INTO rooms (hotel_id, room_type, description, price_per_night, quantity, available_quantity, " +
                      "max_guests, max_adults, max_children, amenities, is_active, created_at, updated_at) " +
                      "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            double price = Double.parseDouble(room.getPricePerNight().replace("₱", "").replace(",", ""));
            stmt.setNull(1, java.sql.Types.INTEGER); // hotel_id is now nullable
            stmt.setString(2, room.getRoomType());
            stmt.setString(3, room.getDescription());
            stmt.setDouble(4, price);
            stmt.setInt(5, room.getQuantity());
            stmt.setInt(6, room.getQuantity()); // available_quantity = quantity initially
            stmt.setInt(7, room.getMaxGuests());
            stmt.setInt(8, room.getMaxAdults() > 0 ? room.getMaxAdults() : room.getMaxGuests());
            stmt.setInt(9, room.getMaxChildren());
            
            // Convert amenities to JSON if provided
            String amenitiesJson = null;
            if (room.getAmenities() != null && !room.getAmenities().trim().isEmpty()) {
                String[] amenitiesArray = room.getAmenities().split(",");
                StringBuilder json = new StringBuilder("[");
                for (int i = 0; i < amenitiesArray.length; i++) {
                    if (i > 0) json.append(",");
                    json.append("\"").append(amenitiesArray[i].trim()).append("\"");
                }
                json.append("]");
                amenitiesJson = json.toString();
            }
            stmt.setString(10, amenitiesJson);
            stmt.setBoolean(11, room.getStatus().equals("Active"));
            
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
        // Validate quantity change
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement checkStmt = conn.prepareStatement(
                 "SELECT quantity, available_quantity FROM rooms WHERE id = ?")) {
            checkStmt.setInt(1, room.getId());
            ResultSet rs = checkStmt.executeQuery();
            if (rs.next()) {
                int currentQuantity = rs.getInt("quantity");
                int availableQuantity = rs.getInt("available_quantity");
                int bookedRooms = currentQuantity - availableQuantity;
                
                if (room.getQuantity() < bookedRooms) {
                    showAlert(Alert.AlertType.ERROR, "Validation Error", 
                        String.format("Cannot reduce quantity below %d. There are %d rooms currently booked.", 
                        bookedRooms, bookedRooms));
                    return;
                }
                
                // Update available_quantity if quantity changed
                int newAvailableQuantity = room.getQuantity() - bookedRooms;
                if (newAvailableQuantity < 0) newAvailableQuantity = 0;
                
                String query = "UPDATE rooms SET room_type = ?, description = ?, price_per_night = ?, quantity = ?, " +
                              "available_quantity = ?, max_guests = ?, max_adults = ?, max_children = ?, " +
                              "amenities = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
                
                try (PreparedStatement stmt = conn.prepareStatement(query)) {
                    double price = Double.parseDouble(room.getPricePerNight().replace("₱", "").replace(",", ""));
                    stmt.setString(1, room.getRoomType());
                    stmt.setString(2, room.getDescription());
                    stmt.setDouble(3, price);
                    stmt.setInt(4, room.getQuantity());
                    stmt.setInt(5, newAvailableQuantity);
                    stmt.setInt(6, room.getMaxGuests());
                    stmt.setInt(7, room.getMaxAdults() > 0 ? room.getMaxAdults() : room.getMaxGuests());
                    stmt.setInt(8, room.getMaxChildren());
                    
                    // Convert amenities to JSON if provided
                    String amenitiesJson = null;
                    if (room.getAmenities() != null && !room.getAmenities().trim().isEmpty()) {
                        String[] amenitiesArray = room.getAmenities().split(",");
                        StringBuilder json = new StringBuilder("[");
                        for (int i = 0; i < amenitiesArray.length; i++) {
                            if (i > 0) json.append(",");
                            json.append("\"").append(amenitiesArray[i].trim()).append("\"");
                        }
                        json.append("]");
                        amenitiesJson = json.toString();
                    }
                    stmt.setString(9, amenitiesJson);
                    stmt.setBoolean(10, room.getStatus().equals("Active"));
                    stmt.setInt(11, room.getId());
            
                    int rows = stmt.executeUpdate();
                    
                    if (rows > 0) {
                        showAlert(Alert.AlertType.INFORMATION, "Success", "Room updated successfully.");
                        loadRooms();
                    }
                }
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
                // hotel_id is now nullable, set to 0 if null
                int hotelId = rs.getInt("hotel_id");
                if (rs.wasNull()) hotelId = 0;
                room.setHotelId(hotelId);
                room.setRoomType(rs.getString("room_type"));
                if (rs.getString("description") != null) {
                    room.setDescription(rs.getString("description"));
                }
                room.setPricePerNight(String.format("₱%.2f", rs.getDouble("price_per_night")));
                room.setQuantity(rs.getInt("quantity"));
                room.setAvailableQuantity(rs.getInt("available_quantity"));
                room.setMaxGuests(rs.getInt("max_guests"));
                room.setMaxAdults(rs.getInt("max_adults"));
                room.setMaxChildren(rs.getInt("max_children"));
                if (rs.getString("amenities") != null) {
                    // Parse JSON array to comma-separated string
                    String amenitiesJson = rs.getString("amenities");
                    if (amenitiesJson.startsWith("[") && amenitiesJson.endsWith("]")) {
                        amenitiesJson = amenitiesJson.substring(1, amenitiesJson.length() - 1);
                        amenitiesJson = amenitiesJson.replace("\"", "").replace(" ", "");
                        room.setAmenities(amenitiesJson.replace(",", ", "));
                    } else {
                        room.setAmenities(amenitiesJson);
                    }
                }
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

        // Check if room has active bookings
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT COUNT(*) as count FROM reservations WHERE room_id = ? AND status NOT IN ('cancelled', 'no_show')")) {
            
            stmt.setInt(1, selected.getId());
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next() && rs.getInt("count") > 0) {
                showAlert(Alert.AlertType.ERROR, "Cannot Delete", 
                    "This room cannot be deleted because it has active bookings. Please cancel or complete all bookings first.");
                return;
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to check room bookings: " + e.getMessage());
            e.printStackTrace();
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

    /**
     * View room availability calendar using TreeMap (sorted by date) and TreeSet (sorted room types).
     */
    private void viewRoomAvailability() {
        Room selected = roomsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a room to view availability.");
            return;
        }

        Dialog<Void> dialog = new Dialog<>();
        dialog.setTitle("Room Availability - " + selected.getRoomType());
        dialog.setHeaderText("Booking Calendar (TreeMap/TreeSet Data Structure)");
        dialog.initModality(Modality.APPLICATION_MODAL);

        VBox content = new VBox(15);
        content.setPadding(new Insets(20));
        content.setPrefWidth(900);

        // Summary information
        HBox summaryBox = new HBox(20);
        summaryBox.setAlignment(Pos.CENTER_LEFT);
        Label totalLabel = new Label("Total Rooms: " + selected.getQuantity());
        Label availableLabel = new Label("Available: " + selected.getAvailableQuantity());
        Label bookedLabel = new Label("Booked: " + (selected.getQuantity() - selected.getAvailableQuantity()));
        summaryBox.getChildren().addAll(totalLabel, availableLabel, bookedLabel);
        content.getChildren().add(summaryBox);

        // Use TreeMap to store bookings sorted by date (TreeMap automatically sorts by key)
        TreeMap<LocalDate, List<BookingInfo>> bookingsByDate = new TreeMap<>();
        // Use TreeSet to store unique room types (alphabetically sorted)
        TreeSet<String> roomTypes = new TreeSet<>();
        roomTypes.add(selected.getRoomType());

        // Load bookings for this room
        String query = "SELECT r.id, r.reservation_number, r.check_in_date, r.check_out_date, " +
                      "r.status, u.name as guest_name " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "WHERE r.room_id = ? AND r.status NOT IN ('cancelled', 'no_show') " +
                      "ORDER BY r.check_in_date";

        TableView<BookingInfo> bookingsTable = new TableView<>();
        bookingsTable.setPrefHeight(400);

        TableColumn<BookingInfo, String> resNumCol = new TableColumn<>("Reservation #");
        resNumCol.setCellValueFactory(new javafx.scene.control.cell.PropertyValueFactory<>("reservationNumber"));
        resNumCol.setPrefWidth(150);

        TableColumn<BookingInfo, String> guestCol = new TableColumn<>("Guest");
        guestCol.setCellValueFactory(new javafx.scene.control.cell.PropertyValueFactory<>("guestName"));
        guestCol.setPrefWidth(150);

        TableColumn<BookingInfo, String> checkInCol = new TableColumn<>("Check-in");
        checkInCol.setCellValueFactory(new javafx.scene.control.cell.PropertyValueFactory<>("checkInDate"));
        checkInCol.setPrefWidth(120);

        TableColumn<BookingInfo, String> checkOutCol = new TableColumn<>("Check-out");
        checkOutCol.setCellValueFactory(new javafx.scene.control.cell.PropertyValueFactory<>("checkOutDate"));
        checkOutCol.setPrefWidth(120);

        TableColumn<BookingInfo, String> statusCol = new TableColumn<>("Status");
        statusCol.setCellValueFactory(new javafx.scene.control.cell.PropertyValueFactory<>("status"));
        statusCol.setPrefWidth(100);

        bookingsTable.getColumns().addAll(resNumCol, guestCol, checkInCol, checkOutCol, statusCol);

        javafx.collections.ObservableList<BookingInfo> bookings = javafx.collections.FXCollections.observableArrayList();

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setInt(1, selected.getId());
            ResultSet rs = stmt.executeQuery();

            while (rs.next()) {
                BookingInfo booking = new BookingInfo();
                booking.setReservationNumber(rs.getString("reservation_number"));
                booking.setGuestName(rs.getString("guest_name"));
                LocalDate checkIn = rs.getDate("check_in_date").toLocalDate();
                LocalDate checkOut = rs.getDate("check_out_date").toLocalDate();
                booking.setCheckInDate(checkIn.format(DateTimeFormatter.ofPattern("yyyy-MM-dd")));
                booking.setCheckOutDate(checkOut.format(DateTimeFormatter.ofPattern("yyyy-MM-dd")));
                booking.setStatus(rs.getString("status"));
                bookings.add(booking);
                
                // Populate TreeMap: group bookings by check-in date (TreeMap automatically sorts by date)
                bookingsByDate.putIfAbsent(checkIn, new ArrayList<>());
                bookingsByDate.get(checkIn).add(booking);
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load bookings: " + e.getMessage());
            e.printStackTrace();
        }

        bookingsTable.setItems(bookings);
        content.getChildren().add(bookingsTable);
        
        // Add TreeMap visualization section
        TitledPane treeMapPane = new TitledPane();
        treeMapPane.setText("TreeMap Structure (Sorted by Date)");
        treeMapPane.setExpanded(true);
        
        VBox treeMapContent = new VBox(10);
        treeMapContent.setPadding(new Insets(10));
        
        Label treeMapInfo = new Label("TreeMap automatically sorts bookings by check-in date:");
        treeMapInfo.setStyle("-fx-font-weight: bold;");
        treeMapContent.getChildren().add(treeMapInfo);
        
        // Display TreeMap entries (sorted by date)
        ScrollPane treeMapScroll = new ScrollPane();
        VBox treeMapEntries = new VBox(5);
        for (Map.Entry<LocalDate, List<BookingInfo>> entry : bookingsByDate.entrySet()) {
            Label dateLabel = new Label(entry.getKey().toString() + " (" + entry.getValue().size() + " booking(s)):");
            dateLabel.setStyle("-fx-font-weight: bold; -fx-text-fill: #2196F3;");
            treeMapEntries.getChildren().add(dateLabel);
            for (BookingInfo b : entry.getValue()) {
                Label bookingLabel = new Label("  - " + b.getReservationNumber() + " (" + b.getGuestName() + ")");
                treeMapEntries.getChildren().add(bookingLabel);
            }
        }
        treeMapScroll.setContent(treeMapEntries);
        treeMapScroll.setPrefHeight(150);
        treeMapContent.getChildren().add(treeMapScroll);
        
        treeMapPane.setContent(treeMapContent);
        content.getChildren().add(treeMapPane);
        
        // Add TreeSet visualization (sorted room types)
        TitledPane treeSetPane = new TitledPane();
        treeSetPane.setText("TreeSet Structure (Sorted Room Types)");
        treeSetPane.setExpanded(true);
        
        VBox treeSetContent = new VBox(10);
        treeSetContent.setPadding(new Insets(10));
        
        Label treeSetInfo = new Label("TreeSet automatically sorts room types alphabetically:");
        treeSetInfo.setStyle("-fx-font-weight: bold;");
        treeSetContent.getChildren().add(treeSetInfo);
        
        // Load all room types into TreeSet
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement("SELECT DISTINCT room_type FROM rooms ORDER BY room_type")) {
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                roomTypes.add(rs.getString("room_type"));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        
        Label roomTypesLabel = new Label("Sorted Room Types: " + String.join(", ", roomTypes));
        roomTypesLabel.setWrapText(true);
        treeSetContent.getChildren().add(roomTypesLabel);
        
        treeSetPane.setContent(treeSetContent);
        content.getChildren().add(treeSetPane);
        
        // Add Binary Search Date Picker
        HBox binarySearchBox = new HBox(10);
        binarySearchBox.setAlignment(Pos.CENTER_LEFT);
        Label searchLabel = new Label("Binary Search - Find Next Available Date:");
        DatePicker searchDatePicker = new DatePicker(LocalDate.now());
        Button searchBtn = new Button("Search");
        Label searchResultLabel = new Label();
        searchResultLabel.setStyle("-fx-font-weight: bold;");
        
        searchBtn.setOnAction(e -> {
            LocalDate searchDate = searchDatePicker.getValue();
            if (searchDate == null) {
                searchResultLabel.setText("Please select a date");
                return;
            }
            
            // Binary search using TreeMap's navigable methods
            NavigableMap<LocalDate, List<BookingInfo>> tailMap = bookingsByDate.tailMap(searchDate, true);
            if (tailMap.isEmpty()) {
                searchResultLabel.setText("No bookings found after " + searchDate + " (Room is available)");
                searchResultLabel.setStyle("-fx-text-fill: green; -fx-font-weight: bold;");
            } else {
                LocalDate nextBookingDate = tailMap.firstKey();
                searchResultLabel.setText("Next booking found on: " + nextBookingDate);
                searchResultLabel.setStyle("-fx-text-fill: orange; -fx-font-weight: bold;");
            }
        });
        
        binarySearchBox.getChildren().addAll(searchLabel, searchDatePicker, searchBtn, searchResultLabel);
        content.getChildren().add(binarySearchBox);

        // Conflict detection
        Label conflictLabel = new Label();
        conflictLabel.setStyle("-fx-text-fill: red; -fx-font-weight: bold;");
        conflictLabel.setWrapText(true);
        
        // Check for date conflicts
        StringBuilder conflicts = new StringBuilder();
        for (int i = 0; i < bookings.size(); i++) {
            for (int j = i + 1; j < bookings.size(); j++) {
                BookingInfo b1 = bookings.get(i);
                BookingInfo b2 = bookings.get(j);
                LocalDate b1In = LocalDate.parse(b1.getCheckInDate());
                LocalDate b1Out = LocalDate.parse(b1.getCheckOutDate());
                LocalDate b2In = LocalDate.parse(b2.getCheckInDate());
                LocalDate b2Out = LocalDate.parse(b2.getCheckOutDate());
                
                if ((b1In.isBefore(b2Out) && b1Out.isAfter(b2In))) {
                    conflicts.append("Conflict: ").append(b1.getReservationNumber())
                            .append(" overlaps with ").append(b2.getReservationNumber()).append("\n");
                }
            }
        }
        
        if (conflicts.length() > 0) {
            conflictLabel.setText("WARNING: Date conflicts detected!\n" + conflicts.toString());
            content.getChildren().add(conflictLabel);
        }

        dialog.getDialogPane().setContent(content);
        dialog.getDialogPane().getButtonTypes().add(ButtonType.CLOSE);
        dialog.showAndWait();
    }
    
    /**
     * View room dependencies using Graph data structure (BFS, DFS, Shortest Path).
     */
    private void viewRoomDependencies() {
        Dialog<Void> dialog = new Dialog<>();
        dialog.setTitle("Room Dependencies (Graph Data Structure)");
        dialog.setHeaderText("Room Dependency Network with Graph Algorithms");
        dialog.initModality(Modality.APPLICATION_MODAL);
        
        VBox content = new VBox(15);
        content.setPadding(new Insets(20));
        content.setPrefWidth(900);
        
        // Build graph from rooms
        RoomDependencyGraph graph = buildRoomDependencyGraph();
        
        // Graph Statistics
        Map<String, Object> stats = graph.getStatistics();
        Label statsLabel = new Label(String.format(
            "Graph Statistics: %d nodes, %d edges, %d connected components",
            stats.get("nodes"), stats.get("edges"), stats.get("connectedComponents")
        ));
        statsLabel.setStyle("-fx-font-weight: bold; -fx-font-size: 14px;");
        content.getChildren().add(statsLabel);
        
        // Graph Visualization (simple text representation)
        TitledPane graphVizPane = new TitledPane();
        graphVizPane.setText("Graph Structure (Nodes and Edges)");
        graphVizPane.setExpanded(true);
        
        ScrollPane graphScroll = new ScrollPane();
        VBox graphContent = new VBox(10);
        graphContent.setPadding(new Insets(10));
        
        // Display nodes
        Label nodesLabel = new Label("Nodes (Rooms):");
        nodesLabel.setStyle("-fx-font-weight: bold;");
        graphContent.getChildren().add(nodesLabel);
        
        for (GraphNode node : graph.getAllNodes()) {
            Label nodeLabel = new Label("  " + node.toString());
            nodeLabel.setStyle(node.isAvailable() ? "-fx-text-fill: green;" : "-fx-text-fill: red;");
            graphContent.getChildren().add(nodeLabel);
        }
        
        // Display edges
        Label edgesLabel = new Label("\nEdges (Dependencies):");
        edgesLabel.setStyle("-fx-font-weight: bold;");
        graphContent.getChildren().add(edgesLabel);
        
        for (GraphEdge edge : graph.getAllEdges()) {
            Label edgeLabel = new Label("  " + edge.toString());
            graphContent.getChildren().add(edgeLabel);
        }
        
        graphScroll.setContent(graphContent);
        graphScroll.setPrefHeight(200);
        graphVizPane.setContent(graphScroll);
        content.getChildren().add(graphVizPane);
        
        // Algorithm Controls
        TitledPane algoPane = new TitledPane();
        algoPane.setText("Graph Algorithms (BFS, DFS, Shortest Path)");
        algoPane.setExpanded(true);
        
        VBox algoContent = new VBox(15);
        algoContent.setPadding(new Insets(15));
        
        // BFS Section
        HBox bfsBox = new HBox(10);
        bfsBox.setAlignment(Pos.CENTER_LEFT);
        ComboBox<Integer> bfsStartCombo = new ComboBox<>();
        bfsStartCombo.setPromptText("Select Start Room");
        for (GraphNode node : graph.getAllNodes()) {
            bfsStartCombo.getItems().add(node.getRoomId());
        }
        Button bfsBtn = new Button("BFS - Find Available Chain");
        Label bfsResultLabel = new Label();
        bfsResultLabel.setWrapText(true);
        
        bfsBtn.setOnAction(e -> {
            Integer startRoom = bfsStartCombo.getValue();
            if (startRoom != null) {
                List<Integer> chain = graph.bfsFindAvailableChain(startRoom);
                bfsResultLabel.setText("BFS Chain: " + chain.toString() + 
                    " (Found " + chain.size() + " available rooms)");
                bfsResultLabel.setStyle("-fx-text-fill: green; -fx-font-weight: bold;");
            }
        });
        
        bfsBox.getChildren().addAll(new Label("BFS Start Room:"), bfsStartCombo, bfsBtn);
        algoContent.getChildren().addAll(bfsBox, bfsResultLabel);
        
        // DFS Section
        HBox dfsBox = new HBox(10);
        dfsBox.setAlignment(Pos.CENTER_LEFT);
        ComboBox<Integer> dfsStartCombo = new ComboBox<>();
        dfsStartCombo.setPromptText("Select Start Room");
        for (GraphNode node : graph.getAllNodes()) {
            dfsStartCombo.getItems().add(node.getRoomId());
        }
        Button dfsBtn = new Button("DFS - Traverse Dependencies");
        Label dfsResultLabel = new Label();
        dfsResultLabel.setWrapText(true);
        
        dfsBtn.setOnAction(e -> {
            Integer startRoom = dfsStartCombo.getValue();
            if (startRoom != null) {
                List<Integer> traversal = graph.dfsTraverseDependencies(startRoom);
                dfsResultLabel.setText("DFS Traversal: " + traversal.toString() + 
                    " (Visited " + traversal.size() + " rooms)");
                dfsResultLabel.setStyle("-fx-text-fill: blue; -fx-font-weight: bold;");
            }
        });
        
        dfsBox.getChildren().addAll(new Label("DFS Start Room:"), dfsStartCombo, dfsBtn);
        algoContent.getChildren().addAll(dfsBox, dfsResultLabel);
        
        // Shortest Path Section
        HBox pathBox = new HBox(10);
        pathBox.setAlignment(Pos.CENTER_LEFT);
        ComboBox<Integer> fromCombo = new ComboBox<>();
        fromCombo.setPromptText("From Room");
        ComboBox<Integer> toCombo = new ComboBox<>();
        toCombo.setPromptText("To Room");
        for (GraphNode node : graph.getAllNodes()) {
            fromCombo.getItems().add(node.getRoomId());
            toCombo.getItems().add(node.getRoomId());
        }
        Button pathBtn = new Button("Find Shortest Path");
        Label pathResultLabel = new Label();
        pathResultLabel.setWrapText(true);
        
        pathBtn.setOnAction(e -> {
            Integer from = fromCombo.getValue();
            Integer to = toCombo.getValue();
            if (from != null && to != null) {
                List<Integer> path = graph.shortestPath(from, to);
                if (path.isEmpty()) {
                    pathResultLabel.setText("No path found between room " + from + " and room " + to);
                    pathResultLabel.setStyle("-fx-text-fill: red; -fx-font-weight: bold;");
                } else {
                    pathResultLabel.setText("Shortest Path: " + path.toString() + 
                        " (Path length: " + (path.size() - 1) + " edges)");
                    pathResultLabel.setStyle("-fx-text-fill: purple; -fx-font-weight: bold;");
                }
            }
        });
        
        pathBox.getChildren().addAll(new Label("From:"), fromCombo, new Label("To:"), toCombo, pathBtn);
        algoContent.getChildren().addAll(pathBox, pathResultLabel);
        
        algoPane.setContent(algoContent);
        content.getChildren().add(algoPane);
        
        dialog.getDialogPane().setContent(content);
        dialog.getDialogPane().getButtonTypes().add(ButtonType.CLOSE);
        dialog.showAndWait();
    }
    
    /**
     * Build room dependency graph from database.
     */
    private RoomDependencyGraph buildRoomDependencyGraph() {
        RoomDependencyGraph graph = new RoomDependencyGraph();
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(
                 "SELECT r.id, r.room_type, r.available_quantity, r.quantity " +
                 "FROM rooms r ORDER BY r.id")) {
            
            ResultSet rs = stmt.executeQuery();
            List<GraphNode> nodes = new ArrayList<>();
            
            // Create nodes
            while (rs.next()) {
                int roomId = rs.getInt("id");
                String roomType = rs.getString("room_type");
                int available = rs.getInt("available_quantity");
                int total = rs.getInt("quantity");
                boolean isAvailable = available > 0;
                
                GraphNode node = new GraphNode(roomId, roomType, isAvailable);
                graph.addNode(node);
                nodes.add(node);
            }
            
            // Create edges (dependencies)
            // Example: Suite rooms require adjacent standard rooms
            // Connecting rooms are linked
            for (int i = 0; i < nodes.size(); i++) {
                GraphNode node1 = nodes.get(i);
                
                // Add dependencies based on room type
                if (node1.getRoomType().toLowerCase().contains("suite")) {
                    // Suite requires adjacent standard room
                    for (GraphNode node2 : nodes) {
                        if (node2.getRoomId() != node1.getRoomId() && 
                            node2.getRoomType().toLowerCase().contains("standard")) {
                            graph.addEdge(node1, node2, "suite_requires", 1);
                        }
                    }
                }
                
                // Connect adjacent rooms (simplified: connect rooms with consecutive IDs)
                if (i < nodes.size() - 1) {
                    GraphNode node2 = nodes.get(i + 1);
                    graph.addEdge(node1, node2, "adjacent", 1);
                    graph.addEdge(node2, node1, "adjacent", 1); // Bidirectional
                }
            }
            
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", 
                "Failed to build dependency graph: " + e.getMessage());
            e.printStackTrace();
        }
        
        return graph;
    }

    /**
     * Simple class to hold booking information for availability view.
     */
    public static class BookingInfo {
        private String reservationNumber;
        private String guestName;
        private String checkInDate;
        private String checkOutDate;
        private String status;

        public String getReservationNumber() { return reservationNumber; }
        public void setReservationNumber(String reservationNumber) { this.reservationNumber = reservationNumber; }

        public String getGuestName() { return guestName; }
        public void setGuestName(String guestName) { this.guestName = guestName; }

        public String getCheckInDate() { return checkInDate; }
        public void setCheckInDate(String checkInDate) { this.checkInDate = checkInDate; }

        public String getCheckOutDate() { return checkOutDate; }
        public void setCheckOutDate(String checkOutDate) { this.checkOutDate = checkOutDate; }

        public String getStatus() { return status; }
        public void setStatus(String status) { this.status = status; }
    }

    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

