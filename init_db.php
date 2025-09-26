<?php
// init_db.php - create sqlite DB and required tables
$dbfile = __DIR__ . '/vulnlab.db';

try {
    $db = new PDO('sqlite:' . $dbfile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // users table (store plaintext passwords for the lab)
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        );
    ");

    // sessions table (stores session id -> username)
    $db->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sid TEXT UNIQUE NOT NULL,
            username TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // attempts table (IP tracking)
    $db->exec("
        CREATE TABLE IF NOT EXISTS attempts (
            ip TEXT PRIMARY KEY,
            fail_count INTEGER DEFAULT 0,
            blocked INTEGER DEFAULT 0
        );
    ");

    // Insert admin user if not present
    $stmt = $db->prepare("SELECT COUNT(*) AS c FROM users WHERE username = :u");
    $stmt->execute([':u' => 'admin']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['c'] == 0) {
        $db->prepare("INSERT INTO users (username, password) VALUES (:u, :p)")
           ->execute([':u' => 'admin', ':p' => 'Password123']);
    }

    echo "DB init OK\n";
} catch (Exception $e) {
    echo "DB init error: " . $e->getMessage() . "\n";
    exit(1);
}
