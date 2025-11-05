<?php
/**
 * ADVANCED AUTHENTICATION API - Railway Panel
 * ============================================
 * VERSION: 2.0 - Username Only Authentication
 * Password checking DISABLED - Username is the key
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'advanced_config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$action = $data['action'] ?? '';

// ============================================
// LOGIN ACTION - USERNAME ONLY
// ============================================
if ($action === 'login') {
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';  // IGNORED - NOT CHECKED
    $device_id = $data['device_id'] ?? '';
    $hardware_id = $data['hardware_id'] ?? '';
    $version = $data['version'] ?? '';
    
    // Log activity
    logActivity('login_attempt', "User: $username", $username);
    
    if (empty($username)) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Username required'
        ]);
        exit();
    }
    
    $db = getDB();
    
    // Get user by USERNAME ONLY - PASSWORD NOT CHECKED
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'User not found'
        ]);
        exit();
    }
    
    // Check if banned
    if ($user['status'] === 'banned') {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'banned' => true,
            'status' => 'banned',
            'message' => 'Account is banned'
        ]);
        exit();
    }
    
    // Check date range
    $current_date = date('Y-m-d');
    $start_date = $user['start_date'];
    $end_date = $user['end_date'];
    
    if ($current_date < $start_date) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'status' => 'not_started',
            'message' => 'Key activation date not reached'
        ]);
        exit();
    }
    
    if ($current_date > $end_date) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'status' => 'expired',
            'message' => 'Key has expired'
        ]);
        exit();
    }
    
    // Hardware lock check
    if (ENABLE_HARDWARE_LOCK && !empty($user['hardware_id'])) {
        if ($user['hardware_id'] !== $hardware_id) {
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'hardware_locked' => true,
                'message' => 'Key is locked to another device'
            ]);
            exit();
        }
    }
    
    // Bind hardware if not set
    if (ENABLE_HARDWARE_LOCK && empty($user['hardware_id']) && !empty($hardware_id)) {
        $stmt = $db->prepare("UPDATE users SET hardware_id = ?, device_id = ? WHERE username = ?");
        $stmt->bind_param("sss", $hardware_id, $device_id, $username);
        $stmt->execute();
    }
    
    // Update last login
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt = $db->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE username = ?");
    $stmt->bind_param("ss", $ip_address, $username);
    $stmt->execute();
    
    // Log success
    logActivity('login_success', "IP: $ip_address, Device: $device_id", $username);
    
    // SUCCESS - PASSWORD WAS IGNORED
    echo json_encode([
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
        'permissions' => ['basic'],
        'message' => 'Authentication successful'
    ]);
    exit();
}

// ============================================
// VERSION CHECK ACTION
// ============================================
if ($action === 'check_version') {
    $current_version = $data['current_version'] ?? '0.0.0';
    
    $db = getDB();
    $stmt = $db->query("SELECT * FROM bot_versions ORDER BY id DESC LIMIT 1");
    $latest = $stmt->fetch_assoc();
    
    if (!$latest) {
        echo json_encode([
            'force_update' => false,
            'needs_update' => false,
            'latest_version' => $current_version,
            'current_version' => $current_version
        ]);
        exit();
    }
    
    $required_version = $latest['version'];
    $force_update = (bool)$latest['force_update'];
    
    // Compare versions
    $needs_update = version_compare($current_version, $required_version, '<');
    
    if ($needs_update && $force_update) {
        // FORCE UPDATE
        echo json_encode([
            'force_update' => true,
            'update_required' => true,
            'needs_update' => true,
            'required_version' => $required_version,
            'current_version' => $current_version,
            'download_url' => $latest['download_url'] ?? '',
            'message' => 'Update required'
        ]);
    } else if ($needs_update) {
        // OPTIONAL UPDATE
        echo json_encode([
            'force_update' => false,
            'needs_update' => true,
            'latest_version' => $required_version,
            'current_version' => $current_version,
            'download_url' => $latest['download_url'] ?? '',
            'message' => 'Update available'
        ]);
    } else {
        // UP TO DATE
        echo json_encode([
            'force_update' => false,
            'needs_update' => false,
            'latest_version' => $required_version,
            'current_version' => $current_version,
            'message' => 'Version up to date'
        ]);
    }
    exit();
}

// Unknown action
echo json_encode([
    'success' => false,
    'message' => 'Unknown action'
]);
?>
