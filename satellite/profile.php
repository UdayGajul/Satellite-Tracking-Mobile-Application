<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "satellite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id']; 

    $query = "SELECT * FROM register WHERE id = '$user_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(array("status" => "no", "message" => "User not found"));
    }
    
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $json = file_get_contents('php://input');
    $_PUT = json_decode($json, true);

    if (!is_array($_PUT)) {
        echo json_encode(["status" => "no", "message" => "Invalid request"]);
        exit;
    }

    if (!isset($_PUT['id'], $_PUT['name'], $_PUT['email'], $_PUT['password'], $_PUT['mobileNumber'])) {
        echo json_encode(["status" => "no", "message" => "Missing required fields."]);
        exit;
    }
    
    $id = $_PUT['id'];
    $name = $_PUT['name'];
    $email = $_PUT['email'];
    $pass = $_PUT['password'];
    $mobileNumber = $_PUT['mobileNumber'];
    
    $checkEmailStmt = $conn->prepare("SELECT id FROM register WHERE email = ? AND id != ?");
    $checkEmailStmt->bind_param("si", $email, $id);
    $checkEmailStmt->execute();
    $emailResult = $checkEmailStmt->get_result();

    if ($emailResult->num_rows > 0) {
        echo json_encode(["status" => "no", "message" => "Email already exists."]);
        $checkEmailStmt->close();
        $conn->close();
        exit;
    }
    $checkEmailStmt->close();
    
    $checkMobileStmt = $conn->prepare("SELECT id FROM register WHERE mobile_number = ? AND id != ?");
    $checkMobileStmt->bind_param("si", $mobileNumber, $id);
    $checkMobileStmt->execute();
    $mobileResult = $checkMobileStmt->get_result();

    if ($mobileResult->num_rows > 0) {
        echo json_encode(["status" => "no", "message" => "Mobile number already exists."]);
        $checkMobileStmt->close();
        $conn->close();
        exit;
    }
    $checkMobileStmt->close();
    
    $stmt = $conn->prepare("UPDATE register SET name = ?, email = ?, password = ?, mobile_number = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $pass, $mobileNumber, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "yes", "message" => "Account updated successfully."]);
    } else {
        echo json_encode(["status" => "no", "message" => "Error while updating account."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}


?>
