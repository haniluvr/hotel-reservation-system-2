# How to Add the Belmont Hotel Logo

## Quick Steps

1. **Get your logo image file**
   - The logo should be the circular emblem with 12 petals/leaves
   - Format: PNG file with transparency
   - Recommended size: 200x200 pixels or larger (will be scaled down)

2. **Place the file**
   - Copy your logo image file
   - Rename it to `logo.png`
   - Place it in: `admin-panel/src/main/resources/images/logo.png`
   - The full path should be: `admin-panel/src/main/resources/images/logo.png`

3. **Rebuild and run**
   ```bash
   cd admin-panel
   mvn clean compile
   mvn javafx:run
   ```

## File Structure

```
admin-panel/
└── src/
    └── main/
        └── resources/
            └── images/
                └── logo.png  ← Place your logo here
```

## Image Requirements

- **File name:** Must be exactly `logo.png`
- **Format:** PNG (supports transparency)
- **Size:** Any size (will be automatically scaled)
  - Login page: 80x80 pixels
  - Sidebar: 60x60 pixels
  - App icon: Will use the same image
- **Background:** Transparent or white background recommended

## Verification

After adding the logo and running the application:
- The logo should appear above "Belmont Hotel" text on the login page
- The logo should appear above "Belmont Hotel" header in the sidebar
- The application window icon should show the logo

## Troubleshooting

If the logo doesn't appear:
1. Check the file path is correct: `admin-panel/src/main/resources/images/logo.png`
2. Verify the file is named exactly `logo.png` (case-sensitive)
3. Make sure you ran `mvn clean compile` after adding the file
4. Check the console output for any error messages about logo loading
5. Ensure the file is a valid PNG image

## Note

The application will run fine without the logo - it just won't be displayed. Once you add the logo file to the correct location, it will automatically appear in all the designated places.

