<?php
/**
 * Centralized session configuration.
 * Include this file BEFORE any session_start() call.
 * It extends session lifetime so users stay logged in longer.
 */

// Only configure if no session is active yet
if (session_status() === PHP_SESSION_NONE) {

    $sessionLifetime = 60 * 60 * 24 * 200; // 200days

    // Tell PHP's garbage collector to keep session files for 7 days
    ini_set('session.gc_maxlifetime', $sessionLifetime);

    // Set the session cookie to persist for 7 days in the browser
    // (default 0 = "until browser closes", which also causes logouts)
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'secure'   => false,   // set to true if using HTTPS
        'httponly'  => true,    // prevent JavaScript access to session cookie
        'samesite'  => 'Lax',  // CSRF protection
    ]);

    session_start();

    // ── Regenerate session ID periodically to prevent fixation ──
    // Regenerate every 30 minutes of activity
    if (!isset($_SESSION['_last_regeneration'])) {
        $_SESSION['_last_regeneration'] = time();
    } elseif (time() - $_SESSION['_last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }

    // ── Refresh the cookie expiry on every request ─────────────
    // This keeps the 7-day window rolling from last activity
    if (isset($_COOKIE[session_name()])) {
        setcookie(
            session_name(),
            session_id(),
            time() + $sessionLifetime,
            '/',
            '',
            false,  // secure - set to true for HTTPS
            true    // httponly
        );
    }
}
