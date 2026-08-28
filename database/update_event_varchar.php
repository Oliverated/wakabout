<?php
/**
 * Migration: Ensure event_date and event_time are VARCHAR columns and clean up unused columns.
 */
require_once __DIR__ . '/../includes/db.php';

echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#eee;}p{margin:6px 0;}</style>';
echo '<h3 style="color:#aaa;">Events Table VARCHAR Migration</h3>';

// Check if event_time column exists
$checkTime = $conn->query("SHOW COLUMNS FROM events LIKE 'event_time'");
if ($checkTime && $checkTime->num_rows === 0) {
    if ($conn->query("ALTER TABLE events ADD COLUMN event_time VARCHAR(100) DEFAULT NULL AFTER event_date")) {
        echo "<p style='color:limegreen;'>✅ Column <b>event_time</b> (VARCHAR) added to <b>events</b>.</p>";
    } else {
        echo "<p style='color:red;'>❌ Error adding <b>event_time</b>: " . htmlspecialchars($conn->error) . "</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠️ Column <b>event_time</b> already exists.</p>";
}

// Check if event_date exists & type
$checkDate = $conn->query("SHOW COLUMNS FROM events LIKE 'event_date'");
if ($checkDate && $checkDate->num_rows === 0) {
    $conn->query("ALTER TABLE events ADD COLUMN event_date VARCHAR(150) DEFAULT NULL AFTER category");
    echo "<p style='color:limegreen;'>✅ Column <b>event_date</b> (VARCHAR) added to <b>events</b>.</p>";
} else {
    $conn->query("ALTER TABLE events MODIFY COLUMN event_date VARCHAR(150) DEFAULT NULL");
    echo "<p style='color:limegreen;'>✅ Column <b>event_date</b> modified to VARCHAR(150).</p>";
}

echo '<p style="color:#888;margin-top:16px;">Migration complete.</p>';
