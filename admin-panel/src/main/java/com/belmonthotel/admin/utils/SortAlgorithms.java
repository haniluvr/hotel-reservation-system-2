package com.belmonthotel.admin.utils;

import java.util.Comparator;
import java.util.List;

/**
 * Utility class demonstrating various sorting algorithms.
 * Implements Quick Sort, Merge Sort, and Heap Sort for educational purposes.
 */
public class SortAlgorithms {
    
    /**
     * Result object containing sorted data and performance metrics.
     */
    public static class SortResult<T> {
        private final List<T> sortedData;
        private final long executionTime;
        private final int comparisons;
        private final String algorithmName;
        
        public SortResult(List<T> sortedData, long executionTime, int comparisons, String algorithmName) {
            this.sortedData = sortedData;
            this.executionTime = executionTime;
            this.comparisons = comparisons;
            this.algorithmName = algorithmName;
        }
        
        public List<T> getSortedData() { return sortedData; }
        public long getExecutionTime() { return executionTime; }
        public int getComparisons() { return comparisons; }
        public String getAlgorithmName() { return algorithmName; }
    }
    
    /**
     * Quick Sort algorithm implementation.
     * Time Complexity: O(n log n) average, O(n²) worst case
     * Space Complexity: O(log n)
     */
    public static <T> SortResult<T> quickSort(List<T> list, Comparator<T> comparator) {
        long startTime = System.nanoTime();
        int[] comparisons = {0};
        
        List<T> sorted = new java.util.ArrayList<>(list);
        quickSortHelper(sorted, 0, sorted.size() - 1, comparator, comparisons);
        
        long endTime = System.nanoTime();
        long executionTime = (endTime - startTime) / 1_000_000; // Convert to milliseconds
        
        return new SortResult<>(sorted, executionTime, comparisons[0], "Quick Sort");
    }
    
    private static <T> void quickSortHelper(List<T> list, int low, int high, 
                                           Comparator<T> comparator, int[] comparisons) {
        if (low < high) {
            int pivotIndex = partition(list, low, high, comparator, comparisons);
            quickSortHelper(list, low, pivotIndex - 1, comparator, comparisons);
            quickSortHelper(list, pivotIndex + 1, high, comparator, comparisons);
        }
    }
    
    private static <T> int partition(List<T> list, int low, int high, 
                                    Comparator<T> comparator, int[] comparisons) {
        T pivot = list.get(high);
        int i = low - 1;
        
        for (int j = low; j < high; j++) {
            comparisons[0]++;
            if (comparator.compare(list.get(j), pivot) < 0) {
                i++;
                swap(list, i, j);
            }
        }
        swap(list, i + 1, high);
        return i + 1;
    }
    
    /**
     * Merge Sort algorithm implementation.
     * Time Complexity: O(n log n) worst case
     * Space Complexity: O(n)
     */
    public static <T> SortResult<T> mergeSort(List<T> list, Comparator<T> comparator) {
        long startTime = System.nanoTime();
        int[] comparisons = {0};
        
        List<T> sorted = new java.util.ArrayList<>(list);
        mergeSortHelper(sorted, 0, sorted.size() - 1, comparator, comparisons);
        
        long endTime = System.nanoTime();
        long executionTime = (endTime - startTime) / 1_000_000; // Convert to milliseconds
        
        return new SortResult<>(sorted, executionTime, comparisons[0], "Merge Sort");
    }
    
    private static <T> void mergeSortHelper(List<T> list, int left, int right, 
                                            Comparator<T> comparator, int[] comparisons) {
        if (left < right) {
            int mid = left + (right - left) / 2;
            mergeSortHelper(list, left, mid, comparator, comparisons);
            mergeSortHelper(list, mid + 1, right, comparator, comparisons);
            merge(list, left, mid, right, comparator, comparisons);
        }
    }
    
    @SuppressWarnings("unchecked")
    private static <T> void merge(List<T> list, int left, int mid, int right, 
                                 Comparator<T> comparator, int[] comparisons) {
        int n1 = mid - left + 1;
        int n2 = right - mid;
        
        List<T> leftArray = new java.util.ArrayList<>();
        List<T> rightArray = new java.util.ArrayList<>();
        
        for (int i = 0; i < n1; i++) {
            leftArray.add(list.get(left + i));
        }
        for (int j = 0; j < n2; j++) {
            rightArray.add(list.get(mid + 1 + j));
        }
        
        int i = 0, j = 0, k = left;
        
        while (i < n1 && j < n2) {
            comparisons[0]++;
            if (comparator.compare(leftArray.get(i), rightArray.get(j)) <= 0) {
                list.set(k, leftArray.get(i));
                i++;
            } else {
                list.set(k, rightArray.get(j));
                j++;
            }
            k++;
        }
        
        while (i < n1) {
            list.set(k, leftArray.get(i));
            i++;
            k++;
        }
        
        while (j < n2) {
            list.set(k, rightArray.get(j));
            j++;
            k++;
        }
    }
    
    /**
     * Heap Sort algorithm implementation.
     * Time Complexity: O(n log n) worst case
     * Space Complexity: O(1)
     */
    public static <T> SortResult<T> heapSort(List<T> list, Comparator<T> comparator) {
        long startTime = System.nanoTime();
        int[] comparisons = {0};
        
        List<T> sorted = new java.util.ArrayList<>(list);
        int n = sorted.size();
        
        // Build max heap
        for (int i = n / 2 - 1; i >= 0; i--) {
            heapify(sorted, n, i, comparator, comparisons);
        }
        
        // Extract elements from heap one by one
        for (int i = n - 1; i > 0; i--) {
            swap(sorted, 0, i);
            heapify(sorted, i, 0, comparator, comparisons);
        }
        
        long endTime = System.nanoTime();
        long executionTime = (endTime - startTime) / 1_000_000; // Convert to milliseconds
        
        return new SortResult<>(sorted, executionTime, comparisons[0], "Heap Sort");
    }
    
    private static <T> void heapify(List<T> list, int n, int i, 
                                    Comparator<T> comparator, int[] comparisons) {
        int largest = i;
        int left = 2 * i + 1;
        int right = 2 * i + 2;
        
        if (left < n) {
            comparisons[0]++;
            if (comparator.compare(list.get(left), list.get(largest)) > 0) {
                largest = left;
            }
        }
        
        if (right < n) {
            comparisons[0]++;
            if (comparator.compare(list.get(right), list.get(largest)) > 0) {
                largest = right;
            }
        }
        
        if (largest != i) {
            swap(list, i, largest);
            heapify(list, n, largest, comparator, comparisons);
        }
    }
    
    /**
     * Helper method to swap two elements in a list.
     */
    private static <T> void swap(List<T> list, int i, int j) {
        T temp = list.get(i);
        list.set(i, list.get(j));
        list.set(j, temp);
    }
}

