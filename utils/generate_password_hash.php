<?php
/**
 * Password Hash Generator Utility
 * Use this script to generate bcrypt password hashes for new users
 * 
 * Usage: php utils/generate_password_hash.php
 * Or access via browser: http://localhost/Student-Result-Management-System-1/utils/generate_password_hash.php
 */

// Prevent direct access in production (optional - remove this check if you want to use it via browser)
// Uncomment the next two lines if you only want command-line access:
// if (php_sapi_name() !== 'cli') {
//     die('This script can only be run from command line.');
// }

$password = $argv[1] ?? 'password'; // Get password from command line argument or use default

echo "===========================================\n";
echo "Password Hash Generator\n";
echo "===========================================\n\n";

echo "Password: $password\n";
echo "Hash: " . password_hash($password, PASSWORD_BCRYPT) . "\n\n";

echo "SQL INSERT statement:\n";
echo "INSERT INTO users (username, password, role, full_name, email) VALUES\n";
echo "('username', '" . password_hash($password, PASSWORD_BCRYPT) . "', 'role', 'Full Name', 'email@example.com');\n\n";

// Verify the hash
$hash = password_hash($password, PASSWORD_BCRYPT);
$verify = password_verify($password, $hash);
echo "Verification: " . ($verify ? "✓ SUCCESS" : "✗ FAILED") . "\n";
?>

