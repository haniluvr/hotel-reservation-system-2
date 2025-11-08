<?php

namespace App\DataStructures;

/**
 * BST - Balanced Binary Search Tree (AVL) Implementation
 * 
 * Used for efficient room filtering by price/capacity with range queries.
 * Self-balancing tree ensures O(log n) operations.
 */
class BST
{
    private ?BSTNode $root = null;
    private int $size = 0;

    public function __construct()
    {
        $this->root = null;
    }

    /**
     * Insert a new node into the BST
     */
    public function insert($key, $value): void
    {
        $this->root = $this->insertNode($this->root, $key, $value);
        $this->size++;
    }

    /**
     * Search for a node with the given key
     */
    public function search($key)
    {
        return $this->searchNode($this->root, $key);
    }

    /**
     * Find all nodes within a range (min, max)
     */
    public function rangeQuery($min, $max): array
    {
        $result = [];
        $this->rangeQueryHelper($this->root, $min, $max, $result);
        return $result;
    }

    /**
     * Get all nodes in in-order traversal (sorted by key)
     */
    public function inOrderTraversal(): array
    {
        $result = [];
        $this->inOrderHelper($this->root, $result);
        return $result;
    }

    /**
     * Get all nodes in pre-order traversal
     */
    public function preOrderTraversal(): array
    {
        $result = [];
        $this->preOrderHelper($this->root, $result);
        return $result;
    }

    /**
     * Get all nodes in post-order traversal
     */
    public function postOrderTraversal(): array
    {
        $result = [];
        $this->postOrderHelper($this->root, $result);
        return $result;
    }

    /**
     * Find the minimum key in the tree
     */
    public function findMin()
    {
        if ($this->root === null) {
            return null;
        }
        return $this->findMinNode($this->root)->key;
    }

    /**
     * Find the maximum key in the tree
     */
    public function findMax()
    {
        if ($this->root === null) {
            return null;
        }
        return $this->findMaxNode($this->root)->key;
    }

    /**
     * Delete a node with the given key
     */
    public function delete($key): bool
    {
        if ($this->search($key) === null) {
            return false;
        }
        
        $this->root = $this->deleteNode($this->root, $key);
        $this->size--;
        return true;
    }

    /**
     * Check if the tree is empty
     */
    public function isEmpty(): bool
    {
        return $this->root === null;
    }

    /**
     * Get the number of nodes in the tree
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Get the height of the tree
     */
    public function height(): int
    {
        return $this->getHeight($this->root);
    }

    /**
     * Clear all nodes from the tree
     */
    public function clear(): void
    {
        $this->root = null;
        $this->size = 0;
    }

    /**
     * Check if the tree is balanced (AVL property)
     */
    public function isBalanced(): bool
    {
        return $this->isBalancedHelper($this->root);
    }

    /**
     * Get tree statistics
     */
    public function getStats(): array
    {
        return [
            'size' => $this->size,
            'height' => $this->height(),
            'is_balanced' => $this->isBalanced(),
            'min_key' => $this->findMin(),
            'max_key' => $this->findMax(),
        ];
    }

    // Private helper methods

    private function insertNode(?BSTNode $node, $key, $value): BSTNode
    {
        if ($node === null) {
            return new BSTNode($key, $value);
        }

        if ($key < $node->key) {
            $node->left = $this->insertNode($node->left, $key, $value);
        } elseif ($key > $node->key) {
            $node->right = $this->insertNode($node->right, $key, $value);
        } else {
            // Key already exists, update value
            $node->value = $value;
            return $node;
        }

        // Update height
        $node->height = 1 + max($this->getHeight($node->left), $this->getHeight($node->right));

        // Get balance factor
        $balance = $this->getBalance($node);

        // Perform rotations if needed (AVL balancing)
        if ($balance > 1 && $key < $node->left->key) {
            return $this->rightRotate($node);
        }

        if ($balance < -1 && $key > $node->right->key) {
            return $this->leftRotate($node);
        }

        if ($balance > 1 && $key > $node->left->key) {
            $node->left = $this->leftRotate($node->left);
            return $this->rightRotate($node);
        }

        if ($balance < -1 && $key < $node->right->key) {
            $node->right = $this->rightRotate($node->right);
            return $this->leftRotate($node);
        }

        return $node;
    }

    private function searchNode(?BSTNode $node, $key)
    {
        if ($node === null || $node->key === $key) {
            return $node;
        }

        if ($key < $node->key) {
            return $this->searchNode($node->left, $key);
        }

        return $this->searchNode($node->right, $key);
    }

