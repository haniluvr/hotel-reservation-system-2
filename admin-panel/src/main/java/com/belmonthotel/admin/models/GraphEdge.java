package com.belmonthotel.admin.models;

/**
 * Edge in the room dependency graph.
 * Represents a dependency relationship between two rooms.
 */
public class GraphEdge {
    private final GraphNode from;
    private final GraphNode to;
    private final String dependencyType; // "adjacent", "connecting", "suite_requires"
    private final int weight; // For shortest path calculations
    
    public GraphEdge(GraphNode from, GraphNode to, String dependencyType, int weight) {
        this.from = from;
        this.to = to;
        this.dependencyType = dependencyType;
        this.weight = weight;
    }
    
    // Getters
    public GraphNode getFrom() { return from; }
    public GraphNode getTo() { return to; }
    public String getDependencyType() { return dependencyType; }
    public int getWeight() { return weight; }
    
    @Override
    public String toString() {
        return String.format("%s -> %s (%s, weight: %d)", 
            from.getRoomType(), to.getRoomType(), dependencyType, weight);
    }
}

