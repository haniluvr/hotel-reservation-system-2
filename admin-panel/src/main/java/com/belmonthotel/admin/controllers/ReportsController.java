package com.belmonthotel.admin.controllers;

import com.belmonthotel.admin.utils.DatabaseConnection;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.stage.FileChooser;
import org.apache.poi.ss.usermodel.*;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import org.apache.poi.ss.usermodel.Cell;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.pdmodel.PDPage;
import org.apache.pdfbox.pdmodel.PDPageContentStream;
import org.apache.pdfbox.pdmodel.font.PDType1Font;
import org.apache.pdfbox.pdmodel.font.Standard14Fonts;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.ResourceBundle;

/**
 * Controller for the reports generation module.
 */
public class ReportsController implements Initializable {
    @FXML
    private ComboBox<String> reportTypeCombo;

    @FXML
    private DatePicker dateFromPicker;

    @FXML
    private DatePicker dateToPicker;

    @FXML
    private Button generateExcelBtn;

    @FXML
    private Button generatePDFBtn;

    @FXML
    private Button generateCSVBtn;

    @FXML
    private TextArea reportPreview;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        // Set report type options
        reportTypeCombo.getItems().addAll(
            "Booking Report",
            "Revenue Report (Daily)",
            "Revenue Report (Weekly)",
            "Revenue Report (Monthly)",
            "Revenue by Room Type",
            "Revenue by Payment Method",
            "Booking Statistics",
            "Bookings by Status",
            "Bookings by Room Type",
            "Booking Trends",
            "Occupancy Report (Daily)",
            "Occupancy Report (Weekly)",
            "Occupancy Report (Monthly)",
            "Occupancy by Room Type",
            "User Activity Report"
        );
        reportTypeCombo.setValue("Booking Report");

        // Set default date range (last 30 days)
        dateToPicker.setValue(LocalDate.now());
        dateFromPicker.setValue(LocalDate.now().minusDays(30));

        // Set button actions
        generateExcelBtn.setOnAction(e -> generateExcelReport());
        generatePDFBtn.setOnAction(e -> generatePDFReport());
        if (generateCSVBtn != null) {
            generateCSVBtn.setOnAction(e -> generateCSVReport());
        }

        // Update preview when options change
        reportTypeCombo.setOnAction(e -> updatePreview());
        dateFromPicker.valueProperty().addListener((obs, oldVal, newVal) -> updatePreview());
        dateToPicker.valueProperty().addListener((obs, oldVal, newVal) -> updatePreview());
        
