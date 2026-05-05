<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';

$dark_mode = 'off';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $darkKey = $user_id . '_dark_mode';

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $darkKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $dark_mode = $row['setting_value'];
    }
}
?>