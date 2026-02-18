<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Test database connection
$conn = new mysqli('localhost', 'root', '', 'satellite');

if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
} else {
    $conn->close();
    echo json_encode([
        'status' => 'success',
        'message' => 'Connection established successfully'
    ]);
}
?>