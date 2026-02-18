<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "satellite";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// GET request - Fetch satellites
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['satellite_id']) && !empty($_GET['satellite_id'])) {
        $satellite_id = intval($_GET['satellite_id']);
        $stmt = $conn->prepare("SELECT * FROM satellite_data WHERE satellite_id = ?");
        $stmt->bind_param("i", $satellite_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM satellite_data");
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $satellites = [];
            while ($row = $result->fetch_assoc()) {
                $satellites[] = $row;
            }
            echo json_encode(["status" => "success", "data" => $satellites]);
        } else {
            echo json_encode(["status" => "error", "message" => "No satellite data found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Query execution failed."]);
    }
    $stmt->close();
}

// POST request - Add new satellite
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (
        isset($data['satellite_id']) && isset($data['satellite_name']) && 
        isset($data['nickname']) && isset($data['description']) && isset($data['country_org_un_registry']) && 
        isset($data['country_operator_owner']) && isset($data['operator_owner']) && 
        isset($data['users']) && isset($data['purpose']) && isset($data['orbit_type']) && 
        isset($data['launch_date']) && isset($data['launch_year']) && 
        isset($data['expected_lifetime_yrs']) && isset($data['contractor']) && 
        isset($data['contractor_country']) && isset($data['launch_site']) && 
        isset($data['launch_vehicle']) && isset($data['active'])
    ) {
        // Check for existing satellite ID
        $checkStmt = $conn->prepare("SELECT satellite_id FROM satellite_data WHERE satellite_id = ?");
        $checkStmt->bind_param("i", $data['satellite_id']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Satellite ID already exists."]);
            $checkStmt->close();
            exit;
        }
        $checkStmt->close();

        // Check for existing satellite name
        $checkStmt = $conn->prepare("SELECT satellite_id FROM satellite_data WHERE satellite_name = ?");
        $checkStmt->bind_param("s", $data['satellite_name']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Satellite name already exists."]);
            $checkStmt->close();
            exit;
        }
        $checkStmt->close();

        // Insert new satellite
        $stmt = $conn->prepare("INSERT INTO satellite_data (
            satellite_id, satellite_name, nickname, description, country_org_un_registry,
            country_operator_owner, operator_owner, users, purpose, orbit_type,
            launch_date, launch_year, expected_lifetime_yrs, contractor,
            contractor_country, launch_site, launch_vehicle, active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("isssssssssssisssss",
            $data['satellite_id'], $data['satellite_name'], $data['nickname'], $data['description'],
            $data['country_org_un_registry'], $data['country_operator_owner'],
            $data['operator_owner'], $data['users'], $data['purpose'],
            $data['orbit_type'], $data['launch_date'], $data['launch_year'],
            $data['expected_lifetime_yrs'], $data['contractor'],
            $data['contractor_country'], $data['launch_site'],
            $data['launch_vehicle'], $data['active']
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Satellite added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error adding satellite: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    }
}

// PUT request - Update satellite
if ($_SERVER["REQUEST_METHOD"] == "PUT") {    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Handle simple active status toggle
    if (isset($data['satellite_id']) && isset($data['active']) && count($data) == 2) {
        $satellite_id = $data['satellite_id'];
        $active = $data['active'];

        // Validate active value
        if ($active !== "0" && $active !== "1") {
            echo json_encode(["status" => "error", "message" => "Invalid active value. Must be '0' or '1'"]);
            exit;
        }

        // Check if satellite exists
        $checkStmt = $conn->prepare("SELECT * FROM satellite_data WHERE satellite_id = ?");
        $checkStmt->bind_param("i", $satellite_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Update only the active status
            $updateStmt = $conn->prepare("UPDATE satellite_data SET active = ? WHERE satellite_id = ?");
            $updateStmt->bind_param("si", $active, $satellite_id);

            if ($updateStmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Satellite status updated successfully.",
                    "data" => ["satellite_id" => $satellite_id, "active" => $active]
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating satellite status."]);
            }
            $updateStmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Satellite not found."]);
        }
        $checkStmt->close();
    }
    // Handle full satellite update
    else if (isset($data['satellite_id'])) {
        $satellite_id = $data['satellite_id'];

        // Check if satellite exists
        $checkStmt = $conn->prepare("SELECT * FROM satellite_data WHERE satellite_id = ?");
        $checkStmt->bind_param("i", $satellite_id);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            // Check for name duplication
            if (isset($data['satellite_name'])) {
                $nameCheckStmt = $conn->prepare("SELECT satellite_id FROM satellite_data WHERE satellite_name = ? AND satellite_id != ?");
                $nameCheckStmt->bind_param("si", $data['satellite_name'], $satellite_id);
                $nameCheckStmt->execute();
                if ($nameCheckStmt->get_result()->num_rows > 0) {
                    echo json_encode(["status" => "error", "message" => "Satellite name already exists."]);
                    $nameCheckStmt->close();
                    $checkStmt->close();
                    exit;
                }
                $nameCheckStmt->close();
            }

            // Prepare UPDATE query dynamically based on provided fields
            $updateFields = [];
            $types = "";
            $values = [];

            $allowedFields = [
                'satellite_name' => 's', 'nickname' => 's', 'description' => 's',
                'country_org_un_registry' => 's', 'country_operator_owner' => 's',
                'operator_owner' => 's', 'users' => 's', 'purpose' => 's',
                'orbit_type' => 's', 'launch_date' => 's', 'launch_year' => 's',
                'expected_lifetime_yrs' => 'i', 'contractor' => 's',
                'contractor_country' => 's', 'launch_site' => 's',
                'launch_vehicle' => 's', 'active' => 's'
            ];

            foreach ($allowedFields as $field => $type) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $types .= $type;
                    $values[] = $data[$field];
                }
            }

            if (count($updateFields) > 0) {
                $types .= "i"; // for the WHERE satellite_id = ?
                $values[] = $satellite_id;

                $sql = "UPDATE satellite_data SET " . implode(", ", $updateFields) . " WHERE satellite_id = ?";
                $updateStmt = $conn->prepare($sql);
                $updateStmt->bind_param($types, ...$values);

                if ($updateStmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Satellite updated successfully."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error updating satellite: " . $updateStmt->error]);
                }
                $updateStmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "No fields to update."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Satellite not found."]);
        }
        $checkStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Missing satellite_id."]);
    }
}

// DELETE request - Delete satellite
if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['satellite_id'])) {
        $satellite_id = $data['satellite_id'];

        // Check if satellite exists
        $checkStmt = $conn->prepare("SELECT satellite_id FROM satellite_data WHERE satellite_id = ?");
        $checkStmt->bind_param("i", $satellite_id);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            $deleteStmt = $conn->prepare("DELETE FROM satellite_data WHERE satellite_id = ?");
            $deleteStmt->bind_param("i", $satellite_id);

            if ($deleteStmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Satellite deleted successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error deleting satellite: " . $deleteStmt->error]);
            }
            $deleteStmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Satellite not found."]);
        }
        $checkStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Missing satellite_id."]);
    }
}

$conn->close();
?>