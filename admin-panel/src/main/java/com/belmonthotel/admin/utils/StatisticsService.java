package com.belmonthotel.admin.utils;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Service class for dashboard statistics.
 */
public class StatisticsService {
    
    /**
     * Get today's bookings count.
     */
    public static int getTodaysBookings() {
        String query = "SELECT COUNT(*) as count FROM reservations WHERE DATE(created_at) = CURDATE()";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt("count");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 0;
    }
    
    /**
     * Get today's revenue.
     */
    public static double getTodaysRevenue() {
        String query = "SELECT COALESCE(SUM(total_amount), 0) as total " +
                      "FROM reservations WHERE DATE(created_at) = CURDATE() " +
                      "AND status IN ('confirmed', 'completed')";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getDouble("total");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 0;
    }
    
    /**
     * Get upcoming check-ins (next 7 days).
     */
    public static int getUpcomingCheckIns() {
        String query = "SELECT COUNT(*) as count FROM reservations " +
                      "WHERE check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) " +
                      "AND status IN ('pending', 'confirmed')";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt("count");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 0;
    }
    
    /**
     * Get upcoming check-outs (next 7 days).
     */
    public static int getUpcomingCheckOuts() {
        String query = "SELECT COUNT(*) as count FROM reservations " +
                      "WHERE check_out_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) " +
                      "AND status IN ('pending', 'confirmed')";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt("count");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 0;
    }
    
    /**
     * Calculate occupancy percentage.
     */
    public static double getOccupancyPercentage() {
        String query = "SELECT " +
                      "SUM(CASE WHEN r.status IN ('pending', 'confirmed', 'completed') THEN 1 ELSE 0 END) as booked, " +
                      "SUM(rm.quantity) as total " +
                      "FROM rooms rm " +
                      "LEFT JOIN reservations r ON rm.id = r.room_id " +
                      "WHERE rm.is_active = 1";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                int booked = rs.getInt("booked");
                int total = rs.getInt("total");
                if (total > 0) {
                    return (booked * 100.0) / total;
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 0;
    }
    
    /**
     * Get average booking value.
     */
    public static double getAverageBookingValue() {
        String query = "SELECT COALESCE(AVG(total_amount), 0) as avg FROM reservations " +
                      "WHERE status IN ('confirmed', 'completed')";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getDouble("avg");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 0;
    }
    
    /**
     * Get recent bookings (last 10).
     */
    public static List<Map<String, Object>> getRecentBookings(int limit) {
        List<Map<String, Object>> bookings = new ArrayList<>();
        String query = "SELECT r.reservation_number, r.check_in_date, r.check_out_date, " +
                      "r.status, r.total_amount, u.name as guest_name " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "ORDER BY r.created_at DESC LIMIT ?";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setInt(1, limit);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> booking = new HashMap<>();
                booking.put("reservation_number", rs.getString("reservation_number"));
                booking.put("guest_name", rs.getString("guest_name"));
                booking.put("check_in_date", rs.getDate("check_in_date"));
                booking.put("check_out_date", rs.getDate("check_out_date"));
                booking.put("status", rs.getString("status"));
                booking.put("total_amount", rs.getDouble("total_amount"));
                bookings.add(booking);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return bookings;
    }
    
    /**
     * Get recent payments (last 10).
     */
    public static List<Map<String, Object>> getRecentPayments(int limit) {
        List<Map<String, Object>> payments = new ArrayList<>();
        String query = "SELECT p.id, p.amount, p.status, p.paid_at, r.reservation_number " +
                      "FROM payments p " +
                      "JOIN reservations r ON p.reservation_id = r.id " +
                      "ORDER BY p.created_at DESC LIMIT ?";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setInt(1, limit);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> payment = new HashMap<>();
                payment.put("id", rs.getInt("id"));
                payment.put("amount", rs.getDouble("amount"));
                payment.put("status", rs.getString("status"));
                payment.put("paid_at", rs.getTimestamp("paid_at"));
                payment.put("reservation_number", rs.getString("reservation_number"));
                payments.add(payment);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return payments;
    }
    
    /**
     * Get revenue data for chart (last 30 days).
     */
    public static List<Map<String, Object>> getRevenueChartData(int days) {
        List<Map<String, Object>> data = new ArrayList<>();
        String query = "SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue " +
                      "FROM reservations " +
                      "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) " +
                      "AND status IN ('confirmed', 'completed') " +
                      "GROUP BY DATE(created_at) " +
                      "ORDER BY date ASC";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setInt(1, days);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> point = new HashMap<>();
                point.put("date", rs.getDate("date"));
                point.put("revenue", rs.getDouble("revenue"));
                data.add(point);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return data;
    }
    
    /**
     * Get booking status distribution.
     */
    public static Map<String, Integer> getBookingStatusDistribution() {
        Map<String, Integer> distribution = new HashMap<>();
        String query = "SELECT status, COUNT(*) as count FROM reservations GROUP BY status";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                distribution.put(rs.getString("status"), rs.getInt("count"));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return distribution;
    }
}

