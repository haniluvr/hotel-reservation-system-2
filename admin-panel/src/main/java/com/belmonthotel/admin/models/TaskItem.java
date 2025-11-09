package com.belmonthotel.admin.models;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

/**
 * Model class for task items in the priority queue.
 */
public class TaskItem implements Comparable<TaskItem> {
    
    public enum TaskPriority {
        URGENT(1),    // Check-ins today
        NORMAL(2),   // Reports, statistics
        LOW(3);       // Background sync
        
        private final int priority;
        
        TaskPriority(int priority) {
            this.priority = priority;
        }
        
        public int getPriority() {
            return priority;
        }
    }
    
    public enum TaskType {
        EMAIL_NOTIFICATION("Email Notification"),
        REPORT_GENERATION("Report Generation"),
        DATA_SYNC("Data Sync"),
        STATISTICS_UPDATE("Statistics Update"),
        CHECK_IN_REMINDER("Check-in Reminder");
        
        private final String displayName;
        
        TaskType(String displayName) {
            this.displayName = displayName;
        }
        
        public String getDisplayName() {
            return displayName;
        }
    }
    
    public enum TaskStatus {
        PENDING, PROCESSING, COMPLETED, FAILED
    }
    
    private final String id;
    private final TaskType type;
    private final TaskPriority priority;
    private TaskStatus status;
    private final LocalDateTime createdAt;
    private LocalDateTime processedAt;
    private String description;
    private String result;
    
    public TaskItem(TaskType type, TaskPriority priority, String description) {
        this.id = "TASK-" + System.currentTimeMillis() + "-" + (int)(Math.random() * 1000);
        this.type = type;
        this.priority = priority;
        this.status = TaskStatus.PENDING;
        this.createdAt = LocalDateTime.now();
        this.description = description;
    }
    
    // Getters
    public String getId() { return id; }
    public TaskType getType() { return type; }
    public TaskPriority getPriority() { return priority; }
    public TaskStatus getStatus() { return status; }
    public LocalDateTime getCreatedAt() { return createdAt; }
    public LocalDateTime getProcessedAt() { return processedAt; }
    public String getDescription() { return description; }
    public String getResult() { return result; }
    
    // Setters
    public void setStatus(TaskStatus status) { this.status = status; }
    public void setProcessedAt(LocalDateTime processedAt) { this.processedAt = processedAt; }
    public void setResult(String result) { this.result = result; }
    
    /**
     * Compare tasks by priority (lower priority number = higher priority).
     * This makes PriorityQueue work as a min-heap for priorities.
     */
    @Override
    public int compareTo(TaskItem other) {
        int priorityCompare = Integer.compare(this.priority.getPriority(), other.priority.getPriority());
        if (priorityCompare != 0) {
            return priorityCompare;
        }
        // If same priority, compare by creation time (older first)
        return this.createdAt.compareTo(other.createdAt);
    }
    
    @Override
    public String toString() {
        return String.format("%s [%s] - %s (%s)", 
            type.getDisplayName(), priority.name(), description, 
            createdAt.format(DateTimeFormatter.ofPattern("HH:mm:ss")));
    }
}

