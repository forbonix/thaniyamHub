<?php
// backend/api/orders.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../db.config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (isset($data->user_id) && isset($data->total_amount) && isset($data->items) && is_array($data->items)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create Order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("id", $data->user_id, $data->total_amount);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Create Order Items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            
            // Update stock
            $stock_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
            
            foreach ($data->items as $item) {
                // Add item
                $item_stmt->bind_param("iiid", $order_id, $item->product_id, $item->quantity, $item->price);
                $item_stmt->execute();
                
                // Deduct stock
                $stock_stmt->bind_param("iii", $item->quantity, $item->product_id, $item->quantity);
                $stock_stmt->execute();
                
                if ($stock_stmt->affected_rows === 0) {
                    throw new Exception("Not enough stock for product ID: " . $item->product_id);
                }
            }
            $item_stmt->close();
            $stock_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Send Order Confirmation Email
            $user_stmt = $conn->prepare("SELECT email, fullname FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $data->user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            if ($user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                $to = $user['email'];
                $subject = "Order Confirmation - ThaniyamHub (Order #" . $order_id . ")";
                $message = "Hi " . $user['fullname'] . ",\n\n";
                $message .= "Thank you for shopping at ThaniyamHub!\n";
                $message .= "Your Order ID is: #" . $order_id . "\n";
                $message .= "Total Amount: Rs. " . number_format($data->total_amount, 2) . "\n\n";
                $message .= "We will process your millets and notify you once they are shipped.\n\n";
                $message .= "Stay healthy,\nThe ThaniyamHub Team";
                
                $headers = "From: no-reply@thaniyamhub.com\r\n";
                $headers .= "Reply-To: support@thaniyamhub.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                
                // Note: This relies on XAMPP php.ini having valid SMTP settings or sendmail.
                @mail($to, $subject, $message, $headers);
            }
            $user_stmt->close();
            
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Order placed successfully", "order_id" => $order_id]);
            
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to place order: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid order format"]);
    }
} elseif ($method === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    
    if ($user_id) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // get items
            $item_stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $item_stmt->bind_param("i", $row['id']);
            $item_stmt->execute();
            $item_result = $item_stmt->get_result();
            
            $items = [];
            while ($item = $item_result->fetch_assoc()) {
                $items[] = $item;
            }
            $row['items'] = $items;
            $item_stmt->close();
            
            $orders[] = $row;
        }
        $stmt->close();
        echo json_encode(["status" => "success", "data" => $orders]);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "User ID required"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}

$conn->close();
?>
