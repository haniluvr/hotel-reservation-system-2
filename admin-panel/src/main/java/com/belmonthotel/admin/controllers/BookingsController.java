package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.controllers.dialogs.BookingDetailsDialog;
import com.belmonthotel.admin.models.Booking;
import com.belmonthotel.admin.utils.DatabaseConnection;
import com.belmonthotel.admin.utils.SortAlgorithms;
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
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;
import java.util.ResourceBundle;

/**
 * Controller for the bookings management module.
 */
public class BookingsController implements Initializable {
    @FXML
    private TableView<Booking> bookingsTable;

    @FXML
    private TableColumn<Booking, Integer> idColumn;

    @FXML
    private TableColumn<Booking, String> reservationNumberColumn;

    @FXML
    private TableColumn<Booking, String> guestNameColumn;

    @FXML
    private TableColumn<Booking, String> hotelColumn;

    @FXML
    private TableColumn<Booking, String> roomColumn;

    @FXML
    private TableColumn<Booking, String> checkInColumn;

    @FXML
    private TableColumn<Booking, String> checkOutColumn;

    @FXML
    private TableColumn<Booking, String> statusColumn;

    @FXML
    private TableColumn<Booking, String> amountColumn;

    @FXML
    private TextField searchField;

    @FXML
    private TextField emailSearchField;

    @FXML
    private ComboBox<String> statusFilter;

    @FXML
    private ComboBox<String> roomTypeFilter;

    @FXML
    private DatePicker dateFromFilter;

    @FXML
    private DatePicker dateToFilter;

    @FXML
    private TextField amountMinField;

    @FXML
    private TextField amountMaxField;

    @FXML
    private Button clearFiltersBtn;

    @FXML
    private Button refreshBtn;

    @FXML
    private Button confirmBtn;

    @FXML
    private Button cancelBtn;

    @FXML
    private Button viewDetailsBtn;

    @FXML
    private ComboBox<String> sortAlgorithmCombo;

    @FXML
    private ComboBox<String> sortFieldCombo;

    @FXML
    private Button sortBtn;

    @FXML
    private Label sortMetricsLabel;

    private ObservableList<Booking> bookingsList;
    private ObservableList<Booking> filteredList;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        bookingsList = FXCollections.observableArrayList();
        filteredList = FXCollections.observableArrayList();

