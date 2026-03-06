<?php
// backend/api/auth.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../db.config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

// Receive JSON payload
$data = json_decode(file_get_contents("php://input"));
$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === 'register') {
    if (!empty($data->fullname) && !empty($data->email) && !empty($data->password)) {
        $fullname = trim($data->fullname);
        $email = trim($data->email);
        $password = password_hash(trim($data->password), PASSWORD_BCRYPT);

        // Check if email exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Email already registered"]);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $password);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "User registered successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to register user"]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    }
} elseif ($action === 'login') {
    if (!empty($data->email) && !empty($data->password)) {
        $email = trim($data->email);
        $password = trim($data->password);

        $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Return simple token/session equivalent as JSON for frontend localStorage
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "user" => [
                        "id" => $user['id'],
                        "fullname" => $user['fullname'],
                        "email" => $email,
                        "role" => $user['role']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
            }
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing initial fields"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid action specified"]);
}

$conn->close();
?>
