<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $cfg = require __DIR__ . '/config.php';
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if ($cfg['db_driver'] === 'mysql') {
        $m = $cfg['mysql'];
        $pdo = new PDO("mysql:host={$m['host']};dbname={$m['name']};charset=utf8mb4", $m['user'], $m['pass'], $opts);
    } else {
        $pdo = new PDO('sqlite:' . $cfg['sqlite_path'], null, null, $opts);
        $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            service TEXT NOT NULL,
            booking_date TEXT NOT NULL,
            booking_time TEXT NOT NULL,
            message TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (booking_date, booking_time)
        )");
    }
    return $pdo;
}
