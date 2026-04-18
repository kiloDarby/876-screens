<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Session Helper
 * ------------------------------------------------------------
 *
 * This file handles starting the session for the application.
 *
 * It makes sure:
 * - A session is not started more than once
 * - Basic security settings are applied to the session cookie
 *
 * Session data (like $_SESSION['user'], errors, etc.)
 * is used across the app for authentication and messages.
 *
 * ------------------------------------------------------------
 */


// Prevent multiple session_start() calls
if (session_status() === PHP_SESSION_NONE) {

    // Set session cookie settings
    session_set_cookie_params([
        'lifetime' => 0, // Ends when browser is closed
        'path' => '/', // Available across the whole site
        'httponly' => true, // Prevent JS from accessing cookie
        'secure' => false, // Change to true when using HTTPS
        'samesite' => 'Lax' // Helps reduce CSRF risks
    ]);

    session_start();    // Start session
}