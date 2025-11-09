package com.belmonthotel.admin.utils;

import com.belmonthotel.admin.models.HistoryNode;

import java.util.ArrayList;
import java.util.List;

/**
 * Custom doubly-linked list implementation for booking modification history.
 * Demonstrates LinkedList data structure with forward/backward navigation.
 */
public class BookingHistoryLinkedList {
    
    private HistoryNode head; // First node
    private HistoryNode tail; // Last node
    private int size;
    
    public BookingHistoryLinkedList() {
        this.head = null;
        this.tail = null;
        this.size = 0;
    }
    
    /**
     * Add a new history node to the end of the list.
     */
    public void add(String actionType, String oldValue, String newValue, String adminUser) {
        HistoryNode newNode = new HistoryNode(actionType, oldValue, newValue, adminUser);
        
        if (head == null) {
            // First node
            head = newNode;
            tail = newNode;
        } else {
            // Add to end
            tail.setNext(newNode);
            newNode.setPrevious(tail);
            tail = newNode;
        }
        size++;
    }
    
    /**
     * Get the first node (head).
     */
    public HistoryNode getHead() {
        return head;
    }
    
    /**
     * Get the last node (tail).
     */
    public HistoryNode getTail() {
        return tail;
    }
    
    /**
     * Get the size of the list.
     */
    public int size() {
        return size;
    }
    
    /**
     * Check if the list is empty.
     */
    public boolean isEmpty() {
        return size == 0;
    }
    
    /**
     * Get all nodes as a list (for display purposes).
     */
    public List<HistoryNode> getAllNodes() {
        List<HistoryNode> nodes = new ArrayList<>();
        HistoryNode current = head;
        while (current != null) {
            nodes.add(current);
            current = current.getNext();
        }
        return nodes;
    }
    
    /**
     * Clear all nodes from the list.
     */
    public void clear() {
        head = null;
        tail = null;
        size = 0;
    }
    
    /**
     * Get a string representation of the list structure.
     */
    @Override
    public String toString() {
        if (isEmpty()) {
            return "Empty LinkedList";
        }
        
        StringBuilder sb = new StringBuilder();
        sb.append("LinkedList (size: ").append(size).append("):\n");
        HistoryNode current = head;
        int index = 0;
        while (current != null) {
            sb.append("  [").append(index++).append("] ").append(current.toString());
            if (current.getNext() != null) {
                sb.append(" -> ");
            }
            current = current.getNext();
        }
        return sb.toString();
    }
}