    private function rangeQueryHelper(?BSTNode $node, $min, $max, array &$result): void
    {
        if ($node === null) {
            return;
        }

        if ($node->key >= $min && $node->key <= $max) {
            $result[] = ['key' => $node->key, 'value' => $node->value];
        }

        if ($node->key > $min) {
            $this->rangeQueryHelper($node->left, $min, $max, $result);
        }

        if ($node->key < $max) {
            $this->rangeQueryHelper($node->right, $min, $max, $result);
        }
    }

    private function inOrderHelper(?BSTNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $this->inOrderHelper($node->left, $result);
        $result[] = ['key' => $node->key, 'value' => $node->value];
        $this->inOrderHelper($node->right, $result);
    }

    private function preOrderHelper(?BSTNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $result[] = ['key' => $node->key, 'value' => $node->value];
        $this->preOrderHelper($node->left, $result);
        $this->preOrderHelper($node->right, $result);
    }

    private function postOrderHelper(?BSTNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $this->postOrderHelper($node->left, $result);
        $this->postOrderHelper($node->right, $result);
        $result[] = ['key' => $node->key, 'value' => $node->value];
    }

    private function findMinNode(BSTNode $node): BSTNode
    {
        while ($node->left !== null) {
            $node = $node->left;
        }
        return $node;
    }

    private function findMaxNode(BSTNode $node): BSTNode
    {
        while ($node->right !== null) {
            $node = $node->right;
        }
        return $node;
    }

    private function deleteNode(?BSTNode $node, $key): ?BSTNode
    {
        if ($node === null) {
            return null;
        }

        if ($key < $node->key) {
            $node->left = $this->deleteNode($node->left, $key);
        } elseif ($key > $node->key) {
            $node->right = $this->deleteNode($node->right, $key);
        } else {
            // Node to be deleted found
            if ($node->left === null) {
                return $node->right;
            } elseif ($node->right === null) {
                return $node->left;
            }

            // Node with two children
            $minNode = $this->findMinNode($node->right);
            $node->key = $minNode->key;
            $node->value = $minNode->value;
            $node->right = $this->deleteNode($node->right, $minNode->key);
        }

        // Update height
        $node->height = 1 + max($this->getHeight($node->left), $this->getHeight($node->right));

        // Get balance factor
        $balance = $this->getBalance($node);

        // Perform rotations if needed
        if ($balance > 1 && $this->getBalance($node->left) >= 0) {
            return $this->rightRotate($node);
        }

        if ($balance > 1 && $this->getBalance($node->left) < 0) {
            $node->left = $this->leftRotate($node->left);
            return $this->rightRotate($node);
        }

        if ($balance < -1 && $this->getBalance($node->right) <= 0) {
            return $this->leftRotate($node);
        }

        if ($balance < -1 && $this->getBalance($node->right) > 0) {
            $node->right = $this->rightRotate($node->right);
            return $this->leftRotate($node);
        }

        return $node;
    }

    private function getHeight(?BSTNode $node): int
    {
        return $node === null ? 0 : $node->height;
    }

    private function getBalance(?BSTNode $node): int
    {
        return $node === null ? 0 : $this->getHeight($node->left) - $this->getHeight($node->right);
    }

    private function rightRotate(BSTNode $y): BSTNode
    {
        $x = $y->left;
        $T2 = $x->right;

        $x->right = $y;
        $y->left = $T2;

        $y->height = max($this->getHeight($y->left), $this->getHeight($y->right)) + 1;
        $x->height = max($this->getHeight($x->left), $this->getHeight($x->right)) + 1;

        return $x;
    }

    private function leftRotate(BSTNode $x): BSTNode
    {
        $y = $x->right;
        $T2 = $y->left;

        $y->left = $x;
        $x->right = $T2;

        $x->height = max($this->getHeight($x->left), $this->getHeight($x->right)) + 1;
        $y->height = max($this->getHeight($y->left), $this->getHeight($y->right)) + 1;

        return $y;
    }

    private function isBalancedHelper(?BSTNode $node): bool
    {
        if ($node === null) {
            return true;
        }

        $balance = $this->getBalance($node);
        if (abs($balance) > 1) {
            return false;
        }

        return $this->isBalancedHelper($node->left) && $this->isBalancedHelper($node->right);
    }
}

/**
 * BSTNode - Node class for the Binary Search Tree
 */
class BSTNode
{
    public $key;
    public $value;
    public ?BSTNode $left = null;
    public ?BSTNode $right = null;
    public int $height = 1;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}



