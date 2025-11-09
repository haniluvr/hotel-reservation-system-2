package com.belmonthotel.admin.utils;

import com.belmonthotel.admin.models.GraphEdge;
import com.belmonthotel.admin.models.GraphNode;

import java.util.*;

/**
 * Graph data structure for room dependencies.
 * Implements BFS, DFS, and shortest path algorithms.
 */
public class RoomDependencyGraph {
    
    private final Map<Integer, GraphNode> nodes;
    private final List<GraphEdge> edges;
    
    public RoomDependencyGraph() {
        this.nodes = new HashMap<>();
        this.edges = new ArrayList<>();
    }
    
    /**
     * Add a node to the graph.
     */
    public void addNode(GraphNode node) {
        nodes.put(node.getRoomId(), node);
    }
    
    /**
     * Add an edge between two nodes.
     */
    public void addEdge(GraphNode from, GraphNode to, String dependencyType, int weight) {
        GraphEdge edge = new GraphEdge(from, to, dependencyType, weight);
        edges.add(edge);
        from.addEdge(edge);
    }
    
    /**
     * Get a node by room ID.
     */
    public GraphNode getNode(int roomId) {
        return nodes.get(roomId);
    }
    
    /**
     * Get all nodes.
     */
    public Collection<GraphNode> getAllNodes() {
        return nodes.values();
    }
    
    /**
     * Get all edges.
     */
    public List<GraphEdge> getAllEdges() {
        return edges;
    }
    
    /**
     * Breadth-First Search (BFS) to find available room chains.
     * Returns a list of room IDs in the chain.
     */
    public List<Integer> bfsFindAvailableChain(int startRoomId) {
        List<Integer> chain = new ArrayList<>();
        if (!nodes.containsKey(startRoomId)) {
            return chain;
        }
        
        Queue<GraphNode> queue = new LinkedList<>();
        Set<Integer> visited = new HashSet<>();
        
        GraphNode start = nodes.get(startRoomId);
        queue.offer(start);
        visited.add(startRoomId);
        
        while (!queue.isEmpty()) {
            GraphNode current = queue.poll();
            chain.add(current.getRoomId());
            
            // Add adjacent available rooms
            for (GraphEdge edge : current.getEdges()) {
                GraphNode neighbor = edge.getTo();
                if (!visited.contains(neighbor.getRoomId()) && neighbor.isAvailable()) {
                    visited.add(neighbor.getRoomId());
                    queue.offer(neighbor);
                }
            }
        }
        
        return chain;
    }
    
    /**
     * Depth-First Search (DFS) for dependency traversal.
     * Returns all rooms reachable from the start room.
     */
    public List<Integer> dfsTraverseDependencies(int startRoomId) {
        List<Integer> result = new ArrayList<>();
        if (!nodes.containsKey(startRoomId)) {
            return result;
        }
        
        Set<Integer> visited = new HashSet<>();
        dfsHelper(nodes.get(startRoomId), visited, result);
        
        return result;
    }
    
    private void dfsHelper(GraphNode node, Set<Integer> visited, List<Integer> result) {
        visited.add(node.getRoomId());
        result.add(node.getRoomId());
        
        for (GraphEdge edge : node.getEdges()) {
            GraphNode neighbor = edge.getTo();
            if (!visited.contains(neighbor.getRoomId())) {
                dfsHelper(neighbor, visited, result);
            }
        }
    }
    
    /**
     * Find shortest path between two rooms using Dijkstra's algorithm.
     * Returns the path as a list of room IDs.
     */
    public List<Integer> shortestPath(int fromRoomId, int toRoomId) {
        if (!nodes.containsKey(fromRoomId) || !nodes.containsKey(toRoomId)) {
            return new ArrayList<>();
        }
        
        Map<Integer, Integer> distances = new HashMap<>();
        Map<Integer, Integer> previous = new HashMap<>();
        PriorityQueue<int[]> pq = new PriorityQueue<>(Comparator.comparingInt(a -> a[1]));
        Set<Integer> visited = new HashSet<>();
        
        // Initialize distances
        for (Integer roomId : nodes.keySet()) {
            distances.put(roomId, Integer.MAX_VALUE);
        }
        distances.put(fromRoomId, 0);
        pq.offer(new int[]{fromRoomId, 0});
        
        while (!pq.isEmpty()) {
            int[] current = pq.poll();
            int currentRoomId = current[0];
            int currentDist = current[1];
            
            if (visited.contains(currentRoomId)) {
                continue;
            }
            visited.add(currentRoomId);
            
            if (currentRoomId == toRoomId) {
                break;
            }
            
            GraphNode currentNode = nodes.get(currentRoomId);
            for (GraphEdge edge : currentNode.getEdges()) {
                GraphNode neighbor = edge.getTo();
                int neighborId = neighbor.getRoomId();
                int newDist = currentDist + edge.getWeight();
                
                if (newDist < distances.get(neighborId)) {
                    distances.put(neighborId, newDist);
                    previous.put(neighborId, currentRoomId);
                    pq.offer(new int[]{neighborId, newDist});
                }
            }
        }
        
        // Reconstruct path
        List<Integer> path = new ArrayList<>();
        if (distances.get(toRoomId) == Integer.MAX_VALUE) {
            return path; // No path found
        }
        
        int current = toRoomId;
        while (current != fromRoomId) {
            path.add(0, current);
            Integer prev = previous.get(current);
            if (prev == null) {
                return new ArrayList<>(); // Path broken
            }
            current = prev;
        }
        path.add(0, fromRoomId);
        
        return path;
    }
    
    /**
     * Get graph statistics.
     */
    public Map<String, Object> getStatistics() {
        Map<String, Object> stats = new HashMap<>();
        stats.put("nodes", nodes.size());
        stats.put("edges", edges.size());
        
        // Count connected components
        int components = 0;
        Set<Integer> visited = new HashSet<>();
        for (GraphNode node : nodes.values()) {
            if (!visited.contains(node.getRoomId())) {
                components++;
                dfsHelper(node, visited, new ArrayList<>());
            }
        }
        stats.put("connectedComponents", components);
        
        return stats;
    }
}

