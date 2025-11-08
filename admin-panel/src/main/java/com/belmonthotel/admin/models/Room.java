package com.belmonthotel.admin.models;

import javafx.beans.property.*;

/**
 * Model class for Room data.
 */
public class Room {
    private final IntegerProperty id = new SimpleIntegerProperty();
    private final IntegerProperty hotelId = new SimpleIntegerProperty();
    private final StringProperty hotelName = new SimpleStringProperty();
    private final StringProperty roomType = new SimpleStringProperty();
    private final StringProperty pricePerNight = new SimpleStringProperty();
    private final IntegerProperty quantity = new SimpleIntegerProperty();
    private final IntegerProperty availableQuantity = new SimpleIntegerProperty();
    private final IntegerProperty maxGuests = new SimpleIntegerProperty();
    private final StringProperty status = new SimpleStringProperty();

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

    public int getHotelId() {
        return hotelId.get();
    }

    public void setHotelId(int hotelId) {
        this.hotelId.set(hotelId);
    }

    public IntegerProperty hotelIdProperty() {
        return hotelId;
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

    public String getPricePerNight() {
        return pricePerNight.get();
    }

    public void setPricePerNight(String pricePerNight) {
        this.pricePerNight.set(pricePerNight);
    }

    public StringProperty pricePerNightProperty() {
        return pricePerNight;
    }

    public int getQuantity() {
        return quantity.get();
    }

    public void setQuantity(int quantity) {
        this.quantity.set(quantity);
    }

    public IntegerProperty quantityProperty() {
        return quantity;
    }

    public int getAvailableQuantity() {
        return availableQuantity.get();
    }

    public void setAvailableQuantity(int availableQuantity) {
        this.availableQuantity.set(availableQuantity);
    }

    public IntegerProperty availableQuantity() {
        return availableQuantity;
    }

    public int getMaxGuests() {
        return maxGuests.get();
    }

    public void setMaxGuests(int maxGuests) {
        this.maxGuests.set(maxGuests);
    }

    public IntegerProperty maxGuestsProperty() {
        return maxGuests;
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
}

