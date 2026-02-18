<?php
header('Content-Type: application/json');

$host = 'localhost';
$username = 'root'; 
$password = ''; 
$dbname = 'satellite';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if (isset($_GET['userId'])) {
    $id = $conn->real_escape_string($_GET['userId']); 

    $sql = "SELECT name FROM register WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'name' => $row['name']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User ID not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request. userId is required.']);
}

// Close connection
$conn->close();
?>
