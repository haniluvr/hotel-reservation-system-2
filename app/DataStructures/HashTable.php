<?php

namespace App\DataStructures;

/**
 * HashTable - Custom Hash Table Implementation
 * 
 * Used for O(1) lookup of room status, user sessions, and active reservations.
 * Implements collision handling using chaining method.
 */
class HashTable
{
    private array $buckets = [];
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 16)
    {
        $this->size = $size;
        $this->buckets = array_fill(0, $size, []);
    }

    /**
     * Store a key-value pair in the hash table
     */
    public function put($key, $value): void
    {
        $index = $this->hash($key);
        
        // Check if key already exists and update it
        foreach ($this->buckets[$index] as &$pair) {
            if ($pair['key'] === $key) {
                $pair['value'] = $value;
                return;
            }
        }
        
        // Add new key-value pair
        $this->buckets[$index][] = ['key' => $key, 'value' => $value];
        $this->count++;
        
        // Resize if load factor is too high
        if ($this->getLoadFactor() > 0.75) {
            $this->resize();
        }
    }

    /**
     * Retrieve a value by key
     */
    public function get($key)
    {
        $index = $this->hash($key);
        
        foreach ($this->buckets[$index] as $pair) {
            if ($pair['key'] === $key) {
                return $pair['value'];
            }
        }
        
        return null;
    }

    /**
     * Remove a key-value pair from the hash table
     */
    public function remove($key): bool
    {
        $index = $this->hash($key);
        
        foreach ($this->buckets[$index] as $i => $pair) {
            if ($pair['key'] === $key) {
                unset($this->buckets[$index][$i]);
                $this->buckets[$index] = array_values($this->buckets[$index]); // Re-index
                $this->count--;
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if a key exists in the hash table
     */
    public function containsKey($key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all keys in the hash table
     */
    public function keys(): array
    {
        $keys = [];
        foreach ($this->buckets as $bucket) {
            foreach ($bucket as $pair) {
                $keys[] = $pair['key'];
            }
        }
        return $keys;
    }

    /**
     * Get all values in the hash table
     */
    public function values(): array
    {
        $values = [];
        foreach ($this->buckets as $bucket) {
            foreach ($bucket as $pair) {
                $values[] = $pair['value'];
            }
        }
        return $values;
    }

    /**
     * Get the number of key-value pairs
     */
    public function size(): int
    {
        return $this->count;
    }

    /**
     * Check if the hash table is empty
     */
    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    /**
     * Clear all key-value pairs
     */
    public function clear(): void
    {
        $this->buckets = array_fill(0, $this->size, []);
        $this->count = 0;
    }

    /**
     * Get all key-value pairs as an array
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->buckets as $bucket) {
            foreach ($bucket as $pair) {
                $result[$pair['key']] = $pair['value'];
            }
        }
        return $result;
    }

    /**
     * Hash function using djb2 algorithm
     */
    private function hash($key): int
    {
        $keyString = is_string($key) ? $key : serialize($key);
        $hash = 5381;
        
        for ($i = 0; $i < strlen($keyString); $i++) {
            $hash = (($hash << 5) + $hash) + ord($keyString[$i]);
        }
        
        return abs($hash) % $this->size;
    }

    /**
     * Calculate the load factor
     */
    private function getLoadFactor(): float
    {
        return $this->count / $this->size;
    }

    /**
     * Resize the hash table when load factor is too high
     */
    private function resize(): void
    {
        $oldBuckets = $this->buckets;
        $oldSize = $this->size;
        
        $this->size *= 2;
        $this->buckets = array_fill(0, $this->size, []);
        $this->count = 0;
        
        // Rehash all existing key-value pairs
        foreach ($oldBuckets as $bucket) {
            foreach ($bucket as $pair) {
                $this->put($pair['key'], $pair['value']);
            }
        }
    }

    /**
     * Get statistics about the hash table
     */
    public function getStats(): array
    {
        $bucketSizes = array_map('count', $this->buckets);
        $maxBucketSize = max($bucketSizes);
        $minBucketSize = min($bucketSizes);
        $avgBucketSize = array_sum($bucketSizes) / count($bucketSizes);
        
        return [
            'size' => $this->size,
            'count' => $this->count,
            'load_factor' => $this->getLoadFactor(),
            'max_bucket_size' => $maxBucketSize,
            'min_bucket_size' => $minBucketSize,
            'avg_bucket_size' => round($avgBucketSize, 2),
        ];
    }
}



