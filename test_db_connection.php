<?php
/**
 * Database Connection Test Script
 * This script will help you find the correct MySQL password
 * DELETE THIS FILE after you've configured your database successfully!
 */

echo "<h2>MySQL Connection Test</h2>";
echo "<p>Trying different password configurations...</p><hr>";

$passwords_to_try = ['', 'root', 'password', 'admin'];

$host = 'localhost';
$user = 'root';
$db_name = 'student_result_system';

foreach ($passwords_to_try as $password) {
    $password_display = $password === '' ? '(empty/no password)' : $password;
    echo "<p><strong>Trying password: {$password_display}</strong></p>";
    
    $conn = @new mysqli($host, $user, $password, $db_name);
    
    if ($conn->connect_error) {
        echo "❌ Failed: " . $conn->connect_error . "<br>";
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>SUCCESS!</strong> Connection works with password: <code>{$password_display}</code><br>";
        echo "Update <code>config/database.php</code> with:<br>";
        echo "<pre>define('DB_PASS', " . var_export($password, true) . ");</pre>";
        
        // Test if database exists
        if ($conn->select_db($db_name)) {
            echo "✅ Database <strong>{$db_name}</strong> exists!<br>";
            
            // Check if tables exist
            $result = $conn->query("SHOW TABLES");
            if ($result && $result->num_rows > 0) {
                echo "✅ Database has " . $result->num_rows . " table(s).<br>";
            } else {
                echo "⚠️ Database exists but has no tables. Please import <code>database/schema.sql</code><br>";
            }
        } else {
            echo "⚠️ Database <strong>{$db_name}</strong> does not exist. Please create it and import schema.<br>";
        }
        
        echo "</div>";
        $conn->close();
        break;
    }
    echo "<hr>";
}

echo "<br><br>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Note the password that worked above</li>";
echo "<li>Update <code>config/database.php</code> with the correct password</li>";
echo "<li>If database doesn't exist, create it in phpMyAdmin and import <code>database/schema.sql</code></li>";
echo "<li><strong>DELETE THIS FILE</strong> (<code>test_db_connection.php</code>) after configuration</li>";
echo "</ol>";

echo "<br><p style='color: red;'><strong>Security Note:</strong> Please delete this file after use!</p>";
?>

