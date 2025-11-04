<?php
/**
 * Advanced Configuration File
 * ===========================
 * Professional Authentication System
 */

// ============== DATABASE CONFIGURATION ==============
define('DB_HOST', 'sql205.infinityfree.com');
define('DB_USER', 'if0_40332408');
define('DB_PASS', 'ALIHAMZA2e');
define('DB_NAME', 'if0_40332408_alihamzapanel');
// ==================================================

// ============== ADMIN CONFIGURATION ==============
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'AliHamza@2025');
// =================================================

// ============== BOT VERSION CONTROL ==============
define('REQUIRED_BOT_VERSION', '5.0.0');  // Change this to force update
define('FORCE_UPDATE', false);  // Set true to force all bots to update
define('UPDATE_URL', 'https://yourwebsite.com/bot_update.zip');
// =================================================

// ============== SECURITY SETTINGS ==============
define('ENABLE_HARDWARE_LOCK', true);  // Hardware ID binding
define('ENABLE_IP_LOGGING', true);     // Log IP addresses
define('MAX_LOGIN_ATTEMPTS', 5);        // Max failed attempts before temp ban
// ==============================================

// Database connection
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed'
            ]));
        }
        
        $conn->set_charset("utf8mb4");
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
    $stmt->bind_param("ssss", $username, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
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

// Get hardware fingerprint
function getHardwareFingerprint($device_id, $additional_data = []) {
    $data = [
        'device_id' => $device_id,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'additional' => $additional_data
    ];
    return hash('sha256', json_encode($data));
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
