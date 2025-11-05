<?php
// NO WHITESPACE BEFORE <?php TAG!
// SUPER CLEAN VERSION - NO HTML OUTPUT

// Start output buffering
ob_start();

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// ============================================
// DATABASE CONFIG - CHANGE THESE!
// ============================================
define('DB_HOST', 'autorack.proxy.rlwy.net');
define('DB_PORT', '12345');
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_PASSWORD');
define('DB_NAME', 'railway');

// ============================================
// FUNCTIONS
// ============================================
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            if ($db->connect_error) {
                sendError('Database connection failed');
            }
            $db->set_charset('utf8mb4');
        } catch (Exception $e) {
            sendError('Database error');
        }
    }
    return $db;
}

function sendError($message) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

function sendSuccess($data) {
    ob_clean();
    echo json_encode($data);
    exit();
}

function logActivity($action, $details, $username = '') {
    try {
        $db = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = @$db->prepare("INSERT INTO activity_logs (username, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $action, $details, $ip);
            @$stmt->execute();
        }
    } catch (Exception $e) {
        // Silent
    }
}

// ============================================
// PARSE INPUT
// ============================================
$input = @file_get_contents('php://input');
$data = @json_decode($input, true);

if (!$data || !isset($data['action'])) {
    sendError('Invalid request');
}

$action = $data['action'];

// ============================================
// LOGIN ACTION
// ============================================
if ($action === 'login') {
    $username = $data['username'] ?? '';
    $device_id = $data['device_id'] ?? '';
    $hardware_id = $data['hardware_id'] ?? '';
    
    logActivity('login_attempt', "User: $username", $username);
    
    if (empty($username)) {
        sendError('Username required');
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            sendError('User not found');
        }
        
        if ($user['status'] === 'banned') {
            sendSuccess([
                'success' => false,
                'authenticated' => false,
                'banned' => true,
                'message' => 'Account banned'
            ]);
        }
        
        $current_date = date('Y-m-d');
        
        if ($current_date < $user['start_date']) {
            sendError('Key not active yet');
        }
        
        if ($current_date > $user['end_date']) {
            sendError('Key expired');
        }
        
        // Hardware check
        if (!empty($user['hardware_id']) && $user['hardware_id'] !== $hardware_id) {
            sendSuccess([
                'success' => false,
                'authenticated' => false,
                'hardware_locked' => true,
                'message' => 'Hardware locked'
            ]);
        }
        
        // Bind hardware
        if (empty($user['hardware_id']) && !empty($hardware_id)) {
            $stmt = $db->prepare("UPDATE users SET hardware_id = ?, device_id = ? WHERE username = ?");
            $stmt->bind_param("sss", $hardware_id, $device_id, $username);
            $stmt->execute();
        }
        
        // Update login
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $db->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE username = ?");
        $stmt->bind_param("ss", $ip, $username);
        $stmt->execute();
        
        logActivity('login_success', "IP: $ip", $username);
        
        sendSuccess([
            'success' => true,
            'authenticated' => true,
            'user_id' => $user['id'],
            'userid' => $user['id'],
            'username' => $user['username'],
            'start_date' => $user['start_date'],
            'end_date' => $user['end_date'],
            'ExpireDate' => $user['end_date'],
            'status' => 'active',
            'hardware_locked' => !empty($user['hardware_id']),
            'message' => 'Success'
        ]);
        
    } catch (Exception $e) {
        sendError('Server error');
    }
}

// ============================================
// VERSION CHECK
// ============================================
if ($action === 'check_version') {
    $current = $data['current_version'] ?? '0.0.0';
    
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM bot_versions ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch_assoc();
        
        if (!$latest) {
            sendSuccess([
                'force_update' => false,
                'needs_update' => false,
                'current_version' => $current
            ]);
        }
        
        $required = $latest['version'];
        $force = (bool)$latest['force_update'];
        $needs = version_compare($current, $required, '<');
        
        if ($needs && $force) {
            sendSuccess([
                'force_update' => true,
                'update_required' => true,
                'required_version' => $required,
                'current_version' => $current,
                'message' => 'Update required'
            ]);
        } else if ($needs) {
            sendSuccess([
                'force_update' => false,
                'needs_update' => true,
                'latest_version' => $required,
                'current_version' => $current
            ]);
        } else {
            sendSuccess([
                'force_update' => false,
                'needs_update' => false,
                'current_version' => $current
            ]);
        }
    } catch (Exception $e) {
        sendSuccess([
            'force_update' => false,
            'needs_update' => false
        ]);
    }
}

sendError('Unknown action');
?>
