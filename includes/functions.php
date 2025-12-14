<?php
/**
 * Helper Functions
 * Common utility functions used across the application
 */

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Calculate pass/fail status
 * @param float $marks Marks obtained
 * @param float $totalMarks Total marks
 * @param float $passPercentage Pass percentage (default 40)
 * @return string Pass or Fail
 */
function getPassFailStatus($marks, $totalMarks, $passPercentage = 40) {
    $percentage = ($marks / $totalMarks) * 100;
    return $percentage >= $passPercentage ? 'Pass' : 'Fail';
}

/**
 * Calculate percentage
 * @param float $marks Marks obtained
 * @param float $totalMarks Total marks
 * @return float Percentage
 */
function calculatePercentage($marks, $totalMarks) {
    if ($totalMarks == 0) return 0;
    return round(($marks / $totalMarks) * 100, 2);
}

/**
 * Get grade based on percentage
 * @param float $percentage Percentage
 * @return string Grade
 */
function getGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B';
    if ($percentage >= 60) return 'C';
    if ($percentage >= 50) return 'D';
    if ($percentage >= 40) return 'E';
    return 'F';
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

/**
 * Get query status badge HTML
 * @param string $status Status
 * @return string HTML badge
 */
function getQueryStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'reviewed' => '<span class="badge badge-info">Reviewed</span>',
        'resolved' => '<span class="badge badge-success">Resolved</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Show flash message
 * @param string $type Message type (success, error, info)
 * @param string $message Message text
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message array or null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = 'alert-' . ($flash['type'] === 'error' ? 'danger' : $flash['type']);
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    }
}
?>

