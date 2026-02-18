<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['email']) && isset($data['password'])) {
        $email = $data['email'];
        $password = $data['password'];

        // Enable error logging
        error_log("Login attempt - Email: " . $email);

        $conn = new mysqli('localhost', 'root', '', 'satellite');
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
        }

        // Check admin table first
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            error_log("Admin found - ID: " . $admin['id']);
            
            if ($password === $admin['password']) {
                error_log("Admin login successful");
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Admin login successful',
                    'userId' => $admin['id'],
                    'userType' => 'admin'
                ]);
                exit();
            } else {
                error_log("Admin password mismatch");
                echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
                exit();
            }
        }

        // If no admin found, check register table
        $stmt = $conn->prepare("SELECT id, password FROM register WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            error_log("Regular user found - ID: " . $user['id']);
            
            if ($password === $user['password']) {
                error_log("User login successful");
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User login successful',
                    'userId' => $user['id'],
                    'userType' => 'register'
                ]);
            } else {
                error_log("User password mismatch");
                echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            error_log("No user found with email: " . $email);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        }

        $stmt->close();
        $conn->close();
    } else {
        error_log("Missing email or password in request");
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>