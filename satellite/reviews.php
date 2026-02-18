<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "satellite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents('php://input');
    $_POST = json_decode($json, true);

    if (!isset($_POST['user_id'], $_POST['email'], $_POST['stars'], $_POST['feedback'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
        exit;
    }

    $user_id = intval($_POST['user_id']);
    $email = $_POST['email'];
    $stars = intval($_POST['stars']);
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, email, stars, feedback) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $email, $stars, $feedback);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Review added successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add review."]);
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $json = file_get_contents('php://input');
    $_PUT = json_decode($json, true);

    if (!isset($_PUT['id'], $_PUT['stars'], $_PUT['feedback'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
        exit;
    }

    $id = intval($_PUT['id']);
    $stars = intval($_PUT['stars']);
    $feedback = $_PUT['feedback'];

    $stmt = $conn->prepare("UPDATE reviews SET stars = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("isi", $stars, $feedback, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Review updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update review."]);
    }

    $stmt->close();
}

// This API will be used on admin side also without passing user_id in url to display all reviews with users details.
// This API will be used on user side also by passing user_id in url to  display specific users reviews.

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $stmt = $conn->prepare("
            SELECT r.id, r.user_id, l.name, r.email, r.stars, r.feedback, r.created_at, r.updated_at
            FROM reviews r
            JOIN register l ON r.user_id = l.id
            WHERE r.user_id = ?
            ORDER BY r.updated_at DESC
        ");
        $stmt->bind_param("i", $user_id);
    } else {        
        $stmt = $conn->prepare("
            SELECT r.id, r.user_id, l.name, r.email, r.stars, r.feedback, r.created_at, r.updated_at
            FROM reviews r
            JOIN register l ON r.user_id = l.id
            ORDER BY r.updated_at DESC
        ");
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $reviews = [];
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
            echo json_encode(["status" => "success", "data" => $reviews]);
        } else {
            echo json_encode(["status" => "error", "message" => "No reviews found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Query execution failed."]);
    }

    $stmt->close();
}

$conn->close();
?>

