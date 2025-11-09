package com.belmonthotel.admin.utils;

import java.time.LocalDate;
import java.util.Stack;

/**
 * Stack data structure for managing booking modification history (Undo/Redo functionality).
 * Uses Java's built-in Stack class to store booking state snapshots.
 */
public class BookingModificationStack {
    
    /**
     * Represents a snapshot of booking state before modification.
     */
    public static class BookingState {
        private final int bookingId;
        private final String status;
        private final LocalDate checkInDate;
        private final LocalDate checkOutDate;
        private final double totalAmount;
        private final String actionType; // "confirm", "cancel", "modify_dates", "complete", "no_show"
        private final long timestamp;
        
        public BookingState(int bookingId, String status, LocalDate checkInDate, 
                           LocalDate checkOutDate, double totalAmount, String actionType) {
            this.bookingId = bookingId;
            this.status = status;
            this.checkInDate = checkInDate;
            this.checkOutDate = checkOutDate;
            this.totalAmount = totalAmount;
            this.actionType = actionType;
            this.timestamp = System.currentTimeMillis();
        }
        
        // Getters
        public int getBookingId() { return bookingId; }
        public String getStatus() { return status; }
        public LocalDate getCheckInDate() { return checkInDate; }
        public LocalDate getCheckOutDate() { return checkOutDate; }
        public double getTotalAmount() { return totalAmount; }
        public String getActionType() { return actionType; }
        public long getTimestamp() { return timestamp; }
        
        @Override
        public String toString() {
            return String.format("BookingState{id=%d, action=%s, status=%s, amount=%.2f}", 
                bookingId, actionType, status, totalAmount);
        }
    }
    
    private final Stack<BookingState> undoStack;
    private final Stack<BookingState> redoStack;
    
    public BookingModificationStack() {
        this.undoStack = new Stack<>();
        this.redoStack = new Stack<>();
    }
    
    /**
     * Push a booking state onto the undo stack before making a modification.
     */
    public void pushState(BookingState state) {
        undoStack.push(state);
        // Clear redo stack when new action is performed
        redoStack.clear();
    }
    
    /**
     * Pop the most recent state from undo stack (for undo operation).
     */
    public BookingState popUndo() {
        if (undoStack.isEmpty()) {
            return null;
        }
        BookingState state = undoStack.pop();
        redoStack.push(state);
        return state;
    }
    
    /**
     * Pop the most recent state from redo stack (for redo operation).
     */
    public BookingState popRedo() {
        if (redoStack.isEmpty()) {
            return null;
        }
        BookingState state = redoStack.pop();
        undoStack.push(state);
        return state;
    }
    
    /**
     * Peek at the top of undo stack without removing it.
     */
    public BookingState peekUndo() {
        return undoStack.isEmpty() ? null : undoStack.peek();
    }
    
    /**
     * Peek at the top of redo stack without removing it.
     */
    public BookingState peekRedo() {
        return redoStack.isEmpty() ? null : redoStack.peek();
    }
    
    /**
     * Get the size of the undo stack.
     */
    public int getUndoStackSize() {
        return undoStack.size();
    }
    
    /**
     * Get the size of the redo stack.
     */
    public int getRedoStackSize() {
        return redoStack.size();
    }
    
    /**
     * Check if undo stack is empty.
     */
    public boolean isUndoEmpty() {
        return undoStack.isEmpty();
    }
    
    /**
     * Check if redo stack is empty.
     */
    public boolean isRedoEmpty() {
        return redoStack.isEmpty();
    }
    
    /**
     * Clear both stacks.
     */
    public void clear() {
        undoStack.clear();
        redoStack.clear();
    }
}

