<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("sqlite:" . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Enable WAL mode for better concurrency
    $pdo->exec("PRAGMA journal_mode=WAL");

    // Create table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            category TEXT DEFAULT 'General',
            author TEXT DEFAULT 'Wakabout Team',
            excerpt TEXT,
            body TEXT NOT NULL,
            cover_image TEXT DEFAULT NULL,
            published_at DATETIME DEFAULT (datetime('now','localtime')),
            created_at DATETIME DEFAULT (datetime('now','localtime')),
            updated_at DATETIME DEFAULT (datetime('now','localtime'))
        )
    ");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
