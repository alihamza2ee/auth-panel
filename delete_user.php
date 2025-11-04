<?php
// delete_user.php
require_once 'advanced_config.php';
if (!isLoggedIn()) { header('Location: advanced_index.php'); exit(); }

$username = $_GET['user'] ?? '';
if ($username) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        logActivity('user_deleted', "Deleted: $username", ADMIN_USER);
    }
}
header('Location: advanced_index.php');
?>
