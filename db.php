<?php
// hash_passwords.php
$host = 'localhost';
$db   = 'pos_system';
$user = 'root';
$pass = 'ramaha';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Hash passwords
$stmt = $pdo->query("SELECT id, password FROM users");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    $plainPassword = $user['password'];

    // Check if it's already hashed
    if (!password_get_info($plainPassword)['algo']) {
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Update each user
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $user['id']]);
       // echo "✅ User ID {$user['id']} password hashed.\n";
    } else {
       // echo "🔒 User ID {$user['id']} password already hashed.\n";
    }
}

//echo "✅ All plaintext passwords have been hashed!\n";
?>
