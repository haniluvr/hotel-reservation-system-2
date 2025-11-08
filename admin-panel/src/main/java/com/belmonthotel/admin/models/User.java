package com.belmonthotel.admin.models;

import javafx.beans.property.*;

/**
 * Model class for User data.
 */
public class User {
    private final IntegerProperty id = new SimpleIntegerProperty();
    private final StringProperty name = new SimpleStringProperty();
    private final StringProperty email = new SimpleStringProperty();
    private final StringProperty role = new SimpleStringProperty();
    private final StringProperty status = new SimpleStringProperty();
    private final StringProperty createdAt = new SimpleStringProperty();

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

    public String getEmail() {
        return email.get();
    }

    public void setEmail(String email) {
        this.email.set(email);
    }

    public StringProperty emailProperty() {
        return email;
    }

    public String getRole() {
        return role.get();
    }

    public void setRole(String role) {
        this.role.set(role);
    }

    public StringProperty roleProperty() {
        return role;
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

    public String getCreatedAt() {
        return createdAt.get();
    }

    public void setCreatedAt(String createdAt) {
        this.createdAt.set(createdAt);
    }

    public StringProperty createdAtProperty() {
        return createdAt;
    }
}

