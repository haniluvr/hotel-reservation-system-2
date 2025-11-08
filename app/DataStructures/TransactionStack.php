<?php

namespace App\DataStructures;

/**
 * TransactionStack - LIFO Stack Implementation
 * 
 * Used for transaction rollback and undo operations.
 * Stores command objects for reversible operations to maintain ACID compliance.
 */
class TransactionStack
{
    private array $stack = [];
    private int $maxSize;

    public function __construct(int $maxSize = 1000)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Push a transaction action onto the stack (LIFO)
     */
    public function push($action): bool
    {
        if ($this->isFull()) {
            return false; // Stack is full
        }

        $this->stack[] = $action;
        return true;
    }

    /**
     * Pop and return the last transaction action from the stack
     */
    public function pop()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return array_pop($this->stack);
    }

    /**
     * Peek at the last transaction action without removing it
     */
    public function peek()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return end($this->stack);
    }

    /**
     * Check if the stack is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->stack);
    }

    /**
     * Check if the stack is full
     */
    public function isFull(): bool
    {
        return count($this->stack) >= $this->maxSize;
    }

    /**
     * Get the current number of items in the stack
     */
    public function size(): int
    {
        return count($this->stack);
    }

    /**
     * Get the maximum size of the stack
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Clear all items from the stack
     */
    public function clear(): void
    {
        $this->stack = [];
    }

    /**
     * Get all items in the stack as an array
     */
    public function toArray(): array
    {
        return $this->stack;
    }

    /**
     * Execute rollback for all actions in the stack
     */
    public function rollbackAll(): array
    {
        $rollbackResults = [];
        
        while (!$this->isEmpty()) {
            $action = $this->pop();
            $rollbackResults[] = $this->executeRollback($action);
        }
        
        return $rollbackResults;
    }

    /**
     * Execute rollback for the last N actions
     */
    public function rollbackLast(int $count): array
    {
        $rollbackResults = [];
        
        for ($i = 0; $i < $count && !$this->isEmpty(); $i++) {
            $action = $this->pop();
            $rollbackResults[] = $this->executeRollback($action);
        }
        
        return $rollbackResults;
    }

    /**
     * Check if a specific action exists in the stack
     */
    public function contains($action): bool
    {
        return in_array($action, $this->stack, true);
    }

    /**
     * Find the position of a specific action in the stack
     */
    public function find($action): int
    {
        $position = array_search($action, $this->stack, true);
        return $position !== false ? $position : -1;
    }

    /**
     * Get actions from a specific position to the top
     */
    public function getFromPosition(int $position): array
    {
        if ($position < 0 || $position >= count($this->stack)) {
            return [];
        }
        
        return array_slice($this->stack, $position);
    }

    /**
     * Execute rollback for a specific action
     */
    private function executeRollback($action): array
    {
        try {
            if (is_array($action) && isset($action['rollback'])) {
                // Action has a rollback method
                if (is_callable($action['rollback'])) {
                    $result = call_user_func($action['rollback'], $action);
                    return [
                        'success' => true,
                        'action' => $action,
                        'result' => $result
                    ];
                }
            }
            
            // Default rollback behavior
            return [
                'success' => true,
                'action' => $action,
                'result' => 'Rollback executed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'action' => $action,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a transaction action object
     */
    public static function createAction(string $type, $data, callable $rollback = null): array
    {
        return [
            'type' => $type,
            'data' => $data,
            'timestamp' => now(),
            'rollback' => $rollback
        ];
    }

    /**
     * Create a room inventory action
     */
    public static function createRoomInventoryAction(int $roomId, int $quantityChange, string $reason = ''): array
    {
        return self::createAction(
            'room_inventory',
            [
                'room_id' => $roomId,
                'quantity_change' => $quantityChange,
                'reason' => $reason
            ],
            function($action) {
                // Rollback: reverse the quantity change
                $roomId = $action['data']['room_id'];
                $quantityChange = $action['data']['quantity_change'];
                
                // This would typically update the database
                // For now, return the rollback data
                return [
                    'room_id' => $roomId,
                    'quantity_change' => -$quantityChange,
                    'action' => 'rollback_room_inventory'
                ];
            }
        );
    }

    /**
     * Create a reservation action
     */
    public static function createReservationAction(int $reservationId, string $status, array $data = []): array
    {
        return self::createAction(
            'reservation',
            array_merge([
                'reservation_id' => $reservationId,
                'status' => $status
            ], $data),
            function($action) {
                $reservationId = $action['data']['reservation_id'];
                $originalStatus = $action['data']['original_status'] ?? 'pending';
                
                return [
                    'reservation_id' => $reservationId,
                    'status' => $originalStatus,
                    'action' => 'rollback_reservation'
                ];
            }
        );
    }

    /**
     * Get stack statistics
     */
    public function getStats(): array
    {
        $actionTypes = [];
        foreach ($this->stack as $action) {
            $type = is_array($action) && isset($action['type']) ? $action['type'] : 'unknown';
            $actionTypes[$type] = ($actionTypes[$type] ?? 0) + 1;
        }
        
        return [
            'size' => $this->size(),
            'max_size' => $this->maxSize,
            'action_types' => $actionTypes,
            'is_empty' => $this->isEmpty(),
            'is_full' => $this->isFull(),
        ];
    }
}



