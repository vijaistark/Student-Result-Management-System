<?php
// Temporary test script — delete after use
require_once __DIR__ . '/config/database.php';

$conn = getDBConnection();
if ($conn) {
    echo "✅ Database connection successful!\n";
    // Show databases for confirmation
    $res = $conn->query("SHOW DATABASES LIKE 'student_result_system';");
    if ($res && $res->num_rows > 0) {
        echo "Database 'student_result_system' exists.\n";
    } else {
        echo "Database 'student_result_system' does NOT exist.\n";
    }
    closeDBConnection($conn);
} else {
    echo "❌ Database connection failed!\n";
}
?>