package com.belmonthotel.admin.utils;

import com.belmonthotel.admin.models.TaskItem;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;

import java.util.PriorityQueue;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;
import java.util.function.Consumer;

/**
 * Service for managing background task processing using PriorityQueue.
 * Tasks are processed based on priority (URGENT > NORMAL > LOW).
 */
public class TaskQueueService {
    
    private static TaskQueueService instance;
    private final PriorityQueue<TaskItem> taskQueue;
    private final ObservableList<TaskItem> taskHistory;
    private final ScheduledExecutorService executorService;
    private TaskItem currentTask;
    private boolean isProcessing;
    private Consumer<TaskItem> onTaskCompleted;
    
    private TaskQueueService() {
        this.taskQueue = new PriorityQueue<>();
        this.taskHistory = FXCollections.observableArrayList();
        this.executorService = Executors.newScheduledThreadPool(2);
        this.isProcessing = false;
        
        // Start background processor
        startProcessor();
    }
    
    public static synchronized TaskQueueService getInstance() {
        if (instance == null) {
            instance = new TaskQueueService();
        }
        return instance;
    }
    
    /**
     * Add a task to the priority queue.
     */
    public void addTask(TaskItem task) {
        taskQueue.offer(task);
        Platform.runLater(() -> {
            // Notify UI if needed
        });
    }
    
    /**
     * Get the current task being processed.
     */
    public TaskItem getCurrentTask() {
        return currentTask;
    }
    
    /**
     * Get the number of pending tasks.
     */
    public int getPendingTaskCount() {
        return taskQueue.size();
    }
    
    /**
     * Get task history (last 10 completed tasks).
     */
    public ObservableList<TaskItem> getTaskHistory() {
        return taskHistory;
    }
    
    /**
     * Check if queue is processing.
     */
    public boolean isProcessing() {
        return isProcessing;
    }
    
    /**
     * Set callback for when task is completed.
     */
    public void setOnTaskCompleted(Consumer<TaskItem> callback) {
        this.onTaskCompleted = callback;
    }
    
    /**
     * Start processing tasks from the queue.
     */
    private void startProcessor() {
        executorService.scheduleWithFixedDelay(() -> {
            if (!isProcessing && !taskQueue.isEmpty()) {
                processNextTask();
            }
        }, 0, 2, TimeUnit.SECONDS); // Check every 2 seconds
    }
    
    /**
     * Process the next task in the queue.
     */
    private void processNextTask() {
        if (taskQueue.isEmpty()) {
            return;
        }
        
        isProcessing = true;
        currentTask = taskQueue.poll();
        
        if (currentTask == null) {
            isProcessing = false;
            return;
        }
        
        currentTask.setStatus(TaskItem.TaskStatus.PROCESSING);
        
        // Simulate task processing based on type
        executorService.submit(() -> {
            try {
                processTask(currentTask);
                
                currentTask.setStatus(TaskItem.TaskStatus.COMPLETED);
                currentTask.setProcessedAt(java.time.LocalDateTime.now());
                currentTask.setResult("Task completed successfully");
                
            } catch (Exception e) {
                currentTask.setStatus(TaskItem.TaskStatus.FAILED);
                currentTask.setResult("Error: " + e.getMessage());
                e.printStackTrace();
            } finally {
                // Add to history (keep last 10)
                Platform.runLater(() -> {
                    taskHistory.add(0, currentTask);
                    if (taskHistory.size() > 10) {
                        taskHistory.remove(taskHistory.size() - 1);
                    }
                    
                    if (onTaskCompleted != null) {
                        onTaskCompleted.accept(currentTask);
                    }
                });
                
                currentTask = null;
                isProcessing = false;
            }
        });
    }
    
    /**
     * Process a specific task based on its type.
     */
    private void processTask(TaskItem task) throws InterruptedException {
        switch (task.getType()) {
            case EMAIL_NOTIFICATION:
                // Simulate email sending
                Thread.sleep(500);
                break;
            case REPORT_GENERATION:
                // Simulate report generation
                Thread.sleep(1000);
                break;
            case DATA_SYNC:
                // Simulate data synchronization
                Thread.sleep(800);
                break;
            case STATISTICS_UPDATE:
                // Simulate statistics update
                Thread.sleep(300);
                break;
            case CHECK_IN_REMINDER:
                // Simulate check-in reminder
                Thread.sleep(400);
                break;
        }
    }
    
    /**
     * Manually trigger queue processing.
     */
    public void processQueue() {
        if (!isProcessing && !taskQueue.isEmpty()) {
            processNextTask();
        }
    }
    
    /**
     * Shutdown the service.
     */
    public void shutdown() {
        executorService.shutdown();
    }
}

