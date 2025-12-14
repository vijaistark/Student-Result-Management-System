<?php
/**
 * Main Configuration File
 * Sets up session, constants, and includes
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Define base URL (adjust based on your setup)
define('BASE_URL', 'http://localhost/Student-Result-Management-System-1/');

// Define base path
define('BASE_PATH', __DIR__ . '/../');

// Include helper functions
require_once __DIR__ . '/../includes/functions.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Check if user has specific role
 * @param string $role Role to check
 * @return bool
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

/**
 * Require specific role - redirect if user doesn't have required role
 * @param string $role Required role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

/**
 * Redirect based on user role
 */
function redirectByRole() {
    if (isLoggedIn()) {
        $role = $_SESSION['role'];
        switch ($role) {
            case 'admin':
                header('Location: ' . BASE_URL . 'admin/dashboard.php');
                break;
            case 'staff':
                header('Location: ' . BASE_URL . 'staff/dashboard.php');
                break;
            case 'student':
                header('Location: ' . BASE_URL . 'student/dashboard.php');
                break;
            default:
                header('Location: ' . BASE_URL . 'index.php');
        }
        exit();
    }
}
?>

