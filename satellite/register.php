<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "satellite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
    exit;
}

$name = isset($data['name']) ? $conn->real_escape_string($data['name']) : null;
$email = isset($data['email']) ? $conn->real_escape_string($data['email']) : null;
$password = isset($data['password']) ? $conn->real_escape_string($data['password']) : null;
$mobile_number = isset($data['mobile_number']) ? $conn->real_escape_string($data['mobile_number']) : null;

if (!$name || !$email || !$password || !$mobile_number) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

$checkEmail = "SELECT id FROM register WHERE email = '$email'";
$emailResult = $conn->query($checkEmail);
if ($emailResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already exists."]);
    exit;
}

$checkMobile = "SELECT id FROM register WHERE mobile_number = '$mobile_number'";
$mobileResult = $conn->query($checkMobile);
if ($mobileResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Mobile number already exists."]);
    exit;
}

$sql = "INSERT INTO register (name, email, password, mobile_number) 
        VALUES ('$name', '$email', '$password', '$mobile_number')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Registration successful!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$conn->close();
?>