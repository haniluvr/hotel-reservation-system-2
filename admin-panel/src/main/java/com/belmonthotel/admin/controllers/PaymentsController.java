package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.models.Payment;
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
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;
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

    @FXML
    private Button markPaidBtn;

    @FXML
    private ComboBox<String> sortAlgorithmCombo;

    @FXML
    private ComboBox<String> sortFieldCombo;

    @FXML
    private Button sortBtn;

    @FXML
    private Label sortMetricsLabel;

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

        // Initialize sort algorithm options (Heap Sort for payments)
        if (sortAlgorithmCombo != null) {
            sortAlgorithmCombo.getItems().addAll("Quick Sort", "Merge Sort", "Heap Sort");
            sortAlgorithmCombo.setValue("Heap Sort");
        }
        
        // Initialize sort field options
        if (sortFieldCombo != null) {
            sortFieldCombo.getItems().addAll("Amount", "Date", "Status", "Payment Method");
            sortFieldCombo.setValue("Amount");
        }

        // Set button actions
        refreshBtn.setOnAction(e -> loadPayments());
        viewDetailsBtn.setOnAction(e -> viewPaymentDetails());
        if (markPaidBtn != null) {
            markPaidBtn.setOnAction(e -> markPaymentAsPaid());
        }
        if (sortBtn != null) {
            sortBtn.setOnAction(e -> performSort());
        }

        // Set filter actions
        searchField.textProperty().addListener((obs, oldVal, newVal) -> applyFilters());
        statusFilter.setOnAction(e -> applyFilters());

        // Update button state based on selected payment
        paymentsTable.getSelectionModel().selectedItemProperty().addListener((obs, oldVal, newVal) -> {
            updateMarkPaidButtonState();
        });

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
     * Perform sorting using selected algorithm (Heap Sort for payments).
     */
    private void performSort() {
        ObservableList<Payment> currentList = paymentsTable.getItems();
        if (currentList.isEmpty()) {
            showAlert(Alert.AlertType.WARNING, "No Data", "No payments to sort.");
            return;
        }
        
        String algorithm = sortAlgorithmCombo != null ? sortAlgorithmCombo.getValue() : "Heap Sort";
        String sortField = sortFieldCombo != null ? sortFieldCombo.getValue() : "Amount";
        
        // Create comparator based on sort field
        Comparator<Payment> comparator = getPaymentComparator(sortField);
        
        // Convert to ArrayList for sorting
        List<Payment> listToSort = new ArrayList<>(currentList);
        
        // Perform sort based on selected algorithm
        SortAlgorithms.SortResult<Payment> result;
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
                result = SortAlgorithms.heapSort(listToSort, comparator);
        }
        
        // Update table with sorted data
        ObservableList<Payment> sortedList = FXCollections.observableArrayList(result.getSortedData());
        paymentsTable.setItems(sortedList);
        
        // Update metrics label
        if (sortMetricsLabel != null) {
            sortMetricsLabel.setText(String.format("%s: %d ms, %d comparisons", 
                result.getAlgorithmName(), result.getExecutionTime(), result.getComparisons()));
            sortMetricsLabel.setTooltip(new Tooltip("Algorithm performance metrics"));
        }
        
        showAlert(Alert.AlertType.INFORMATION, "Sort Complete", 
            String.format("Sorted %d payments using %s\nTime: %d ms\nComparisons: %d",
                listToSort.size(), result.getAlgorithmName(), 
                result.getExecutionTime(), result.getComparisons()));
    }
    
    /**
     * Get comparator for payment based on sort field.
     */
    private Comparator<Payment> getPaymentComparator(String sortField) {
        switch (sortField) {
            case "Amount":
                return Comparator.comparing(p -> {
                    try {
                        return Double.parseDouble(p.getAmount().replace("₱", "").replace(",", ""));
                    } catch (NumberFormatException e) {
                        return 0.0;
                    }
                });
            case "Date":
                return Comparator.comparing(p -> {
                    if (p.getPaidAt() == null || p.getPaidAt().equals("N/A")) {
                        return "";
                    }
                    return p.getPaidAt();
                });
            case "Status":
                return Comparator.comparing(Payment::getStatus, String.CASE_INSENSITIVE_ORDER);
            case "Payment Method":
                return Comparator.comparing(Payment::getPaymentMethod, String.CASE_INSENSITIVE_ORDER);
            default:
                return Comparator.comparing(p -> {
                    try {
                        return Double.parseDouble(p.getAmount().replace("₱", "").replace(",", ""));
                    } catch (NumberFormatException e) {
                        return 0.0;
                    }
                });
        }
    }

    /**
     * Update the state of the "Mark as Paid" button based on selected payment.
     */
    private void updateMarkPaidButtonState() {
        if (markPaidBtn == null) return;
        
        Payment selected = paymentsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            markPaidBtn.setDisable(true);
            markPaidBtn.setTooltip(null);
            return;
        }

        // Only allow marking cash payments as paid
        // Xendit payments are automatically confirmed when paid
        boolean isCash = "cash".equalsIgnoreCase(selected.getPaymentMethod());
        boolean isPending = "pending".equalsIgnoreCase(selected.getStatus());
        
        if (isCash && isPending) {
            markPaidBtn.setDisable(false);
            markPaidBtn.setTooltip(new Tooltip("Mark this cash payment as paid"));
        } else if (!isCash) {
            markPaidBtn.setDisable(true);
            markPaidBtn.setTooltip(new Tooltip("Xendit payments are automatically confirmed when paid"));
        } else {
            markPaidBtn.setDisable(true);
            markPaidBtn.setTooltip(new Tooltip("This payment is already " + selected.getStatus()));
        }
    }

    /**
     * Mark selected payment as paid (only for cash payments).
     */
    private void markPaymentAsPaid() {
        Payment selected = paymentsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "No Selection", "Please select a payment to mark as paid.");
            return;
        }

        // Only allow marking cash payments as paid
        if (!"cash".equalsIgnoreCase(selected.getPaymentMethod())) {
            showAlert(Alert.AlertType.WARNING, "Invalid Action", 
                "Only cash payments can be manually marked as paid.\n" +
                "Xendit payments are automatically confirmed when payment is completed.");
            return;
        }

        // Check if already paid
        if ("paid".equalsIgnoreCase(selected.getStatus())) {
            showAlert(Alert.AlertType.INFORMATION, "Already Paid", "This payment is already marked as paid.");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Mark Cash Payment as Paid");
        confirm.setHeaderText(null);
        confirm.setContentText(String.format(
            "Mark cash payment as paid?\n\n" +
            "Payment ID: %d\n" +
            "Reservation: %s\n" +
            "Amount: %s\n" +
            "Payment Method: %s\n" +
            "Current Status: %s\n\n" +
            "This will also confirm the reservation.",
            selected.getId(),
            selected.getReservationNumber(),
            selected.getAmount(),
            selected.getPaymentMethod(),
            selected.getStatus()
        ));

        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            try (Connection conn = DatabaseConnection.getConnection()) {
                // Start transaction
                conn.setAutoCommit(false);
                
                try {
                    // Update payment status
                    PreparedStatement paymentStmt = conn.prepareStatement(
                        "UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                    paymentStmt.setInt(1, selected.getId());
                    int paymentRows = paymentStmt.executeUpdate();
                    paymentStmt.close();
                    
                    if (paymentRows > 0) {
                        // Update reservation status to confirmed
                        PreparedStatement reservationStmt = conn.prepareStatement(
                            "UPDATE reservations SET status = 'confirmed', confirmed_at = NOW() " +
                            "WHERE id = (SELECT reservation_id FROM payments WHERE id = ?) " +
                            "AND status = 'pending'");
                        reservationStmt.setInt(1, selected.getId());
                        reservationStmt.executeUpdate();
                        reservationStmt.close();
                        
                        // Commit transaction
                        conn.commit();
                        
                        showAlert(Alert.AlertType.INFORMATION, "Success", 
                            "Payment marked as paid and reservation confirmed successfully.");
                        loadPayments(); // Reload to refresh the table
                    } else {
                        conn.rollback();
                        showAlert(Alert.AlertType.ERROR, "Error", 
                            "Failed to update payment. Please try again.");
                    }
                } catch (SQLException e) {
                    conn.rollback();
                    throw e;
                } finally {
                    conn.setAutoCommit(true);
                }
            } catch (SQLException e) {
                showAlert(Alert.AlertType.ERROR, "Database Error", 
                    "Failed to update payment: " + e.getMessage());
                e.printStackTrace();
            }
        }
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

