<?php
// Load the Env class and parse the .env file (idempotent — safe to call multiple times)
require_once __DIR__ . '/../env/env.php';
Env::load(__DIR__ . '/../env/.env');

$conn = new mysqli(
    Env::get('DB_HOST', 'localhost'),
    Env::get('DB_USER', 'root'),
    Env::get('DB_PASS', ''),
    Env::get('DB_NAME', 'wakabout')
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
