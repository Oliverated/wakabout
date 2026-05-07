<?php
/**
 * upload_image.php
 * Handles inline image uploads from the Quill WYSIWYG editor.
 * Returns JSON: { "location": "absolute URL" }  on success
 *               { "error":    "message"       }  on failure
 */

define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/inline/');

// Build an absolute base URL so the image works from any page (e.g. post.php at root)
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
// Walk up from /admin to site root, then point to assets
$adminDir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /wakaabout_blog/admin
$siteRoot = dirname($adminDir);               // e.g. /wakaabout_blog
define('UPLOAD_URL', $scheme . '://' . $host . $siteRoot . '/assets/post-img/inline/');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload error code: ' . $file['error']]);
    exit;
}

$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['error' => 'Invalid file type. Allowed: jpg, jpeg, png, webp, gif']);
    exit;
}

// Max 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['error' => 'File too large. Max 5 MB.']);
    exit;
}

// Create directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Generate unique filename
$filename = uniqid('img_', true) . '.' . $ext;
$dest     = UPLOAD_DIR . $filename;

if (move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['location' => UPLOAD_URL . $filename]);
} else {
    echo json_encode(['error' => 'Failed to save uploaded file.']);
}
