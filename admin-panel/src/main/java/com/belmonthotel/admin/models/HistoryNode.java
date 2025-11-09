package com.belmonthotel.admin.models;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

/**
 * Node for the booking modification history linked list.
 * Represents a single modification event in the history chain.
 */
public class HistoryNode {
    private String actionType; // "confirm", "cancel", "modify_dates", "complete", "no_show"
    private String oldValue;
    private String newValue;
    private LocalDateTime timestamp;
    private String adminUser;
    
    // Doubly-linked list pointers
    private HistoryNode previous;
    private HistoryNode next;
    
    public HistoryNode(String actionType, String oldValue, String newValue, String adminUser) {
        this.actionType = actionType;
        this.oldValue = oldValue;
        this.newValue = newValue;
        this.adminUser = adminUser;
        this.timestamp = LocalDateTime.now();
        this.previous = null;
        this.next = null;
    }
    
    // Getters
    public String getActionType() { return actionType; }
    public String getOldValue() { return oldValue; }
    public String getNewValue() { return newValue; }
    public LocalDateTime getTimestamp() { return timestamp; }
    public String getAdminUser() { return adminUser; }
    public HistoryNode getPrevious() { return previous; }
    public HistoryNode getNext() { return next; }
    
    // Setters
    public void setPrevious(HistoryNode previous) { this.previous = previous; }
    public void setNext(HistoryNode next) { this.next = next; }
    
    @Override
    public String toString() {
        return String.format("[%s] %s: %s → %s (by %s)", 
            timestamp.format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")),
            actionType, oldValue, newValue, adminUser);
    }
}

