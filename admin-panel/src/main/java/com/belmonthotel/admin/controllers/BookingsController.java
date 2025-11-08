package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.Booking;
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
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
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
    private ComboBox<String> statusFilter;

    @FXML
    private DatePicker dateFromFilter;

    @FXML
    private DatePicker dateToFilter;

    @FXML
    private Button refreshBtn;

    @FXML
    private Button confirmBtn;

    @FXML
    private Button cancelBtn;

    @FXML
    private Button viewDetailsBtn;

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

        // Set button actions
        refreshBtn.setOnAction(e -> loadBookings());
        confirmBtn.setOnAction(e -> confirmSelectedBooking());
        cancelBtn.setOnAction(e -> cancelSelectedBooking());
        viewDetailsBtn.setOnAction(e -> viewBookingDetails());

        // Set filter actions
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        statusFilter.setOnAction(e -> applyFilters());
        dateFromFilter.valueProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        dateToFilter.valueProperty().addListener((obs, oldVal, newVal) -> applyFilters());

        // Load initial data
        loadBookings();
    }

    /**
     * Load bookings from database.
     */
    private void loadBookings() {
        bookingsList.clear();
        
        String query = "SELECT r.id, r.reservation_number, r.check_in_date, r.check_out_date, " +
                      "r.status, r.total_amount, r.adults, r.children, " +
                      "u.name as guest_name, u.email as guest_email, " +
                      "h.name as hotel_name, rm.room_type " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "JOIN hotels h ON rm.hotel_id = h.id " +
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
                booking.setHotelName(rs.getString("hotel_name"));
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
        String status = statusFilter.getValue();
        LocalDate fromDate = dateFromFilter.getValue();
        LocalDate toDate = dateToFilter.getValue();

        for (Booking booking : bookingsList) {
            // Search filter
            if (!searchText.isEmpty()) {
                if (!booking.getReservationNumber().toLowerCase().contains(searchText) &&
                    !booking.getGuestName().toLowerCase().contains(searchText) &&
                    !booking.getHotelName().toLowerCase().contains(searchText)) {
                    continue;
                }
            }

            // Status filter
            if (status != null && !status.equals("All")) {
                if (!booking.getStatus().equalsIgnoreCase(status)) {
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

            filteredList.add(booking);
        }

        bookingsTable.setItems(filteredList);
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

        // Load full booking details from database
        String query = "SELECT r.*, u.name as guest_name, u.email as guest_email, " +
                      "h.name as hotel_name, h.address as hotel_address, " +
                      "rm.room_type, rm.description as room_description, rm.price_per_night " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "JOIN hotels h ON rm.hotel_id = h.id " +
                      "WHERE r.id = ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setInt(1, selected.getId());
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                StringBuilder details = new StringBuilder();
                details.append("Reservation Number: ").append(rs.getString("reservation_number")).append("\n\n");
                details.append("Guest Information:\n");
                details.append("  Name: ").append(rs.getString("guest_name")).append("\n");
                details.append("  Email: ").append(rs.getString("guest_email")).append("\n\n");
                details.append("Hotel: ").append(rs.getString("hotel_name")).append("\n");
                details.append("Address: ").append(rs.getString("hotel_address")).append("\n\n");
                details.append("Room: ").append(rs.getString("room_type")).append("\n");
                details.append("Check-in: ").append(rs.getDate("check_in_date")).append("\n");
                details.append("Check-out: ").append(rs.getDate("check_out_date")).append("\n");
                details.append("Guests: ").append(rs.getInt("adults")).append(" adults, ")
                       .append(rs.getInt("children")).append(" children\n\n");
                details.append("Total Amount: ").append(String.format("₱%.2f", rs.getDouble("total_amount"))).append("\n");
                details.append("Status: ").append(rs.getString("status")).append("\n");
                
                if (rs.getString("special_requests") != null) {
                    details.append("\nSpecial Requests: ").append(rs.getString("special_requests"));
                }

                Alert alert = new Alert(Alert.AlertType.INFORMATION);
                alert.setTitle("Booking Details");
                alert.setHeaderText(null);
                alert.setContentText(details.toString());
                alert.showAndWait();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load booking details: " + e.getMessage());
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

