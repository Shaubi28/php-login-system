
<?php
require_once 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL,
	email VARCHAR(100),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
	echo "Table 'users' created or already exists.";
} else {
	echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
