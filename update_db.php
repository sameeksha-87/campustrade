<?php
require_once 'config/db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
      email VARCHAR(150) NOT NULL,
      token VARCHAR(255) NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      expires_at DATETIME,
      INDEX(email),
      INDEX(token)
    )";
    
    $pdo->exec($sql);
    echo "Table 'password_resets' created successfully.";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
