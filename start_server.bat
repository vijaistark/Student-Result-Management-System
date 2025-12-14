@echo off
echo ============================================
echo Starting PHP Development Server
echo ============================================
echo.
echo This will start a PHP built-in server on port 8000
echo.
echo Access the application at:
echo http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

REM Get the current directory
cd /d "%~dp0"

REM Start PHP server
php -S localhost:8000

