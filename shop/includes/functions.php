<?php
require_once 'db.php';

// User Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function login($username, $password) {
    global $db;
    $username = $db->escape($username);
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return true;
        }
    }
    return false;
}

function register($username, $email, $password, $full_name, $phone = '', $address = '') {
    global $db;
    
    $username = $db->escape($username);
    $email = $db->escape($email);
    $full_name = $db->escape($full_name);
    $phone = $db->escape($phone);
    $address = $db->escape($address);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $phone, $address);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Product Functions
function getProducts($category_id = null, $featured = false, $limit = null) {
    global $db;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    
    if ($category_id) {
        $sql .= " AND p.category_id = " . intval($category_id);
    }
    
    if ($featured) {
        $sql .= " AND p.featured = 1";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $result = $db->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductById($id) {
    global $db;
    $id = intval($id);
    $result = $db->query("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = $id");
    return $result->fetch_assoc();
}

function searchProducts($keyword) {
    global $db;
    $keyword = $db->escape($keyword);
    $result = $db->query("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.name LIKE '%$keyword%' OR p.description LIKE '%$keyword%'
                          ORDER BY p.created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Category Functions
function getCategories() {
    global $db;
    $result = $db->query("SELECT * FROM categories ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCategoryById($id) {
    global $db;
    $id = intval($id);
    $result = $db->query("SELECT * FROM categories WHERE id = $id");
    return $result->fetch_assoc();
}

// Cart Functions
function getCartItems($user_id) {
    global $db;
    $user_id = intval($user_id);
    $result = $db->query("SELECT c.*, p.name, p.price, p.image, p.stock 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = $user_id");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCartCount($user_id) {
    global $db;
    $user_id = intval($user_id);
    $result = $db->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id");
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

function getCartTotal($user_id) {
    global $db;
    $user_id = intval($user_id);
    $result = $db->query("SELECT SUM(c.quantity * p.price) as total 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = $user_id");
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function addToCart($user_id, $product_id, $quantity = 1) {
    global $db;
    $user_id = intval($user_id);
    $product_id = intval($product_id);
    $quantity = intval($quantity);
    
    $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) 
                          VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
    return $stmt->execute();
}

function updateCartQuantity($user_id, $product_id, $quantity) {
    global $db;
    $user_id = intval($user_id);
    $product_id = intval($product_id);
    $quantity = intval($quantity);
    
    if ($quantity <= 0) {
        return removeFromCart($user_id, $product_id);
    }
    
    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    return $stmt->execute();
}

function removeFromCart($user_id, $product_id) {
    global $db;
    $user_id = intval($user_id);
    $product_id = intval($product_id);
    
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function clearCart($user_id) {
    global $db;
    $user_id = intval($user_id);
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Order Functions
function createOrder($user_id, $payment_method, $shipping_address) {
    global $db;
    
    $cart_items = getCartItems($user_id);
    if (empty($cart_items)) {
        return false;
    }
    
    $total = getCartTotal($user_id);
    
    $db->getConnection()->begin_transaction();
    
    try {
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, payment_method, shipping_address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $total, $payment_method, $shipping_address);
        $stmt->execute();
        
        $order_id = $db->lastInsertId();
        
        foreach ($cart_items as $item) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            $stmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }
        
        clearCart($user_id);
        
        $db->getConnection()->commit();
        return $order_id;
    } catch (Exception $e) {
        $db->getConnection()->rollback();
        return false;
    }
}

function getOrders($user_id = null) {
    global $db;
    
    $sql = "SELECT o.*, u.username, u.email FROM orders o 
            JOIN users u ON o.user_id = u.id";
    
    if ($user_id) {
        $user_id = intval($user_id);
        $sql .= " WHERE o.user_id = $user_id";
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $result = $db->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getOrderById($order_id) {
    global $db;
    $order_id = intval($order_id);
    $result = $db->query("SELECT o.*, u.username, u.email, u.phone 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.id 
                          WHERE o.id = $order_id");
    return $result->fetch_assoc();
}

function getOrderItems($order_id) {
    global $db;
    $order_id = intval($order_id);
    $result = $db->query("SELECT oi.*, p.name, p.image 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = $order_id");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Utility Functions
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function uploadImage($file, $upload_dir = '') {
    if (!$upload_dir) {
        $upload_dir = UPLOAD_DIR;
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}

function getStats() {
    global $db;
    
    $stats = [];
    
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
    $stats['users'] = $result->fetch_assoc()['count'];
    
    $result = $db->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $result->fetch_assoc()['count'];
    
    $result = $db->query("SELECT COUNT(*) as count FROM orders");
    $stats['orders'] = $result->fetch_assoc()['count'];
    
    $result = $db->query("SELECT SUM(total_amount) as total FROM orders");
    $stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    return $stats;
}
?>
