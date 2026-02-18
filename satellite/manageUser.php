<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "satellite";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the request data
$data = json_decode(file_get_contents("php://input"), true);

// Validate incoming data
if (!isset($data['id']) || !isset($data['action'])) {
    echo json_encode(["message" => "User ID and action are required"]);
    exit;
}

// Get the user ID and action
$id = $data['id'];
$action = $data['action'];

// If the action is 'view', fetch the user data
if ($action == 'view') {
    $sql = "SELECT * FROM register WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode($user); // Return user data
    } else {
        echo json_encode(["message" => "User not found"]);
    }
}

// If the action is 'delete', delete the user
elseif ($action == 'delete') {
    $sql = "DELETE FROM register WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "User deleted successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
}

// If the action is not recognized, return an error
else {
    echo json_encode(["message" => "Invalid action"]);
}

$conn->close();
?>
