<?php
// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "satellite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "no", "message" => "Database connection failed."]);
    exit;
}

// Handle POST for login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents('php://input');
    $_POST = json_decode($json, true);

    if (!is_array($_POST)) {
        echo json_encode(["status" => "no", "message" => "Invalid request"]);
        exit;
    }

    if (!isset($_POST['email'], $_POST['password'])) {
        echo json_encode(["status" => "no", "message" => "Missing required fields."]);
        exit;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "no", "message" => "Invalid email or password."]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $row = $result->fetch_assoc();

    if ($password === $row['password']) {
        echo json_encode(["status" => "yes", "message" => "Login successful."]);
    } else {
        echo json_encode(["status" => "no", "message" => "Invalid email or password."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Handle PUT for updating password
if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!is_array($data)) {
        echo json_encode(["status" => "no", "message" => "Invalid request"]);
        exit;
    }

    if (!isset($data['email'], $data['newPassword'], $data['confirmPassword'])) {
        echo json_encode(["status" => "no", "message" => "Missing required fields."]);
        exit;
    }

    $email = $data['email'];
    $newPassword = $data['newPassword'];
    $confirmPassword = $data['confirmPassword'];

    if ($newPassword !== $confirmPassword) {
        echo json_encode(["status" => "no", "message" => "Passwords do not match."]);
        exit;
    }

    // Check if email exists
    $checkEmailStmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $result = $checkEmailStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "no", "message" => "Email not found."]);
        $checkEmailStmt->close();
        $conn->close();
        exit;
    }
    $checkEmailStmt->close();
  
    // Update the password
    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $newPassword, $email);

    if ($stmt->execute()) {
        echo json_encode(["status" => "yes", "message" => "Password updated successfully."]);
    } else {
        echo json_encode(["status" => "no", "message" => "Error while updating password."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
$conn->close();
?>
