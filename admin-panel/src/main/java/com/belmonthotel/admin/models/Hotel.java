package com.belmonthotel.admin.models;

import javafx.beans.property.*;

/**
 * Model class for Hotel data.
 */
public class Hotel {
    private final IntegerProperty id = new SimpleIntegerProperty();
    private final StringProperty name = new SimpleStringProperty();
    private final StringProperty city = new SimpleStringProperty();
    private final StringProperty country = new SimpleStringProperty();
    private final IntegerProperty starRating = new SimpleIntegerProperty();
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

    public String getName() {
        return name.get();
    }

    public void setName(String name) {
        this.name.set(name);
    }

    public StringProperty nameProperty() {
        return name;
    }

    public String getCity() {
        return city.get();
    }

    public void setCity(String city) {
        this.city.set(city);
    }

    public StringProperty cityProperty() {
        return city;
    }

    public String getCountry() {
        return country.get();
    }

    public void setCountry(String country) {
        this.country.set(country);
    }

    public StringProperty countryProperty() {
        return country;
    }

    public int getStarRating() {
        return starRating.get();
    }

    public void setStarRating(int starRating) {
        this.starRating.set(starRating);
    }

    public IntegerProperty starRatingProperty() {
        return starRating;
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

