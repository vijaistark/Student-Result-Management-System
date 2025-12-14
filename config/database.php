<?php
/**
 * Database Configuration File
 * Handles MySQL database connection
 */

// Database configuration
// IMPORTANT: Update these values according to your MySQL setup
define('DB_HOST', 'localhost');
define('DB_USER', 'root');

// Password options:
// - If XAMPP/WAMP with no password: '' (empty string)
// - If you set a password: 'your_password'
// - Common defaults: '' (empty) or 'root'
// 
// TROUBLESHOOTING: If you get "Access denied" error, run test_db_connection.php
// to find the correct password, then update this value.
define('DB_PASS', '');  // CHANGE THIS: Try '', 'root', or your actual password

define('DB_NAME', 'student_result_system');

/**
 * Get database connection
 * @return mysqli Connection object
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Close database connection
 * @param mysqli $conn Connection object
 */
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>

