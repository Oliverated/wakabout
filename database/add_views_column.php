<?php
/**
 * One-time migration: add `views` column to the posts table.
 * Run this file once via browser or CLI, then delete (or keep) it.
 *   http://localhost/wakaabout_blog/database/add_views_column.php
 */
require_once __DIR__ . '/../includes/db.php';

// Check if the column already exists to avoid duplicate-column errors
$check = $conn->query("SHOW COLUMNS FROM posts LIKE 'views'");

if ($check && $check->num_rows > 0) {
    echo '<p style="font-family:monospace;color:orange;">⚠️  Column <b>views</b> already exists in <b>posts</b>. Nothing to do.</p>';
} else {
    $sql = "ALTER TABLE posts ADD COLUMN views INT UNSIGNED NOT NULL DEFAULT 0";
    if ($conn->query($sql)) {
        echo '<p style="font-family:monospace;color:green;">✅ Column <b>views</b> added successfully to <b>posts</b>.</p>';
    } else {
        echo '<p style="font-family:monospace;color:red;">❌ Error: ' . htmlspecialchars($conn->error) . '</p>';
    }
}
