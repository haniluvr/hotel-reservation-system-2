# Belmont Hotel Admin Panel - Setup Guide

## Prerequisites

Before setting up the JavaFX Admin Panel, ensure you have the following installed:

1. **Java Development Kit (JDK) 17 or higher**
   - Download from: https://adoptium.net/ or https://www.oracle.com/java/technologies/downloads/
   - Verify installation: `java -version` and `javac -version`

2. **Apache Maven 3.6 or higher**
   - Download from: https://maven.apache.org/download.cgi
   - Verify installation: `mvn -version`

3. **MySQL Database**
   - Ensure MySQL is running and the `hotel_db` database exists
   - The database should be populated with the Laravel application's migrations and seeders

4. **JavaFX SDK (Optional)**
   - JavaFX 21 is included as a Maven dependency, but you may need to install it separately for Scene Builder
   - Download from: https://openjfx.io/

## Installation Steps

### 1. Navigate to Admin Panel Directory

```bash
cd admin-panel
```

### 2. Configure Database Connection

Edit the configuration file:
- File: `admin-panel/src/main/resources/config.properties`

Update the following values to match your MySQL setup:

```properties
# Database Configuration
db.host=127.0.0.1
db.port=3306
db.database=hotel_db
db.username=root
db.password=your_password_here
```

**Note:** If your MySQL password is empty, leave `db.password=` blank.

### 3. Build the Project

From the `admin-panel` directory, run:

```bash
mvn clean compile
```

This will download all required dependencies (JavaFX, MySQL JDBC driver, Apache POI, PDFBox, HikariCP, jBCrypt) and compile the project.

### 4. Run the Application

#### Option A: Using Maven (Recommended)

```bash
mvn javafx:run
```

#### Option B: Using Java directly

First, build a JAR file:

```bash
mvn clean package
```

Then run:

```bash
java --module-path /path/to/javafx/lib --add-modules javafx.controls,javafx.fxml -cp target/admin-panel-1.0.0.jar:target/lib/* com.belmonthotel.admin.Main
```

#### Option C: Using IDE (IntelliJ IDEA / Eclipse / NetBeans)

1. Import the project as a Maven project
2. Ensure JavaFX SDK is configured in your IDE
3. Run the `Main.java` file from `src/main/java/com/belmonthotel/admin/Main.java`

## Default Login Credentials

The admin panel uses the same user database as the Laravel application. To log in:

- **Email:** `admin@belmonthotel.com`
- **Password:** `password` (default from Laravel seeder)

**Note:** If you haven't run the Laravel seeders, create an admin user in the database or use any existing user account. The system checks if the email contains "admin" to determine admin privileges.

## Application Features

### Dashboard
- Overview of key metrics (total bookings, revenue, occupancy, etc.)
- Recent bookings summary
- Quick access to all modules

### Bookings Management
- View all reservations in a table
- Filter by status, date range, or search terms
- Confirm pending bookings
- Cancel bookings with reason
- View detailed booking information

### Hotels Management
- View all hotels
- Add new hotels
- Edit hotel information
- Toggle hotel active/inactive status
- Delete hotels

### Rooms Management
- View all rooms grouped by hotel
- Filter by hotel
- Add new rooms
- Edit room details (price, quantity, max guests)
- Delete rooms
- Track available quantity vs total quantity

### Users Management
- View all registered users
- Filter by role (Admin/User)
- Search by name or email
- View user details and booking history

### Payments Tracking
- View all payment transactions
- Filter by status (pending, paid, failed, etc.)
- Search by reservation number or Xendit invoice ID
- View detailed payment information

### Reports Generation
- Generate booking reports
- Generate revenue reports
- Generate occupancy reports
- Generate user activity reports
- Export to Excel (.xlsx) format
- Export to PDF format
- Customizable date ranges

## Troubleshooting

### Issue: "Failed to initialize database connection"

**Solution:**
1. Verify MySQL is running: `mysql -u root -p`
2. Check database exists: `SHOW DATABASES;`
3. Verify credentials in `config.properties`
4. Ensure the `hotel_db` database is accessible

### Issue: "JavaFX runtime components are missing"

**Solution:**
1. Ensure JavaFX dependencies are downloaded: `mvn dependency:resolve`
2. For Java 11+, you may need to add JavaFX modules manually
3. Use the Maven JavaFX plugin (already configured in `pom.xml`)

### Issue: "Password verification fails"

**Solution:**
1. The application uses jBCrypt to verify Laravel's bcrypt hashes
2. If password verification fails, try resetting the password in the database
3. Or create a new user with a known password hash

### Issue: "Module not found" errors

**Solution:**
1. Clean and rebuild: `mvn clean install`
2. Ensure all dependencies are downloaded
3. Check that Java 17+ is being used

### Issue: Application window doesn't appear

**Solution:**
1. Check console for error messages
2. Verify FXML files are in `src/main/resources/fxml/`
3. Ensure CSS file is in `src/main/resources/css/`
4. Check that all resource paths are correct

## Project Structure

```
admin-panel/
├── pom.xml                          # Maven configuration
├── src/
│   ├── main/
│   │   ├── java/
│   │   │   └── com/belmonthotel/admin/
│   │   │       ├── Main.java        # Application entry point
│   │   │       ├── controllers/     # FXML controllers
│   │   │       ├── models/          # Data models
│   │   │       └── utils/           # Utility classes
│   │   └── resources/
│   │       ├── fxml/                # FXML layout files
│   │       ├── css/                 # Stylesheet
│   │       └── config.properties   # Database configuration
│   └── test/                        # Test files (if any)
└── target/                          # Build output (generated)
```

## Database Schema Requirements

The admin panel expects the following tables (created by Laravel migrations):

- `users` - User accounts
- `hotels` - Hotel information
- `rooms` - Room details
- `reservations` - Booking records
- `payments` - Payment transactions
- `promo_codes` - Promotional codes

Ensure all migrations have been run in the Laravel application before using the admin panel.

## Security Notes

1. **Password Storage:** The application verifies passwords using jBCrypt, matching Laravel's bcrypt implementation.

2. **Database Credentials:** Never commit `config.properties` with real credentials to version control. Use environment-specific configuration files.

3. **Admin Access:** Currently, admin access is determined by email containing "admin". Consider adding an `is_admin` column to the users table for better security.

4. **Connection Pooling:** The application uses HikariCP for efficient database connection management.

## Additional Resources

- JavaFX Documentation: https://openjfx.io/
- Maven Documentation: https://maven.apache.org/guides/
- MySQL JDBC Driver: https://dev.mysql.com/doc/connector-j/
- Apache POI (Excel): https://poi.apache.org/
- Apache PDFBox: https://pdfbox.apache.org/

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the application logs in the console
3. Verify database connectivity and schema
4. Ensure all dependencies are properly installed

## Version Information

- **Application Version:** 1.0.0
- **Java Version Required:** 17+
- **JavaFX Version:** 21
- **MySQL Connector:** 8.0.33
- **Build Tool:** Maven 3.6+

---

**Last Updated:** 2025-01-28

