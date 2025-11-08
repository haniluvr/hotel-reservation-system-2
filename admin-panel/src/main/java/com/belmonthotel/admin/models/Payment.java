package com.belmonthotel.admin.models;

import javafx.beans.property.*;

/**
 * Model class for Payment data.
 */
public class Payment {
    private final IntegerProperty id = new SimpleIntegerProperty();
    private final StringProperty reservationNumber = new SimpleStringProperty();
    private final StringProperty amount = new SimpleStringProperty();
    private final StringProperty paymentMethod = new SimpleStringProperty();
    private final StringProperty status = new SimpleStringProperty();
    private final StringProperty paidAt = new SimpleStringProperty();
    private final StringProperty xenditInvoiceId = new SimpleStringProperty();

    // Getters and Setters
    public int getId() {
        return id.get();
    }

    public void setId(int id) {
        this.id.set(id);
    }

    public IntegerProperty idProperty() {
        return id;
    }

    public String getReservationNumber() {
        return reservationNumber.get();
    }

    public void setReservationNumber(String reservationNumber) {
        this.reservationNumber.set(reservationNumber);
    }

    public StringProperty reservationNumberProperty() {
        return reservationNumber;
    }

    public String getAmount() {
        return amount.get();
    }

    public void setAmount(String amount) {
        this.amount.set(amount);
    }

    public StringProperty amountProperty() {
        return amount;
    }

    public String getPaymentMethod() {
        return paymentMethod.get();
    }

    public void setPaymentMethod(String paymentMethod) {
        this.paymentMethod.set(paymentMethod);
    }

    public StringProperty paymentMethodProperty() {
        return paymentMethod;
    }

    public String getStatus() {
        return status.get();
    }

    public void setStatus(String status) {
        this.status.set(status);
    }

    public StringProperty statusProperty() {
        return status;
    }

    public String getPaidAt() {
        return paidAt.get();
    }

    public void setPaidAt(String paidAt) {
        this.paidAt.set(paidAt);
    }

    public StringProperty paidAtProperty() {
        return paidAt;
    }

    public String getXenditInvoiceId() {
        return xenditInvoiceId.get();
    }

    public void setXenditInvoiceId(String xenditInvoiceId) {
        this.xenditInvoiceId.set(xenditInvoiceId);
    }

    public StringProperty xenditInvoiceIdProperty() {
        return xenditInvoiceId;
    }
}