        // Initialize table columns
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        reservationNumberColumn.setCellValueFactory(new PropertyValueFactory<>("reservationNumber"));
        guestNameColumn.setCellValueFactory(new PropertyValueFactory<>("guestName"));
        hotelColumn.setCellValueFactory(new PropertyValueFactory<>("hotelName"));
        roomColumn.setCellValueFactory(new PropertyValueFactory<>("roomType"));
        checkInColumn.setCellValueFactory(new PropertyValueFactory<>("checkInDate"));
        checkOutColumn.setCellValueFactory(new PropertyValueFactory<>("checkOutDate"));
        statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));
        amountColumn.setCellValueFactory(new PropertyValueFactory<>("totalAmount"));

        // Set status filter options
        statusFilter.getItems().addAll("All", "Pending", "Confirmed", "Cancelled", "Completed", "No Show");
        statusFilter.setValue("All");

        // Load room types for filter
        loadRoomTypes();

        // Initialize sort algorithm options
        if (sortAlgorithmCombo != null) {
            sortAlgorithmCombo.getItems().addAll("Quick Sort", "Merge Sort", "Heap Sort");
            sortAlgorithmCombo.setValue("Quick Sort");
        }
        
        // Initialize sort field options
        if (sortFieldCombo != null) {
            sortFieldCombo.getItems().addAll("Date (Check-in)", "Amount", "Guest Name", "Status");
            sortFieldCombo.setValue("Date (Check-in)");
        }

        // Set button actions
        refreshBtn.setOnAction(e -> loadBookings());
        confirmBtn.setOnAction(e -> confirmSelectedBooking());
        cancelBtn.setOnAction(e -> cancelSelectedBooking());
        viewDetailsBtn.setOnAction(e -> viewBookingDetails());
        clearFiltersBtn.setOnAction(e -> clearFilters());
        if (sortBtn != null) {
            sortBtn.setOnAction(e -> performSort());
        }

        // Set filter actions
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        emailSearchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        statusFilter.setOnAction(e -> applyFilters());
        roomTypeFilter.setOnAction(e -> applyFilters());
        dateFromFilter.valueProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        dateToFilter.valueProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        amountMinField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        amountMaxField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());

        // Load initial data
        loadBookings();
    }

    /**
     * Load room types for filter dropdown.
     */
    private void loadRoomTypes() {
        roomTypeFilter.getItems().clear();
        roomTypeFilter.getItems().add("All");
        
        String query = "SELECT DISTINCT room_type FROM rooms ORDER BY room_type";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            
            while (rs.next()) {
                roomTypeFilter.getItems().add(rs.getString("room_type"));
            }
            
            roomTypeFilter.setValue("All");
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    /**
     * Load bookings from database.
     */
    private void loadBookings() {
        bookingsList.clear();
        
        String query = "SELECT r.id, r.reservation_number, r.check_in_date, r.check_out_date, " +
                      "r.status, r.total_amount, r.adults, r.children, " +
                      "u.name as guest_name, u.email as guest_email, " +
                      "rm.room_type " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "ORDER BY r.created_at DESC";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                Booking booking = new Booking();
                booking.setId(rs.getInt("id"));
                booking.setReservationNumber(rs.getString("reservation_number"));
                booking.setGuestName(rs.getString("guest_name"));
                booking.setGuestEmail(rs.getString("guest_email"));
                booking.setHotelName("Belmont Hotel");
                booking.setRoomType(rs.getString("room_type"));
                booking.setCheckInDate(rs.getDate("check_in_date").toLocalDate().format(DateTimeFormatter.ofPattern("yyyy-MM-dd")));
                booking.setCheckOutDate(rs.getDate("check_out_date").toLocalDate().format(DateTimeFormatter.ofPattern("yyyy-MM-dd")));
                booking.setStatus(rs.getString("status"));
                booking.setTotalAmount(String.format("₱%.2f", rs.getDouble("total_amount")));
                booking.setAdults(rs.getInt("adults"));
                booking.setChildren(rs.getInt("children"));
                
                bookingsList.add(booking);
            }

            applyFilters();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load bookings: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Apply filters to the bookings list.
     */
    private void applyFilters() {
        filteredList.clear();
        
        String searchText = searchField.getText().toLowerCase();
        String emailText = emailSearchField.getText().toLowerCase();
        String status = statusFilter.getValue();
        String roomType = roomTypeFilter.getValue();
        LocalDate fromDate = dateFromFilter.getValue();
        LocalDate toDate = dateToFilter.getValue();
        
        double minAmount = -1;
        double maxAmount = -1;
        try {
            if (!amountMinField.getText().isEmpty()) {
                minAmount = Double.parseDouble(amountMinField.getText());
            }
        } catch (NumberFormatException e) {
            // Invalid number, ignore
        }
        try {
            if (!amountMaxField.getText().isEmpty()) {
                maxAmount = Double.parseDouble(amountMaxField.getText());
            }
        } catch (NumberFormatException e) {
            // Invalid number, ignore
        }

        for (Booking booking : bookingsList) {
            // Search filter
            if (!searchText.isEmpty()) {
                if (!booking.getReservationNumber().toLowerCase().contains(searchText) &&
                    !booking.getGuestName().toLowerCase().contains(searchText) &&
                    !booking.getHotelName().toLowerCase().contains(searchText)) {
                    continue;
                }
            }

            // Email search filter
            if (!emailText.isEmpty()) {
                if (!booking.getGuestEmail().toLowerCase().contains(emailText)) {
                    continue;
                }
            }

            // Status filter
            if (status != null && !status.equals("All")) {
                if (!booking.getStatus().equalsIgnoreCase(status)) {
                    continue;
                }
            }

            // Room type filter
            if (roomType != null && !roomType.equals("All")) {
                if (!booking.getRoomType().equalsIgnoreCase(roomType)) {
                    continue;
                }
            }

            // Date filters
            if (fromDate != null) {
                LocalDate checkIn = LocalDate.parse(booking.getCheckInDate());
                if (checkIn.isBefore(fromDate)) {
                    continue;
                }
            }

            if (toDate != null) {
                LocalDate checkIn = LocalDate.parse(booking.getCheckInDate());
                if (checkIn.isAfter(toDate)) {
                    continue;
                }
            }

            // Amount range filter
            if (minAmount >= 0 || maxAmount >= 0) {
                try {
                    double amount = Double.parseDouble(booking.getTotalAmount().replace("₱", "").replace(",", ""));
                    if (minAmount >= 0 && amount < minAmount) {
                        continue;
                    }
                    if (maxAmount >= 0 && amount > maxAmount) {
                        continue;
                    }
                } catch (NumberFormatException e) {
                    // If we can't parse the amount, skip this filter
                }
            }

            filteredList.add(booking);
        }

        bookingsTable.setItems(filteredList);
    }
    
    /**
     * Clear all filters.
     */
    private void clearFilters() {
        searchField.clear();
        emailSearchField.clear();
        statusFilter.setValue("All");
        roomTypeFilter.setValue("All");
        dateFromFilter.setValue(null);
        dateToFilter.setValue(null);
        amountMinField.clear();
        amountMaxField.clear();
        applyFilters();
    }
    
    /**
     * Perform sorting using selected algorithm (Quick Sort for bookings).
     */
    private void performSort() {
        if (filteredList.isEmpty()) {
            showAlert(Alert.AlertType.WARNING, "No Data", "No bookings to sort.");
            return;
        }
        
        String algorithm = sortAlgorithmCombo != null ? sortAlgorithmCombo.getValue() : "Quick Sort";
        String sortField = sortFieldCombo != null ? sortFieldCombo.getValue() : "Date (Check-in)";
        
        // Create comparator based on sort field
        Comparator<Booking> comparator = getBookingComparator(sortField);
        
        // Convert to ArrayList for sorting
        List<Booking> listToSort = new ArrayList<>(filteredList);
        
        // Perform sort based on selected algorithm
        SortAlgorithms.SortResult<Booking> result;
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
                result = SortAlgorithms.quickSort(listToSort, comparator);
        }
        
        // Update filtered list with sorted data
        filteredList.clear();
        filteredList.addAll(result.getSortedData());
        bookingsTable.setItems(filteredList);
        
        // Update metrics label
        if (sortMetricsLabel != null) {
            sortMetricsLabel.setText(String.format("%s: %d ms, %d comparisons", 
                result.getAlgorithmName(), result.getExecutionTime(), result.getComparisons()));
            sortMetricsLabel.setTooltip(new Tooltip("Algorithm performance metrics"));
        }
        
        showAlert(Alert.AlertType.INFORMATION, "Sort Complete", 
            String.format("Sorted %d bookings using %s\nTime: %d ms\nComparisons: %d",
                listToSort.size(), result.getAlgorithmName(), 
                result.getExecutionTime(), result.getComparisons()));
    }
    
    /**
     * Get comparator for booking based on sort field.
     */
    private Comparator<Booking> getBookingComparator(String sortField) {
        switch (sortField) {
            case "Date (Check-in)":
                return Comparator.comparing(b -> LocalDate.parse(b.getCheckInDate()));
            case "Amount":
                return Comparator.comparing(b -> {
                    try {
                        return Double.parseDouble(b.getTotalAmount().replace("₱", "").replace(",", ""));
                    } catch (NumberFormatException e) {
                        return 0.0;
                    }
                });
            case "Guest Name":
                return Comparator.comparing(Booking::getGuestName, String.CASE_INSENSITIVE_ORDER);
            case "Status":
                return Comparator.comparing(Booking::getStatus, String.CASE_INSENSITIVE_ORDER);
            default:
                return Comparator.comparing(b -> LocalDate.parse(b.getCheckInDate()));
        }
    }

    /**
     * Confirm the selected booking.
     */
    private void confirmSelectedBooking() {
        Booking selected = bookingsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a booking to confirm.");
            return;
        }

        if (!selected.getStatus().equals("pending")) {
            showAlert(Alert.AlertType.WARNING, "Invalid Status", "Only pending bookings can be confirmed.");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirm Booking");
        confirm.setHeaderText(null);
        confirm.setContentText("Are you sure you want to confirm this booking?");
        
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            String query = "UPDATE reservations SET status = 'confirmed', confirmed_at = NOW() WHERE id = ?";
            
            try (Connection conn = DatabaseConnection.getConnection();
                 PreparedStatement stmt = conn.prepareStatement(query)) {
                
                stmt.setInt(1, selected.getId());
                int rows = stmt.executeUpdate();
                
                if (rows > 0) {
                    showAlert(Alert.AlertType.INFORMATION, "Success", "Booking confirmed successfully.");
                    loadBookings();
                }
            } catch (SQLException e) {
                showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to confirm booking: " + e.getMessage());
                e.printStackTrace();
            }
        }
    }

    /**
     * Cancel the selected booking.
     */
    private void cancelSelectedBooking() {
        Booking selected = bookingsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a booking to cancel.");
            return;
        }

        if (selected.getStatus().equals("cancelled") || selected.getStatus().equals("completed")) {
            showAlert(Alert.AlertType.WARNING, "Invalid Status", "This booking cannot be cancelled.");
            return;
        }

        TextInputDialog dialog = new TextInputDialog();
        dialog.setTitle("Cancel Booking");
        dialog.setHeaderText(null);
        dialog.setContentText("Please enter cancellation reason:");

        String reason = dialog.showAndWait().orElse("");
        if (reason.isEmpty()) {
            return;
        }

        String query = "UPDATE reservations SET status = 'cancelled', cancelled_at = NOW(), cancellation_reason = ? WHERE id = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setString(1, reason);
            stmt.setInt(2, selected.getId());
            int rows = stmt.executeUpdate();
            
            if (rows > 0) {
                // Release room inventory
                releaseRoomInventory(selected.getId());
                
                showAlert(Alert.AlertType.INFORMATION, "Success", "Booking cancelled successfully.");
                loadBookings();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to cancel booking: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Release room inventory when booking is cancelled.
     */
    private void releaseRoomInventory(int reservationId) throws SQLException {
        String query = "UPDATE rooms SET available_quantity = available_quantity + 1 " +
                      "WHERE id = (SELECT room_id FROM reservations WHERE id = ?)";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setInt(1, reservationId);
            stmt.executeUpdate();
        }
    }

    /**
     * View details of the selected booking.
     */
    private void viewBookingDetails() {
        Booking selected = bookingsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a booking to view details.");
            return;
        }

        BookingDetailsDialog dialog = new BookingDetailsDialog(selected.getId(), v -> {
            // Reload bookings when status changes
            loadBookings();
        });
        dialog.showAndWait();
    }

    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

