@echo off
title Student Result Management System - Server
color 0A
echo ============================================
echo   Student Result Management System
echo   Starting PHP Development Server
echo ============================================
echo.
echo [INFO] Checking prerequisites...
echo.

REM Check if PHP is available
php -v >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP is not installed or not in PATH
    echo Please install PHP or add it to your system PATH
    pause
    exit /b 1
)

REM Get current directory
cd /d "%~dp0"

REM Check if database config exists
if not exist "config\database.php" (
    echo [ERROR] Database configuration file not found!
    echo Please ensure config\database.php exists
    pause
    exit /b 1
)

echo [OK] PHP is available
echo [OK] Configuration files found
echo.

REM Display configuration info
echo ============================================
echo   Server Information
echo ============================================
echo Server URL: http://localhost:8000
echo Admin Login: admin / password
echo Staff Login: staff1 / password
echo Student Login: student1 / password
echo.
echo Press Ctrl+C to stop the server
echo ============================================
echo.

REM Start PHP built-in server
echo [STARTING] PHP Development Server...
echo.
php -S localhost:8000

pause

