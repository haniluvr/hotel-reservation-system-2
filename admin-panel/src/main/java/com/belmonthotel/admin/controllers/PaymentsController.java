package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.Payment;
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
 * Controller for the payments tracking module.
 */
public class PaymentsController implements Initializable {
    @FXML
    private TableView<Payment> paymentsTable;

    @FXML
    private TableColumn<Payment, Integer> idColumn;

    @FXML
    private TableColumn<Payment, String> reservationNumberColumn;

    @FXML
    private TableColumn<Payment, String> amountColumn;

    @FXML
    private TableColumn<Payment, String> paymentMethodColumn;

    @FXML
    private TableColumn<Payment, String> statusColumn;

    @FXML
    private TableColumn<Payment, String> paidAtColumn;

    @FXML
    private TableColumn<Payment, String> xenditInvoiceIdColumn;

    @FXML
    private TextField searchField;

    @FXML
    private ComboBox<String> statusFilter;

    @FXML
    private Button refreshBtn;

    @FXML
    private Button viewDetailsBtn;

    private ObservableList<Payment> paymentsList;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        paymentsList = FXCollections.observableArrayList();

        // Initialize table columns
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        reservationNumberColumn.setCellValueFactory(new PropertyValueFactory<>("reservationNumber"));
        amountColumn.setCellValueFactory(new PropertyValueFactory<>("amount"));
        paymentMethodColumn.setCellValueFactory(new PropertyValueFactory<>("paymentMethod"));
        statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));
        paidAtColumn.setCellValueFactory(new PropertyValueFactory<>("paidAt"));
        xenditInvoiceIdColumn.setCellValueFactory(new PropertyValueFactory<>("xenditInvoiceId"));

        // Set status filter options
        statusFilter.getItems().addAll("All", "Pending", "Paid", "Failed", "Expired", "Cancelled");
        statusFilter.setValue("All");

        // Set button actions
        refreshBtn.setOnAction(e -> loadPayments());
        viewDetailsBtn.setOnAction(e -> viewPaymentDetails());

        // Set filter actions
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        statusFilter.setOnAction(e -> applyFilters());

        // Load initial data
        loadPayments();
    }

    /**
     * Load payments from database.
     */
    private void loadPayments() {
        paymentsList.clear();
        
        String query = "SELECT p.id, p.amount, p.payment_method, p.status, p.paid_at, " +
                      "p.xendit_invoice_id, r.reservation_number " +
                      "FROM payments p " +
                      "JOIN reservations r ON p.reservation_id = r.id " +
                      "ORDER BY p.created_at DESC";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                Payment payment = new Payment();
                payment.setId(rs.getInt("id"));
                payment.setReservationNumber(rs.getString("reservation_number"));
                payment.setAmount(String.format("₱%.2f", rs.getDouble("amount")));
                payment.setPaymentMethod(rs.getString("payment_method"));
                payment.setStatus(rs.getString("status"));
                payment.setXenditInvoiceId(rs.getString("xendit_invoice_id"));
                
                if (rs.getTimestamp("paid_at") != null) {
                    payment.setPaidAt(rs.getTimestamp("paid_at").toLocalDateTime()
                        .format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm")));
                } else {
                    payment.setPaidAt("N/A");
                }
                
                paymentsList.add(payment);
            }

            applyFilters();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load payments: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Apply filters to the payments list.
     */
    private void applyFilters() {
        ObservableList<Payment> filtered = FXCollections.observableArrayList();
        String searchText = searchField.getText().toLowerCase();
        String status = statusFilter.getValue();

        for (Payment payment : paymentsList) {
            // Status filter
            if (status != null && !status.equals("All")) {
                if (!payment.getStatus().equalsIgnoreCase(status)) {
                    continue;
                }
            }

            // Search filter
            if (!searchText.isEmpty()) {
                if (!payment.getReservationNumber().toLowerCase().contains(searchText) &&
                    !payment.getXenditInvoiceId().toLowerCase().contains(searchText)) {
                    continue;
                }
            }

            filtered.add(payment);
        }

        paymentsTable.setItems(filtered);
    }

    /**
     * View details of the selected payment.
     */
    private void viewPaymentDetails() {
        Payment selected = paymentsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a payment to view details.");
            return;
        }

        String query = "SELECT p.*, r.reservation_number, r.check_in_date, r.check_out_date, " +
                      "u.name as guest_name, u.email as guest_email " +
                      "FROM payments p " +
                      "JOIN reservations r ON p.reservation_id = r.id " +
                      "JOIN users u ON r.user_id = u.id " +
                      "WHERE p.id = ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setInt(1, selected.getId());
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                StringBuilder details = new StringBuilder();
                details.append("Payment Details:\n\n");
                details.append("Payment ID: ").append(rs.getInt("id")).append("\n");
                details.append("Reservation Number: ").append(rs.getString("reservation_number")).append("\n");
                details.append("Amount: ").append(String.format("₱%.2f", rs.getDouble("amount"))).append("\n");
                details.append("Payment Method: ").append(rs.getString("payment_method")).append("\n");
                details.append("Status: ").append(rs.getString("status")).append("\n");
                details.append("Xendit Invoice ID: ").append(rs.getString("xendit_invoice_id")).append("\n\n");
                
                if (rs.getTimestamp("paid_at") != null) {
                    details.append("Paid At: ").append(rs.getTimestamp("paid_at")).append("\n");
                }
                
                if (rs.getTimestamp("expires_at") != null) {
                    details.append("Expires At: ").append(rs.getTimestamp("expires_at")).append("\n");
                }
                
                if (rs.getString("failure_reason") != null) {
                    details.append("\nFailure Reason: ").append(rs.getString("failure_reason"));
                }

                details.append("\n\nGuest Information:\n");
                details.append("Name: ").append(rs.getString("guest_name")).append("\n");
                details.append("Email: ").append(rs.getString("guest_email")).append("\n");
                details.append("Check-in: ").append(rs.getDate("check_in_date")).append("\n");
                details.append("Check-out: ").append(rs.getDate("check_out_date"));

                Alert alert = new Alert(Alert.AlertType.INFORMATION);
                alert.setTitle("Payment Details");
                alert.setHeaderText(null);
                alert.setContentText(details.toString());
                alert.showAndWait();
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load payment details: " + e.getMessage());
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

