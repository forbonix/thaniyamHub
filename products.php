<?php
// backend/api/products.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db.config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(["status" => "success", "data" => $result->fetch_assoc()]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Product not found"]);
        }
        $stmt->close();
    } else {
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $products]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}

$conn->close();
?>
