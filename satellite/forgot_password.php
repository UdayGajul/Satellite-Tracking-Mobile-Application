<?php
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "satellite";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (json_last_error() != JSON_ERROR_NONE) {
        echo json_encode([
            "status" => "error",
            "message" => "JSON decode error: " . json_last_error_msg()
        ]);
        exit;
    }

    $email = trim($data->email);  
    $password = $data->password;
    $confirmPassword = $data->confirmPassword;

    if ($password !== $confirmPassword) {
        echo json_encode([
            "status" => "error",
            "message" => "Passwords do not match."
        ]);
        exit;
    }

    $sql = "SELECT * FROM register WHERE email = ?";  
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode([
            "status" => "error",
            "message" => "User with this email does not exist."
        ]);
        exit;
    }

    $update_sql = "UPDATE register SET password = ? WHERE email = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $password, $email);

    if ($update_stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Your password has been successfully reset. Ready to go!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error updating password. Please try again."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Use POST."
    ]);
}

$conn->close();
?>
