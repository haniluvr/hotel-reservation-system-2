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
    private TextArea reportPreview;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        // Set report type options
        reportTypeCombo.getItems().addAll(
            "Booking Report",
            "Revenue Report",
            "Occupancy Report",
            "User Activity Report"
        );
        reportTypeCombo.setValue("Booking Report");

        // Set default date range (last 30 days)
        dateToPicker.setValue(LocalDate.now());
        dateFromPicker.setValue(LocalDate.now().minusDays(30));

        // Set button actions
        generateExcelBtn.setOnAction(e -> generateExcelReport());
        generatePDFBtn.setOnAction(e -> generatePDFReport());

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
            switch (reportType) {
                case "Booking Report":
                    preview.append(generateBookingReportPreview(fromDate, toDate));
                    break;
                case "Revenue Report":
                    preview.append(generateRevenueReportPreview(fromDate, toDate));
                    break;
                case "Occupancy Report":
                    preview.append(generateOccupancyReportPreview(fromDate, toDate));
                    break;
                case "User Activity Report":
                    preview.append(generateUserActivityReportPreview(fromDate, toDate));
                    break;
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

        String query = "SELECT r.id, r.reservation_number, u.name as guest_name, h.name as hotel_name, " +
                      "r.check_in_date, r.check_out_date, r.status, r.total_amount " +
                      "FROM reservations r " +
                      "JOIN users u ON r.user_id = u.id " +
                      "JOIN rooms rm ON r.room_id = rm.id " +
                      "JOIN hotels h ON rm.hotel_id = h.id " +
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
                cell3.setCellValue(rs.getString("hotel_name"));
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

    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}

