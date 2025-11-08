package com.belmonthotel.admin.models;

import javafx.beans.property.SimpleIntegerProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.beans.property.IntegerProperty;
import javafx.beans.property.StringProperty;

/**
 * Model class for Booking/Reservation data.
 */
public class Booking {
    private final IntegerProperty id = new SimpleIntegerProperty();
    private final StringProperty reservationNumber = new SimpleStringProperty();
    private final StringProperty guestName = new SimpleStringProperty();
    private final StringProperty guestEmail = new SimpleStringProperty();
    private final StringProperty hotelName = new SimpleStringProperty();
    private final StringProperty roomType = new SimpleStringProperty();
    private final StringProperty checkInDate = new SimpleStringProperty();
    private final StringProperty checkOutDate = new SimpleStringProperty();
    private final StringProperty status = new SimpleStringProperty();
    private final StringProperty totalAmount = new SimpleStringProperty();
    private final IntegerProperty adults = new SimpleIntegerProperty();
    private final IntegerProperty children = new SimpleIntegerProperty();

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

    public String getGuestName() {
        return guestName.get();
    }

    public void setGuestName(String guestName) {
        this.guestName.set(guestName);
    }

    public StringProperty guestNameProperty() {
        return guestName;
    }

    public String getGuestEmail() {
        return guestEmail.get();
    }

    public void setGuestEmail(String guestEmail) {
        this.guestEmail.set(guestEmail);
    }

    public StringProperty guestEmailProperty() {
        return guestEmail;
    }

    public String getHotelName() {
        return hotelName.get();
    }

    public void setHotelName(String hotelName) {
        this.hotelName.set(hotelName);
    }

    public StringProperty hotelNameProperty() {
        return hotelName;
    }

    public String getRoomType() {
        return roomType.get();
    }

    public void setRoomType(String roomType) {
        this.roomType.set(roomType);
    }

    public StringProperty roomTypeProperty() {
        return roomType;
    }

    public String getCheckInDate() {
        return checkInDate.get();
    }

    public void setCheckInDate(String checkInDate) {
        this.checkInDate.set(checkInDate);
    }

    public StringProperty checkInDateProperty() {
        return checkInDate;
    }

    public String getCheckOutDate() {
        return checkOutDate.get();
    }

    public void setCheckOutDate(String checkOutDate) {
        this.checkOutDate.set(checkOutDate);
    }

    public StringProperty checkOutDateProperty() {
        return checkOutDate;
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

    public String getTotalAmount() {
        return totalAmount.get();
    }

    public void setTotalAmount(String totalAmount) {
        this.totalAmount.set(totalAmount);
    }

    public StringProperty totalAmountProperty() {
        return totalAmount;
    }

    public int getAdults() {
        return adults.get();
    }

    public void setAdults(int adults) {
        this.adults.set(adults);
    }

    public IntegerProperty adultsProperty() {
        return adults;
    }

    public int getChildren() {
        return children.get();
    }

    public void setChildren(int children) {
        this.children.set(children);
    }

    public IntegerProperty childrenProperty() {
        return children;
    }
}

