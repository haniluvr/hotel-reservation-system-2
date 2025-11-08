# Admin Panel - Recent Changes

## Fixed Issues

### 1. Navigation Fixed
- Updated `DashboardController.loadModule()` to use `DashboardController.class.getResource()` instead of `getClass().getResource()` for proper resource loading
- Added error handling with alert dialogs when modules fail to load
- All navigation buttons (Bookings, Hotels, Rooms, Customers, Payments, Reports, Logout) should now work correctly

### 2. Logo/Icon Added
- Added logo image support to login page (80x80 pixels)
- Added logo image above "Belmont Hotel" header in sidebar (60x60 pixels)
- Added application icon support (window icon)
- **Action Required:** Place the actual logo image file at `admin-panel/src/main/resources/images/logo.png`
  - See `admin-panel/src/main/resources/images/README.md` for details

### 3. "Users" Changed to "Customers"
- Updated sidebar button text from "Users" to "Customers"
- Updated page title from "Users Management" to "Customers Management"
- Updated role filter options (Admin/Customer instead of Admin/User)
- Updated search field placeholder text
- Updated role assignment logic in UsersController

### 4. Lint Errors Fixed
- Added missing imports: `GridPane`, `Insets` in HotelsController and RoomsController
- Fixed Cell type ambiguity in ReportsController by using fully qualified names
- Fixed PDFBox font usage for PDFBox 3.0.0 (using `Standard14Fonts.FontName`)
- Removed unused variable warning in ReportsController

## Files Modified

1. `DashboardController.java` - Fixed navigation and added error handling
2. `Main.java` - Added application icon support
3. `login.fxml` - Added logo ImageView
4. `dashboard.fxml` - Added logo ImageView above header, changed "Users" to "Customers"
5. `users.fxml` - Changed title and search placeholder
6. `UsersController.java` - Updated role labels and logic
7. `HotelsController.java` - Added missing imports
8. `RoomsController.java` - Added missing imports
9. `ReportsController.java` - Fixed Cell ambiguity and PDFBox font usage

## Next Steps

1. **Add Logo Image:**
   - Place the Belmont Hotel logo (circular emblem) at:
     `admin-panel/src/main/resources/images/logo.png`
   - Recommended sizes: 80x80 for login, 60x60 for sidebar, 32x32/64x64/128x128 for app icon
   - Format: PNG with transparency support

2. **Test Navigation:**
   - Verify all sidebar buttons work correctly
   - Test logout functionality
   - Ensure all modules load without errors

3. **Rebuild and Run:**
   ```bash
   cd admin-panel
   mvn clean compile
   mvn javafx:run
   ```

## Notes

- The logo image path uses JavaFX resource loading (`@/images/logo.png`)
- If the logo image is missing, the ImageView will simply not display (no error)
- The application icon will only show if the logo.png file exists in the resources folder

