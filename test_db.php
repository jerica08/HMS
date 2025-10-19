<?php
try {
    $db = db_connect();
    echo "Database connection successful";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
