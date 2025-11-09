package com.belmonthotel.admin.models;

import java.util.ArrayList;
import java.util.List;

/**
 * Node in the room dependency graph.
 * Represents a room with its dependencies.
 */
public class GraphNode {
    private final int roomId;
    private final String roomType;
    private final boolean isAvailable;
    private final List<GraphEdge> edges;
    
    public GraphNode(int roomId, String roomType, boolean isAvailable) {
        this.roomId = roomId;
        this.roomType = roomType;
        this.isAvailable = isAvailable;
        this.edges = new ArrayList<>();
    }
    
    // Getters
    public int getRoomId() { return roomId; }
    public String getRoomType() { return roomType; }
    public boolean isAvailable() { return isAvailable; }
    public List<GraphEdge> getEdges() { return edges; }
    
    /**
     * Add an edge to this node.
     */
    public void addEdge(GraphEdge edge) {
        edges.add(edge);
    }
    
    @Override
    public String toString() {
        return String.format("Room %d (%s) - %s", roomId, roomType, isAvailable ? "Available" : "Booked");
    }
}