        updatePreview();
    }

    /**
     * Update the report preview.
     */
    private void updatePreview() {
        String reportType = reportTypeCombo.getValue();
        LocalDate fromDate = dateFromPicker.getValue();
        LocalDate toDate = dateToPicker.getValue();

        if (reportType == null || fromDate == null || toDate == null) {
            return;
        }

        StringBuilder preview = new StringBuilder();
        preview.append("Report Type: ").append(reportType).append("\n");
        preview.append("Date Range: ").append(fromDate).append(" to ").append(toDate).append("\n\n");

        try {
            if (reportType.startsWith("Revenue Report")) {
                if (reportType.contains("Daily")) {
                    preview.append(generateDailyRevenueReportPreview(fromDate, toDate));
                } else if (reportType.contains("Weekly")) {
                    preview.append(generateWeeklyRevenueReportPreview(fromDate, toDate));
                } else if (reportType.contains("Monthly")) {
                    preview.append(generateMonthlyRevenueReportPreview(fromDate, toDate));
                } else {
                    preview.append(generateRevenueReportPreview(fromDate, toDate));
                }
            } else if (reportType.equals("Revenue by Room Type")) {
                preview.append(generateRevenueByRoomTypePreview(fromDate, toDate));
            } else if (reportType.equals("Revenue by Payment Method")) {
                preview.append(generateRevenueByPaymentMethodPreview(fromDate, toDate));
            } else if (reportType.equals("Booking Statistics")) {
                preview.append(generateBookingStatisticsPreview(fromDate, toDate));
            } else if (reportType.equals("Bookings by Status")) {
                preview.append(generateBookingsByStatusPreview(fromDate, toDate));
            } else if (reportType.equals("Bookings by Room Type")) {
                preview.append(generateBookingsByRoomTypePreview(fromDate, toDate));
            } else if (reportType.equals("Booking Trends")) {
                preview.append(generateBookingTrendsPreview(fromDate, toDate));
            } else if (reportType.startsWith("Occupancy Report")) {
                if (reportType.contains("Daily")) {
                    preview.append(generateDailyOccupancyReportPreview(fromDate, toDate));
                } else if (reportType.contains("Weekly")) {
                    preview.append(generateWeeklyOccupancyReportPreview(fromDate, toDate));
                } else if (reportType.contains("Monthly")) {
                    preview.append(generateMonthlyOccupancyReportPreview(fromDate, toDate));
                } else {
                    preview.append(generateOccupancyReportPreview(fromDate, toDate));
                }
            } else if (reportType.equals("Occupancy by Room Type")) {
                preview.append(generateOccupancyByRoomTypePreview(fromDate, toDate));
            } else if (reportType.equals("Booking Report")) {
                preview.append(generateBookingReportPreview(fromDate, toDate));
            } else if (reportType.equals("User Activity Report")) {
                preview.append(generateUserActivityReportPreview(fromDate, toDate));
            }
        } catch (SQLException e) {
            preview.append("Error generating preview: ").append(e.getMessage());
        }

        reportPreview.setText(preview.toString());
    }

    /**
     * Generate booking report preview.
     */
    private String generateBookingReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT COUNT(*) as total, " +
                      "SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed, " +
                      "SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled " +
                      "FROM reservations " +
                      "WHERE check_in_date BETWEEN ? AND ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                return String.format("Total Bookings: %d\nConfirmed: %d\nCancelled: %d",
                    rs.getInt("total"), rs.getInt("confirmed"), rs.getInt("cancelled"));
            }
        }
        return "No data available";
    }

    /**
     * Generate revenue report preview.
     */
    private String generateRevenueReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue, " +
                      "COUNT(*) as total_bookings " +
                      "FROM reservations " +
                      "WHERE status IN ('confirmed', 'completed') " +
                      "AND check_in_date BETWEEN ? AND ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                return String.format("Total Revenue: ₱%.2f\nTotal Bookings: %d",
                    rs.getDouble("total_revenue"), rs.getInt("total_bookings"));
            }
        }
        return "No data available";
    }

    /**
     * Generate occupancy report preview.
     */
    private String generateOccupancyReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT COUNT(DISTINCT room_id) as rooms_booked, " +
                      "COUNT(*) as total_bookings " +
                      "FROM reservations " +
                      "WHERE status = 'confirmed' " +
                      "AND check_in_date BETWEEN ? AND ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                return String.format("Rooms Booked: %d\nTotal Bookings: %d",
                    rs.getInt("rooms_booked"), rs.getInt("total_bookings"));
            }
        }
        return "No data available";
    }

    /**
     * Generate user activity report preview.
     */
    private String generateUserActivityReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT COUNT(DISTINCT user_id) as active_users, " +
                      "COUNT(*) as total_bookings " +
                      "FROM reservations " +
                      "WHERE created_at BETWEEN ? AND ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                return String.format("Active Users: %d\nTotal Bookings: %d",
                    rs.getInt("active_users"), rs.getInt("total_bookings"));
            }
        }
        return "No data available";
    }

    /**
     * Generate Excel report.
     */
    private void generateExcelReport() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Save Excel Report");
        fileChooser.getExtensionFilters().add(
            new FileChooser.ExtensionFilter("Excel Files", "*.xlsx")
        );
        fileChooser.setInitialFileName("report_" + LocalDate.now().format(DateTimeFormatter.ofPattern("yyyyMMdd")) + ".xlsx");

        File file = fileChooser.showSaveDialog(null);
        if (file == null) {
            return;
        }

        try (Workbook workbook = new XSSFWorkbook()) {
            Sheet sheet = workbook.createSheet("Report");
            
            // Create header style
            CellStyle headerStyle = workbook.createCellStyle();
            Font headerFont = workbook.createFont();
            headerFont.setBold(true);
            headerStyle.setFont(headerFont);
            
            // Generate report based on type
            String reportType = reportTypeCombo.getValue();
            LocalDate fromDate = dateFromPicker.getValue();
            LocalDate toDate = dateToPicker.getValue();

            if (reportType != null && fromDate != null && toDate != null) {
                generateExcelData(sheet, reportType, fromDate, toDate, headerStyle);
            }

            // Auto-size columns
            for (int i = 0; i < 10; i++) {
                sheet.autoSizeColumn(i);
            }

            // Write to file
            try (FileOutputStream outputStream = new FileOutputStream(file)) {
                workbook.write(outputStream);
            }

            showAlert(Alert.AlertType.INFORMATION, "Success", "Excel report generated successfully!");
        } catch (IOException | SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Error", "Failed to generate Excel report: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Generate Excel data based on report type.
     */
    private void generateExcelData(Sheet sheet, String reportType, LocalDate fromDate, LocalDate toDate, 
                                   CellStyle headerStyle) throws SQLException {
        switch (reportType) {
            case "Booking Report":
                Row bookingHeaderRow = sheet.createRow(0);
                generateBookingReportExcel(sheet, bookingHeaderRow, fromDate, toDate, headerStyle);
                break;
            case "Revenue Report":
                Row revenueHeaderRow = sheet.createRow(0);
                generateRevenueReportExcel(sheet, revenueHeaderRow, fromDate, toDate, headerStyle);
                break;
            // Add other report types as needed
        }
    }

    /**
     * Generate booking report in Excel format.
     */
    private void generateBookingReportExcel(Sheet sheet, Row headerRow, LocalDate fromDate, LocalDate toDate,
                                           CellStyle headerStyle) throws SQLException {
        String[] headers = {"ID", "Reservation Number", "Guest Name", "Hotel", "Check-in", "Check-out", "Status", "Amount"};
        
        for (int i = 0; i < headers.length; i++) {
            org.apache.poi.ss.usermodel.Cell cell = headerRow.createCell(i);
            cell.setCellValue(headers[i]);
            cell.setCellStyle(headerStyle);
        }

        String query = "SELECT r.id, r.reservation_number, u.name as guest_name, " +
                      "r.check_in_date, r.check_out_date, r.status, r.total_amount " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "WHERE r.check_in_date BETWEEN ? AND ? " +
                      "ORDER BY r.created_at DESC";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();

            int rowNum = 1;
            while (rs.next()) {
                Row row = sheet.createRow(rowNum++);
                org.apache.poi.ss.usermodel.Cell cell0 = row.createCell(0);
                cell0.setCellValue(rs.getInt("id"));
                org.apache.poi.ss.usermodel.Cell cell1 = row.createCell(1);
                cell1.setCellValue(rs.getString("reservation_number"));
                org.apache.poi.ss.usermodel.Cell cell2 = row.createCell(2);
                cell2.setCellValue(rs.getString("guest_name"));
                org.apache.poi.ss.usermodel.Cell cell3 = row.createCell(3);
                cell3.setCellValue("Belmont Hotel");
                org.apache.poi.ss.usermodel.Cell cell4 = row.createCell(4);
                cell4.setCellValue(rs.getDate("check_in_date").toString());
                org.apache.poi.ss.usermodel.Cell cell5 = row.createCell(5);
                cell5.setCellValue(rs.getDate("check_out_date").toString());
                org.apache.poi.ss.usermodel.Cell cell6 = row.createCell(6);
                cell6.setCellValue(rs.getString("status"));
                org.apache.poi.ss.usermodel.Cell cell7 = row.createCell(7);
                cell7.setCellValue(rs.getDouble("total_amount"));
            }
        }
    }

    /**
     * Generate revenue report in Excel format.
     */
    private void generateRevenueReportExcel(Sheet sheet, Row headerRow, LocalDate fromDate, LocalDate toDate,
                                          CellStyle headerStyle) throws SQLException {
        String[] headers = {"Date", "Total Revenue", "Number of Bookings"};
        
        for (int i = 0; i < headers.length; i++) {
            Cell cell = headerRow.createCell(i);
            cell.setCellValue(headers[i]);
            cell.setCellStyle(headerStyle);
        }

        String query = "SELECT DATE(created_at) as date, " +
                      "SUM(total_amount) as daily_revenue, " +
                      "COUNT(*) as bookings " +
                      "FROM reservations " +
                      "WHERE status IN ('confirmed', 'completed') " +
                      "AND check_in_date BETWEEN ? AND ? " +
                      "GROUP BY DATE(created_at) " +
                      "ORDER BY date";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();

            int rowNum = 1;
            while (rs.next()) {
                Row row = sheet.createRow(rowNum++);
                org.apache.poi.ss.usermodel.Cell cell0 = row.createCell(0);
                cell0.setCellValue(rs.getDate("date").toString());
                org.apache.poi.ss.usermodel.Cell cell1 = row.createCell(1);
                cell1.setCellValue(rs.getDouble("daily_revenue"));
                org.apache.poi.ss.usermodel.Cell cell2 = row.createCell(2);
                cell2.setCellValue(rs.getInt("bookings"));
            }
        }
    }

    /**
     * Generate PDF report.
     */
    private void generatePDFReport() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Save PDF Report");
        fileChooser.getExtensionFilters().add(
            new FileChooser.ExtensionFilter("PDF Files", "*.pdf")
        );
        fileChooser.setInitialFileName("report_" + LocalDate.now().format(DateTimeFormatter.ofPattern("yyyyMMdd")) + ".pdf");

        File file = fileChooser.showSaveDialog(null);
        if (file == null) {
            return;
        }

        try (PDDocument document = new PDDocument()) {
            PDPage page = new PDPage();
            document.addPage(page);

            try (PDPageContentStream contentStream = new PDPageContentStream(document, page)) {
                contentStream.beginText();
                contentStream.setFont(new PDType1Font(Standard14Fonts.FontName.HELVETICA_BOLD), 16);
                contentStream.newLineAtOffset(50, 750);
                contentStream.showText("Belmont Hotel - " + reportTypeCombo.getValue());
                contentStream.endText();

                contentStream.beginText();
                contentStream.setFont(new PDType1Font(Standard14Fonts.FontName.HELVETICA), 12);
                contentStream.newLineAtOffset(50, 720);
                contentStream.showText("Date Range: " + dateFromPicker.getValue() + " to " + dateToPicker.getValue());
                contentStream.endText();

                contentStream.beginText();
                contentStream.setFont(new PDType1Font(Standard14Fonts.FontName.HELVETICA), 10);
                contentStream.newLineAtOffset(50, 690);
                contentStream.showText(reportPreview.getText());
                contentStream.endText();
            }

            document.save(file);
            showAlert(Alert.AlertType.INFORMATION, "Success", "PDF report generated successfully!");
        } catch (IOException e) {
            showAlert(Alert.AlertType.ERROR, "Error", "Failed to generate PDF report: " + e.getMessage());
            e.printStackTrace();
        }
    }

    // Additional preview methods for all report types
    private String generateDailyRevenueReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as bookings " +
                      "FROM reservations WHERE status IN ('confirmed', 'completed') " +
                      "AND created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY date";
        return generateDateGroupedPreview(query, fromDate, toDate, "Daily Revenue");
    }

    private String generateWeeklyRevenueReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT YEARWEEK(created_at) as week, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as bookings " +
                      "FROM reservations WHERE status IN ('confirmed', 'completed') " +
                      "AND created_at BETWEEN ? AND ? GROUP BY YEARWEEK(created_at) ORDER BY week";
        return generateGroupedPreview(query, fromDate, toDate, "Weekly Revenue");
    }

    private String generateMonthlyRevenueReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as bookings " +
                      "FROM reservations WHERE status IN ('confirmed', 'completed') " +
                      "AND created_at BETWEEN ? AND ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month";
        return generateGroupedPreview(query, fromDate, toDate, "Monthly Revenue");
    }

    private String generateRevenueByRoomTypePreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT rm.room_type, COALESCE(SUM(r.total_amount), 0) as revenue, COUNT(*) as bookings " +
                      "FROM reservations r JOIN rooms rm ON r.room_id = rm.id " +
                      "WHERE r.status IN ('confirmed', 'completed') AND r.created_at BETWEEN ? AND ? " +
                      "GROUP BY rm.room_type ORDER BY revenue DESC";
        return generateGroupedPreview(query, fromDate, toDate, "Revenue by Room Type");
    }

    private String generateRevenueByPaymentMethodPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT COALESCE(p.payment_method, 'N/A') as method, COALESCE(SUM(p.amount), 0) as revenue, COUNT(*) as payments " +
                      "FROM payments p JOIN reservations r ON p.reservation_id = r.id " +
                      "WHERE p.status = 'paid' AND p.created_at BETWEEN ? AND ? " +
                      "GROUP BY p.payment_method ORDER BY revenue DESC";
        return generateGroupedPreview(query, fromDate, toDate, "Revenue by Payment Method");
    }

    private String generateBookingStatisticsPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT COUNT(*) as total, " +
                      "SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed, " +
                      "SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled, " +
                      "AVG(total_amount) as avg_amount, " +
                      "AVG(DATEDIFF(check_out_date, check_in_date)) as avg_nights " +
                      "FROM reservations WHERE created_at BETWEEN ? AND ?";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                int total = rs.getInt("total");
                int cancelled = rs.getInt("cancelled");
                double cancellationRate = total > 0 ? (cancelled * 100.0 / total) : 0;
                return String.format("Total Bookings: %d\nConfirmed: %d\nCancelled: %d\nCancellation Rate: %.2f%%\n" +
                                   "Average Amount: ₱%.2f\nAverage Nights: %.1f",
                    total, rs.getInt("confirmed"), cancelled, cancellationRate,
                    rs.getDouble("avg_amount"), rs.getDouble("avg_nights"));
            }
        }
        return "No data available";
    }

    private String generateBookingsByStatusPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT status, COUNT(*) as count FROM reservations " +
                      "WHERE created_at BETWEEN ? AND ? GROUP BY status ORDER BY count DESC";
        return generateGroupedPreview(query, fromDate, toDate, "Bookings by Status");
    }

    private String generateBookingsByRoomTypePreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT rm.room_type, COUNT(*) as count FROM reservations r " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "WHERE r.created_at BETWEEN ? AND ? GROUP BY rm.room_type ORDER BY count DESC";
        return generateGroupedPreview(query, fromDate, toDate, "Bookings by Room Type");
    }

    private String generateBookingTrendsPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT DATE(created_at) as date, COUNT(*) as bookings " +
                      "FROM reservations WHERE created_at BETWEEN ? AND ? " +
                      "GROUP BY DATE(created_at) ORDER BY date";
        return generateDateGroupedPreview(query, fromDate, toDate, "Booking Trends");
    }

    private String generateDailyOccupancyReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT DATE(check_in_date) as date, " +
                      "COUNT(DISTINCT r.room_id) as rooms_booked, " +
                      "COUNT(*) as bookings, " +
                      "ROUND(COUNT(DISTINCT r.room_id) * 100.0 / (SELECT COUNT(*) FROM rooms WHERE is_active = 1), 2) as occupancy_rate " +
                      "FROM reservations r WHERE r.status IN ('pending', 'confirmed', 'completed') " +
                      "AND r.check_in_date BETWEEN ? AND ? GROUP BY DATE(check_in_date) ORDER BY date";
        return generateDateGroupedPreview(query, fromDate, toDate, "Daily Occupancy");
    }

    private String generateWeeklyOccupancyReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT YEARWEEK(check_in_date) as week, " +
                      "COUNT(DISTINCT r.room_id) as rooms_booked, " +
                      "COUNT(*) as bookings " +
                      "FROM reservations r WHERE r.status IN ('pending', 'confirmed', 'completed') " +
                      "AND r.check_in_date BETWEEN ? AND ? GROUP BY YEARWEEK(check_in_date) ORDER BY week";
        return generateGroupedPreview(query, fromDate, toDate, "Weekly Occupancy");
    }

    private String generateMonthlyOccupancyReportPreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT DATE_FORMAT(check_in_date, '%Y-%m') as month, " +
                      "COUNT(DISTINCT r.room_id) as rooms_booked, " +
                      "COUNT(*) as bookings " +
                      "FROM reservations r WHERE r.status IN ('pending', 'confirmed', 'completed') " +
                      "AND r.check_in_date BETWEEN ? AND ? GROUP BY DATE_FORMAT(check_in_date, '%Y-%m') ORDER BY month";
        return generateGroupedPreview(query, fromDate, toDate, "Monthly Occupancy");
    }

    private String generateOccupancyByRoomTypePreview(LocalDate fromDate, LocalDate toDate) throws SQLException {
        String query = "SELECT rm.room_type, COUNT(DISTINCT r.room_id) as rooms_booked, COUNT(*) as bookings " +
                      "FROM reservations r JOIN rooms rm ON r.room_id = rm.id " +
                      "WHERE r.status IN ('pending', 'confirmed', 'completed') AND r.check_in_date BETWEEN ? AND ? " +
                      "GROUP BY rm.room_type ORDER BY rooms_booked DESC";
        return generateGroupedPreview(query, fromDate, toDate, "Occupancy by Room Type");
    }

    private String generateDateGroupedPreview(String query, LocalDate fromDate, LocalDate toDate, String title) throws SQLException {
        StringBuilder result = new StringBuilder(title + ":\n");
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();
            int count = 0;
            while (rs.next() && count < 10) {
                result.append(String.format("%s: ₱%.2f (%d bookings)\n",
                    rs.getString(1), rs.getDouble(2), rs.getInt(3)));
                count++;
            }
            if (count == 0) result.append("No data available");
        }
        return result.toString();
    }

    private String generateGroupedPreview(String query, LocalDate fromDate, LocalDate toDate, String title) throws SQLException {
        StringBuilder result = new StringBuilder(title + ":\n");
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setDate(1, java.sql.Date.valueOf(fromDate));
            stmt.setDate(2, java.sql.Date.valueOf(toDate));
            ResultSet rs = stmt.executeQuery();
            int count = 0;
            while (rs.next() && count < 10) {
                if (rs.getMetaData().getColumnCount() >= 3) {
                    result.append(String.format("%s: ₱%.2f (%d)\n",
                        rs.getString(1), rs.getDouble(2), rs.getInt(3)));
                } else {
                    result.append(String.format("%s: %d\n", rs.getString(1), rs.getInt(2)));
                }
                count++;
            }
            if (count == 0) result.append("No data available");
        }
        return result.toString();
    }

    /**
     * Generate CSV report.
     */
    private void generateCSVReport() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Save CSV Report");
        fileChooser.getExtensionFilters().add(
            new FileChooser.ExtensionFilter("CSV Files", "*.csv")
        );
        fileChooser.setInitialFileName("report_" + LocalDate.now().format(DateTimeFormatter.ofPattern("yyyyMMdd")) + ".csv");

        File file = fileChooser.showSaveDialog(null);
        if (file == null) {
            return;
        }

        try (java.io.PrintWriter writer = new java.io.PrintWriter(file, "UTF-8")) {
            String reportType = reportTypeCombo.getValue();
            LocalDate fromDate = dateFromPicker.getValue();
            LocalDate toDate = dateToPicker.getValue();

            if (reportType != null && fromDate != null && toDate != null) {
                generateCSVData(writer, reportType, fromDate, toDate);
            }

            showAlert(Alert.AlertType.INFORMATION, "Success", "CSV report generated successfully!");
        } catch (IOException | SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Error", "Failed to generate CSV report: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Generate CSV data based on report type.
     */
    private void generateCSVData(java.io.PrintWriter writer, String reportType, LocalDate fromDate, LocalDate toDate) throws SQLException {
        // Write header
        writer.println("Report Type: " + reportType);
        writer.println("Date Range: " + fromDate + " to " + toDate);
        writer.println();

        // Generate data based on report type (similar to Excel generation)
        if (reportType.equals("Booking Report")) {
            writer.println("ID,Reservation Number,Guest Name,Hotel,Check-in,Check-out,Status,Amount");
            String query = "SELECT r.id, r.reservation_number, u.name, r.check_in_date, r.check_out_date, r.status, r.total_amount " +
                          "FROM reservations r JOIN users u ON r.user_id = u.id " +
                          "JOIN rooms rm ON r.room_id = rm.id " +
                          "WHERE r.check_in_date BETWEEN ? AND ? ORDER BY r.created_at DESC";
            try (Connection conn = DatabaseConnection.getConnection();
                 PreparedStatement stmt = conn.prepareStatement(query)) {
                stmt.setDate(1, java.sql.Date.valueOf(fromDate));
                stmt.setDate(2, java.sql.Date.valueOf(toDate));
                ResultSet rs = stmt.executeQuery();
                while (rs.next()) {
                    writer.printf("%d,%s,%s,%s,%s,%s,%s,%.2f%n",
                        rs.getInt(1), rs.getString(2), rs.getString(3), "Belmont Hotel",
                        rs.getDate(4), rs.getDate(5), rs.getString(6), rs.getDouble(7));
                }
            }
        }
        // Add other report types as needed
    }

    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

