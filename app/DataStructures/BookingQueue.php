<?php

namespace App\DataStructures;

/**
 * BookingQueue - FIFO Queue Implementation
 * 
 * Used for managing concurrent booking requests during peak load.
 * Ensures fairness and prevents database overload by queuing requests.
 */
class BookingQueue
{
    private array $queue = [];
    private int $maxSize;

    public function __construct(int $maxSize = 1000)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Add a booking request to the end of the queue (FIFO)
     */
    public function enqueue($request): bool
    {
        if ($this->isFull()) {
            return false; // Queue is full
        }

        $this->queue[] = $request;
        return true;
    }

    /**
     * Remove and return the first booking request from the queue
     */
    public function dequeue()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return array_shift($this->queue);
    }

    /**
     * Check if the queue is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->queue);
    }

    /**
     * Check if the queue is full
     */
    public function isFull(): bool
    {
        return count($this->queue) >= $this->maxSize;
    }

    /**
     * Get the current number of items in the queue
     */
    public function size(): int
    {
        return count($this->queue);
    }

    /**
     * Get the maximum size of the queue
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Peek at the first item without removing it
     */
    public function peek()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->queue[0];
    }

    /**
     * Clear all items from the queue
     */
    public function clear(): void
    {
        $this->queue = [];
    }

    /**
     * Get all items in the queue as an array
     */
    public function toArray(): array
    {
        return $this->queue;
    }

    /**
     * Check if a specific request exists in the queue
     */
    public function contains($request): bool
    {
        return in_array($request, $this->queue, true);
    }

    /**
     * Remove a specific request from the queue
     */
    public function remove($request): bool
    {
        $key = array_search($request, $this->queue, true);
        if ($key !== false) {
            unset($this->queue[$key]);
            $this->queue = array_values($this->queue); // Re-index array
            return true;
        }
        return false;
    }
}



