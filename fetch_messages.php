<?php
session_start();
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isset($_SESSION['user_id']) || !isset($_GET['chat_with'])) {
    http_response_code(400);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_with = intval($_GET['chat_with']);

if ($chat_with <= 0 || $user_id <= 0) {
    http_response_code(400);
    exit();
}

// Ensure messages table exists with all columns
$tableCheck = $conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_conversation (sender_id, receiver_id),
    KEY idx_receiver (receiver_id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if (!$tableCheck) {
    http_response_code(500);
    exit('Database error');
}

// Fetch messages between the two users (limit last 100 for performance)
$stmt = $conn->prepare("
    SELECT m.id, m.message, m.sender_id, m.created_at, m.is_read
    FROM messages m 
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
    LIMIT 100
");

if (!$stmt) {
    http_response_code(500);
    exit('Prepare failed');
}

$stmt->bind_param("iiii", $user_id, $chat_with, $chat_with, $user_id);

if (!$stmt->execute()) {
    http_response_code(500);
    exit('Execute failed');
}

$result = $stmt->get_result();

// Mark received messages as read
if ($result->num_rows > 0) {
    $markReadStmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0");
    $markReadStmt->bind_param("ii", $user_id, $chat_with);
    $markReadStmt->execute();
    $markReadStmt->close();
    
    // Re-fetch with updated data
    $stmt->bind_param("iiii", $user_id, $chat_with, $chat_with, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result->num_rows === 0) {
    echo '<div style="text-align:center; color:#999; padding:20px; margin-top:40px;">No messages yet. Start the conversation!</div>';
} else {
    while ($row = $result->fetch_assoc()) {
        $is_me = ($row['sender_id'] == $user_id) ? 'me' : 'other';
        $time = date('H:i', strtotime($row['created_at']));
        $fullDate = date('M d, Y H:i', strtotime($row['created_at']));
        
        echo '<div class="msg ' . $is_me . '" title="' . htmlspecialchars($fullDate) . '">';
        echo '<div class="msg-text">' . htmlspecialchars($row['message']) . '</div>';
        echo '<div class="msg-time">' . htmlspecialchars($time) . '</div>';
        echo '</div>';
    }
}

$stmt->close();
?>
