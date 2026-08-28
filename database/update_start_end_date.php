<?php
/**
 * Migration: Ensure start_date and end_date columns exist in events table.
 */
require_once __DIR__ . '/../includes/db.php';

echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#eee;}p{margin:6px 0;}</style>';
echo '<h3 style="color:#aaa;">Ensure Start Date & End Date Columns</h3>';

$checkStart = $conn->query("SHOW COLUMNS FROM events LIKE 'start_date'");
if ($checkStart && $checkStart->num_rows === 0) {
    $conn->query("ALTER TABLE events ADD COLUMN start_date DATE DEFAULT NULL AFTER category");
    echo "<p style='color:limegreen;'>✅ Column <b>start_date</b> added.</p>";
} else {
    echo "<p style='color:orange;'>⚠️ Column <b>start_date</b> already exists.</p>";
}

$checkEnd = $conn->query("SHOW COLUMNS FROM events LIKE 'end_date'");
if ($checkEnd && $checkEnd->num_rows === 0) {
    $conn->query("ALTER TABLE events ADD COLUMN end_date DATE DEFAULT NULL AFTER start_date");
    echo "<p style='color:limegreen;'>✅ Column <b>end_date</b> added.</p>";
} else {
    echo "<p style='color:orange;'>⚠️ Column <b>end_date</b> already exists.</p>";
}

echo '<p style="color:#888;margin-top:16px;">Migration complete.</p>';
