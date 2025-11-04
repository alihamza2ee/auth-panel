<?php
/**
 * Railway.app Configuration
 * Replace your old advanced_config.php with this file
 */

// Railway MySQL environment variables
define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql.railway.internal');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'railway');
define('DB_PORT', getenv('MYSQLPORT') ?: 3306);

// Admin credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'AliHamza@2025');

// Bot version control
define('REQUIRED_BOT_VERSION', '5.0.0');
define('FORCE_UPDATE', false);
define('UPDATE_URL', '');

// Security settings
define('ENABLE_HARDWARE_LOCK', true);
define('ENABLE_IP_LOGGING', true);
define('MAX_LOGIN_ATTEMPTS', 5);

// Database connection
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($conn->connect_error) {
                die(json_encode([
                    'success' => false,
                    'message' => 'Database connection failed: ' . $conn->connect_error
                ]));
            }
            
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]));
        }
    }
    
    return $conn;
}

// Generate unique user ID
function generateUserId() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

// Generate username with pattern
function generateUsername($prefix, $number, $digits = 4) {
    return $prefix . str_pad($number, $digits, '0', STR_PAD_LEFT);
}

// Generate random password
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// Check authentication
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Log activity
function logActivity($action, $details = '', $username = 'system') {
    $db = getDB();
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $db->prepare("INSERT INTO activity_logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $username, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

// Check if key is in valid time range
function isKeyTimeValid($start_date, $end_date) {
    $now = time();
    $start = strtotime($start_date);
    $end = strtotime($end_date . ' 23:59:59');
    
    if ($now < $start) {
        return ['valid' => false, 'reason' => 'not_started', 'message' => 'Key activation date not reached'];
    }
    
    if ($now > $end) {
        return ['valid' => false, 'reason' => 'expired', 'message' => 'Key has expired'];
    }
    
    return ['valid' => true];
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
