<?php
/**
 * One-time migration: add start_date, end_date, start_time, end_time, city
 * columns to the events table.
 * Run once via browser: http://localhost/wakaabout/database/add_event_fields.php
 */
require_once __DIR__ . '/../includes/db.php';

$columns = [
    'category'   => "ALTER TABLE events ADD COLUMN category VARCHAR(150) DEFAULT 'General' AFTER title",
    'start_date' => "ALTER TABLE events ADD COLUMN start_date DATE DEFAULT NULL AFTER event_date",
    'end_date'   => "ALTER TABLE events ADD COLUMN end_date DATE DEFAULT NULL AFTER start_date",
    'start_time' => "ALTER TABLE events ADD COLUMN start_time TIME DEFAULT NULL AFTER end_date",
    'end_time'   => "ALTER TABLE events ADD COLUMN end_time TIME DEFAULT NULL AFTER start_time",
    'city'       => "ALTER TABLE events ADD COLUMN city VARCHAR(150) DEFAULT NULL AFTER location",
];

echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#eee;}p{margin:6px 0;}</style>';
echo '<h3 style="color:#aaa;">Events Table Migration</h3>';

foreach ($columns as $col => $sql) {
    $check = $conn->query("SHOW COLUMNS FROM events LIKE '$col'");
    if ($check && $check->num_rows > 0) {
        echo "<p style='color:orange;'>⚠️  Column <b>$col</b> already exists. Skipped.</p>";
    } else {
        if ($conn->query($sql)) {
            echo "<p style='color:limegreen;'>✅ Column <b>$col</b> added to <b>events</b>.</p>";
        } else {
            echo "<p style='color:red;'>❌ Error adding <b>$col</b>: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
}

echo '<p style="color:#888;margin-top:16px;">Migration complete. You can now delete this file.</p>';
