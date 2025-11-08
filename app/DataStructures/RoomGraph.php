<?php

namespace App\DataStructures;

/**
 * RoomGraph - Graph Data Structure Implementation
 * 
 * Used for modeling connected rooms (adjacent suites, family blocks).
 * Implements BFS and DFS traversal algorithms for multi-room booking logic.
 */
class RoomGraph
{
    private array $adjacencyList = [];
    private array $rooms = [];

    public function __construct()
    {
        $this->adjacencyList = [];
        $this->rooms = [];
    }

    /**
     * Add a room to the graph
     */
    public function addRoom(int $roomId, array $roomData = []): void
    {
        if (!isset($this->adjacencyList[$roomId])) {
            $this->adjacencyList[$roomId] = [];
            $this->rooms[$roomId] = $roomData;
        }
    }

    /**
     * Add an edge between two rooms (they are connected)
     */
    public function addEdge(int $room1, int $room2, array $edgeData = []): void
    {
        $this->addRoom($room1);
        $this->addRoom($room2);
        
        $this->adjacencyList[$room1][] = [
            'room_id' => $room2,
            'data' => $edgeData
        ];
        
        $this->adjacencyList[$room2][] = [
            'room_id' => $room1,
            'data' => $edgeData
        ];
    }

    /**
     * Remove an edge between two rooms
     */
    public function removeEdge(int $room1, int $room2): void
    {
        if (isset($this->adjacencyList[$room1])) {
            $this->adjacencyList[$room1] = array_filter(
                $this->adjacencyList[$room1],
                fn($edge) => $edge['room_id'] !== $room2
            );
        }
        
        if (isset($this->adjacencyList[$room2])) {
            $this->adjacencyList[$room2] = array_filter(
                $this->adjacencyList[$room2],
                fn($edge) => $edge['room_id'] !== $room1
            );
        }
    }

    /**
     * Remove a room and all its connections
     */
    public function removeRoom(int $roomId): void
    {
        if (isset($this->adjacencyList[$roomId])) {
            // Remove all edges connected to this room
            foreach ($this->adjacencyList[$roomId] as $edge) {
                $this->removeEdge($roomId, $edge['room_id']);
            }
            
            unset($this->adjacencyList[$roomId]);
            unset($this->rooms[$roomId]);
        }
    }

    /**
     * Get all rooms connected to a specific room
     */
    public function getConnectedRooms(int $roomId): array
    {
        if (!isset($this->adjacencyList[$roomId])) {
            return [];
        }
        
        return array_map(fn($edge) => $edge['room_id'], $this->adjacencyList[$roomId]);
    }

