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
    private final IntegerProperty maxAdults = new SimpleIntegerProperty();
    private final IntegerProperty maxChildren = new SimpleIntegerProperty();
    private final StringProperty description = new SimpleStringProperty();
    private final StringProperty amenities = new SimpleStringProperty();
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

    public int getMaxAdults() {
        return maxAdults.get();
    }

    public void setMaxAdults(int maxAdults) {
        this.maxAdults.set(maxAdults);
    }

    public IntegerProperty maxAdultsProperty() {
        return maxAdults;
    }

    public int getMaxChildren() {
        return maxChildren.get();
    }

    public void setMaxChildren(int maxChildren) {
        this.maxChildren.set(maxChildren);
    }

    public IntegerProperty maxChildrenProperty() {
        return maxChildren;
    }

    public String getDescription() {
        return description.get();
    }

    public void setDescription(String description) {
        this.description.set(description);
    }

    public StringProperty descriptionProperty() {
        return description;
    }

    public String getAmenities() {
        return amenities.get();
    }

    public void setAmenities(String amenities) {
        this.amenities.set(amenities);
    }

    public StringProperty amenitiesProperty() {
        return amenities;
    }
}

