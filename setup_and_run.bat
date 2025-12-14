@echo off
echo ============================================
echo Student Result Management System Setup
echo ============================================
echo.

REM Check if XAMPP is installed
set XAMPP_PATH=C:\xampp
set WAMP_PATH=C:\wamp64

echo [1/4] Checking for XAMPP/WAMP...
if exist "%XAMPP_PATH%\mysql\bin\mysql.exe" (
    set MYSQL_PATH=%XAMPP_PATH%\mysql\bin
    set PHP_PATH=%XAMPP_PATH%\php
    set DB_USER=root
    set DB_PASS=
    echo Found XAMPP at %XAMPP_PATH%
    goto :check_services
)

if exist "%WAMP_PATH%\bin\mysql\mysql8.0.31\bin\mysql.exe" (
    set MYSQL_PATH=%WAMP_PATH%\bin\mysql\mysql8.0.31\bin
    set PHP_PATH=%WAMP_PATH%\bin\php\php8.2.0
    set DB_USER=root
    set DB_PASS=
    echo Found WAMP at %WAMP_PATH%
    goto :check_services
)

echo XAMPP/WAMP not found in default locations.
echo Please start MySQL manually and ensure PHP is in PATH.
goto :setup_database

:check_services
echo.
echo [2/4] Checking MySQL service...
sc query MySQL | find "RUNNING" >nul
if errorlevel 1 (
    echo MySQL is not running. Attempting to start...
    net start MySQL 2>nul
    if errorlevel 1 (
        echo Failed to start MySQL automatically.
        echo Please start MySQL manually from XAMPP/WAMP control panel.
        pause
        exit /b 1
    )
    echo MySQL started successfully.
) else (
    echo MySQL is running.
)

:setup_database
echo.
echo [3/4] Setting up database...
echo This will create the database and import schema.
echo.

REM Check if database exists
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% -e "USE student_result_system" 2>nul
if errorlevel 1 (
    echo Creating database...
    "%MYSQL_PATH%\mysql.exe" -u %DB_USER% -e "CREATE DATABASE IF NOT EXISTS student_result_system CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
    if errorlevel 1 (
        echo Error creating database. Please check MySQL credentials.
        pause
        exit /b 1
    )
    echo Database created.
) else (
    echo Database already exists.
)

echo Importing schema...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% student_result_system < database\schema.sql
if errorlevel 1 (
    echo Error importing schema. Please check MySQL connection.
    pause
    exit /b 1
)
echo Schema imported successfully.

echo.
echo [4/4] Configuration check...
echo Please verify database credentials in config\database.php
echo Current settings:
type config\database.php | findstr "DB_"
echo.

echo ============================================
echo Setup Complete!
echo ============================================
echo.
echo To run the project:
echo 1. Make sure Apache is running (XAMPP/WAMP control panel)
echo 2. Open browser: http://localhost/Student-Result-Management-System-1/
echo 3. Login with: admin / password
echo.
pause

