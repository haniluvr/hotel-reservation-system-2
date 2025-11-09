@echo off
REM Quick Start Script for React + Laravel Setup (Windows)
echo 🚀 Starting React + TypeScript + shadcn/ui Setup...
echo.

REM Install NPM dependencies
echo 📦 Installing NPM dependencies...
call npm install

if %ERRORLEVEL% EQU 0 (
    echo ✅ NPM dependencies installed successfully!
    echo.
    
    echo 🎨 Starting development server...
    echo.
    echo Run these commands in separate terminals:
    echo.
    echo Terminal 1 (Vite Dev Server^):
    echo   npm run dev
    echo.
    echo Terminal 2 (Laravel Server^):
    echo   php artisan serve
    echo.
    echo Then visit: http://localhost:8000/react-demo
    echo.
    echo 📚 For more information, see REACT_SETUP.md
) else (
    echo ❌ NPM installation failed!
    echo Please run 'npm install' manually and check for errors.
    exit /b 1
)

pause

