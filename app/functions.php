<?php
/**
 * ------------------------------------------------------------
 * 876 Screens - Helper Functions
 * ------------------------------------------------------------
 * 
 * This file contains helper functions used across the project
 * to reduce repetition and keep the code cleaner.
 * 
 * Functions in this file handle things like:
 * - Redirecting users to other pages
 * - Cleaning user input
 * - Checking if a request is POST
 * - Generating ticket reference numbers
 * - Handling flash messages (errors/success)
 * - Creating URLs for links (pretty URLs)
 * - Setting the active page in navigation
 * 
 * This file should be included wherever needed.
 * Only general reusable functions should be placed here.
 * 
 * ------------------------------------------------------------
 */


/**
 * Redirect the user to another page.
 *
 * @param string $path
 * @return void
 */
function redirect($path) {
    header("Location: " . $path);
    exit;
}


/**
 * Base URL of the project.
 * Change this if the project folder name changes.
 *
 * @return string
 */
function baseUrl() {
    return '/876-screens';
}


/**
 * Build a full URL for the project (used for links and forms).
 *
 * Example:
 * url('login') => /876-screens/login
 *
 * @param string $path
 * @return string
 */
function url($path = '') {
    $base = rtrim(baseUrl(), '/');
    $path = ltrim($path, '/');

    return $path ? $base . '/' . $path : $base;
}


/**
 * Clean user input by trimming spaces.
 *
 * @param mixed $value
 * @return string
 */
function sanitizeInput($value) {
    return trim((string) $value);
}


/**
 * Check if the request method is POST.
 *
 * @return bool
 */
function isPostRequest() {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}


/**
 * Generate a unique ticket reference.
 *
 * @return string
 */
function generateTicketReference() {
    return 'TKT-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
}


/**
 * Get error messages from session (flash).
 *
 * @return array
 */
function getFlashErrors() {
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);

    return is_array($errors) ? $errors : [];
}


/**
 * Get success message from session (flash).
 *
 * @return string|null
 */
function getFlashSuccess() {
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['success']);

    return is_string($success) ? $success : null;
}


/**
 * Add 'active' class to current page link.
 *
 * @param string $page
 * @param string $activePage
 * @return string
 */
function isActivePage($page, $activePage) {
    return $page === $activePage ? 'active' : '';
}