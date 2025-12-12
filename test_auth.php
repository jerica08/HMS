<?php
// Test authentication status
session_start();

echo "=== Authentication Status Check ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Is Logged In: " . (isset($_SESSION['isLoggedIn']) ? "YES" : "NO") . "\n";
echo "User Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "Staff ID: " . ($_SESSION['staff_id'] ?? 'NOT SET') . "\n";

// Check if user has required role
$requiredRoles = ['admin', 'it_staff'];
$userRole = $_SESSION['role'] ?? '';
$hasRequiredRole = in_array($userRole, $requiredRoles);

echo "Has Required Role: " . ($hasRequiredRole ? "YES" : "NO") . "\n";

if (!$hasRequiredRole) {
    echo "\n=== ISSUE ===\n";
    echo "You need to be logged in as 'admin' or 'it_staff' to create departments.\n";
    echo "Current role: '$userRole'\n";
    echo "Please log in with the correct account.\n";
} else {
    echo "\n=== Authentication OK ===\n";
    echo "You should be able to create departments.\n";
}

echo "\n=== Session Contents ===\n";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
