<?php
require __DIR__ . '/db/config.php';
// Run once to create default admin: username 'admin' password 'admin123'
try {
  $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM admin WHERE username=?");
  $stmt->execute(['admin']);
  $row = $stmt->fetch();
  $exists = isset($row['c']) ? $row['c'] : 0;

  if ($exists == 0) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO admin (username, password_hash) VALUES (?,?)")->execute(['admin', $hash]);
    echo "Admin user created. Username: admin, Password: admin123";
  } else {
    echo "Admin already exists.";
  }
} catch (Exception $e) { echo "Error: " . $e->getMessage(); }