    /**
     * Check if two rooms are connected
     */
    public function areConnected(int $room1, int $room2): bool
    {
        if (!isset($this->adjacencyList[$room1])) {
            return false;
        }
        
        foreach ($this->adjacencyList[$room1] as $edge) {
            if ($edge['room_id'] === $room2) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Breadth-First Search from a starting room
     */
    public function bfs(int $startRoom): array
    {
        if (!isset($this->adjacencyList[$startRoom])) {
            return [];
        }
        
        $visited = [];
        $queue = [$startRoom];
        $result = [];
        
        while (!empty($queue)) {
            $currentRoom = array_shift($queue);
            
            if (!in_array($currentRoom, $visited)) {
                $visited[] = $currentRoom;
                $result[] = $currentRoom;
                
                foreach ($this->adjacencyList[$currentRoom] as $edge) {
                    if (!in_array($edge['room_id'], $visited)) {
                        $queue[] = $edge['room_id'];
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * Depth-First Search from a starting room
     */
    public function dfs(int $startRoom): array
    {
        if (!isset($this->adjacencyList[$startRoom])) {
            return [];
        }
        
        $visited = [];
        $result = [];
        
        $this->dfsHelper($startRoom, $visited, $result);
        
        return $result;
    }

    /**
     * Find all rooms within a certain distance from a starting room
     */
    public function findRoomsWithinDistance(int $startRoom, int $maxDistance): array
    {
        if (!isset($this->adjacencyList[$startRoom])) {
            return [];
        }
        
        $visited = [];
        $queue = [[$startRoom, 0]]; // [room_id, distance]
        $result = [];
        
        while (!empty($queue)) {
            [$currentRoom, $distance] = array_shift($queue);
            
            if (!in_array($currentRoom, $visited) && $distance <= $maxDistance) {
                $visited[] = $currentRoom;
                $result[] = ['room_id' => $currentRoom, 'distance' => $distance];
                
                if ($distance < $maxDistance) {
                    foreach ($this->adjacencyList[$currentRoom] as $edge) {
                        if (!in_array($edge['room_id'], $visited)) {
                            $queue[] = [$edge['room_id'], $distance + 1];
                        }
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * Find the shortest path between two rooms
     */
    public function findShortestPath(int $startRoom, int $endRoom): array
    {
        if (!isset($this->adjacencyList[$startRoom]) || !isset($this->adjacencyList[$endRoom])) {
            return [];
        }
        
        if ($startRoom === $endRoom) {
            return [$startRoom];
        }
        
        $visited = [];
        $queue = [[$startRoom, [$startRoom]]]; // [room_id, path]
        
        while (!empty($queue)) {
            [$currentRoom, $path] = array_shift($queue);
            
            if ($currentRoom === $endRoom) {
                return $path;
            }
            
            if (!in_array($currentRoom, $visited)) {
                $visited[] = $currentRoom;
                
                foreach ($this->adjacencyList[$currentRoom] as $edge) {
                    if (!in_array($edge['room_id'], $visited)) {
                        $newPath = array_merge($path, [$edge['room_id']]);
                        $queue[] = [$edge['room_id'], $newPath];
                    }
                }
            }
        }
        
        return []; // No path found
    }

    /**
     * Find connected components in the graph
     */
    public function findConnectedComponents(): array
    {
        $visited = [];
        $components = [];
        
        foreach (array_keys($this->adjacencyList) as $roomId) {
            if (!in_array($roomId, $visited)) {
                $component = $this->dfs($roomId);
                $components[] = $component;
                $visited = array_merge($visited, $component);
            }
        }
        
        return $components;
    }

    /**
     * Get all rooms that can accommodate a group booking
     */
    public function findGroupBookingRooms(int $startRoom, int $requiredRooms): array
    {
        $connectedRooms = $this->bfs($startRoom);
        
        if (count($connectedRooms) < $requiredRooms) {
            return []; // Not enough connected rooms
        }
        
        // Return the first N rooms including the starting room
        return array_slice($connectedRooms, 0, $requiredRooms);
    }

    /**
     * Check if the graph is connected (all rooms are reachable from any room)
     */
    public function isConnected(): bool
    {
        if (empty($this->adjacencyList)) {
            return true; // Empty graph is considered connected
        }
        
        $components = $this->findConnectedComponents();
        return count($components) === 1;
    }

    /**
     * Get the degree of a room (number of connections)
     */
    public function getDegree(int $roomId): int
    {
        return isset($this->adjacencyList[$roomId]) ? count($this->adjacencyList[$roomId]) : 0;
    }

    /**
     * Get all rooms sorted by degree (most connected first)
     */
    public function getRoomsByDegree(): array
    {
        $roomsWithDegree = [];
        
        foreach (array_keys($this->adjacencyList) as $roomId) {
            $roomsWithDegree[] = [
                'room_id' => $roomId,
                'degree' => $this->getDegree($roomId)
            ];
        }
        
        usort($roomsWithDegree, fn($a, $b) => $b['degree'] <=> $a['degree']);
        
        return $roomsWithDegree;
    }

    /**
     * Get graph statistics
     */
    public function getStats(): array
    {
        $totalRooms = count($this->adjacencyList);
        $totalEdges = 0;
        $degrees = [];
        
        foreach ($this->adjacencyList as $roomId => $edges) {
            $degree = count($edges);
            $totalEdges += $degree;
            $degrees[] = $degree;
        }
        
        $totalEdges = $totalEdges / 2; // Each edge is counted twice
        
        return [
            'total_rooms' => $totalRooms,
            'total_edges' => $totalEdges,
            'is_connected' => $this->isConnected(),
            'connected_components' => count($this->findConnectedComponents()),
            'avg_degree' => $totalRooms > 0 ? round($totalEdges * 2 / $totalRooms, 2) : 0,
            'max_degree' => !empty($degrees) ? max($degrees) : 0,
            'min_degree' => !empty($degrees) ? min($degrees) : 0,
        ];
    }

    /**
     * Get the adjacency list representation
     */
    public function getAdjacencyList(): array
    {
        return $this->adjacencyList;
    }

    /**
     * Get all rooms data
     */
    public function getRooms(): array
    {
        return $this->rooms;
    }

    /**
     * Clear the graph
     */
    public function clear(): void
    {
        $this->adjacencyList = [];
        $this->rooms = [];
    }

    // Private helper methods

    private function dfsHelper(int $roomId, array &$visited, array &$result): void
    {
        if (in_array($roomId, $visited)) {
            return;
        }
        
        $visited[] = $roomId;
        $result[] = $roomId;
        
        if (isset($this->adjacencyList[$roomId])) {
            foreach ($this->adjacencyList[$roomId] as $edge) {
                $this->dfsHelper($edge['room_id'], $visited, $result);
            }
        }
    }
}



