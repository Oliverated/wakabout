<?php
require_once __DIR__ . '/includes/db.php';

echo "<h2>Setting up Categories Table...</h2>";

$createTableQuery = "
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    group_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createTableQuery)) {
    echo "<p>Categories table created or already exists.</p>";
} else {
    echo "<p>Error creating table: " . $conn->error . "</p>";
}

$defaultCategories = [
    'Destinations' => ['Nigeria', 'Africa', 'Europe', 'Road Trips'],
    'Culture & Heritage' => ['Festivals', 'Museums', 'Exhibitions', 'Music & Concerts'],
    'Experiences & Lifestyle' => ['Hotels', 'Recreation', 'Fashion & Lifestyle', 'Luxury Travel'],
    'Reviews' => ['Books', 'Reviews', 'Film', 'Stage'],
    'Diaries & Columns' => ['Wayside', 'Editor\'s Notes']
];

$stmt = $conn->prepare("INSERT IGNORE INTO categories (name, group_name) VALUES (?, ?)");

foreach ($defaultCategories as $group => $cats) {
    foreach ($cats as $cat) {
        $stmt->bind_param("ss", $cat, $group);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "Added: $cat ($group)<br>";
            }
        } else {
            echo "Error adding $cat: " . $stmt->error . "<br>";
        }
    }
}

echo "<h3>Done! You can now <a href='index.php'>Go to Homepage</a> or <a href='admin/dashboard.php'>Admin Dashboard</a>.</h3>";
