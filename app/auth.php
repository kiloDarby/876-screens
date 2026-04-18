<?php

require_once __DIR__ . '/session.php';

/**
 * ------------------------------------------------------------
 * 876 Screens - Authentication and Authorization Helper Functions
 * ------------------------------------------------------------
 *
 * This file contains helper functions used to manage:
 * - User login status
 * - Current logged-in user data
 * - Role checking
 * - Page protection
 *
 * These functions rely on PHP session data (e.g. $_SESSION['user'])
 * to track authentication state and user roles across the application.
 *
 * ------------------------------------------------------------
 */


/**
 * Check if a user is currently logged in.
 *
 * A user is considered logged in if the session contains
 * a 'user' entry and that entry is stored as an array.
 *
 * @return bool True if logged in, otherwise false
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}


/**
 * Get the currently logged-in user's session data.
 *
 * If no user is logged in, null is returned.
 *
 * @return array|null
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}


/**
 * Check if the logged-in user has one of the allowed roles.
 *
 * @param string|array $roles
 * @return bool
 */
function hasRole($roles) {
    if ( ! isLoggedIn() || ! isset($_SESSION['user']['role_name']) ) {
        return false;
    }

    $userRole = strtolower($_SESSION['user']['role_name']);

    if ( ! is_array($roles) ) {
        $roles = [$roles];
    }

    $normalizedRoles = array_map(fn($role) => strtolower(trim($role)), $roles);

    return in_array($userRole, $normalizedRoles, true);
}


/**
 * Protect a page so that only logged-in users can access it.
 *
 * Redirects unauthenticated users to the login page.
 *
 * @return void
 */
function requireLogin() {
    if ( ! isLoggedIn()) {
        $_SESSION['errors'] = ['You must be logged in to access that page.'];
        header('Location: ' . url('public/login.php')); // Redirect to Login Page
        exit;
    }
}


/**
 * Protect a page so that only guests can access it.
 *
 * Redirects logged-in users to the home page.
 *
 * @return void
 */
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: ' . url('public/index.php')); // Redirect to Home Page
        exit;
    }
}


/**
 * Restrict access to users with specific roles only.
 *
 * Redirects unauthorized users to the home page.
 *
 * @param array $allowedRoles
 * @return void
 */
function requireRole($allowedRoles) {
    requireLogin();

    $userRole = strtolower($_SESSION['user']['role_name'] ?? '');
    $normalizedRoles = array_map(fn($role) => strtolower(trim($role)), $allowedRoles);

    if ( ! in_array($userRole, $normalizedRoles, true) ) {
        $_SESSION['errors'] = ['You are not authorized to access that page.'];
        header('Location: ' . url('public/index.php')); // Redirect to Home Page
        exit;
    }
}


/**
 * Quick helper for pages that should only be accessed
 * by Admins or Supervisors.
 *
 * @return void
 */
function requireAdminOrSupervisor() {
    requireRole(['Admin', 'Supervisor']);
}


/**
 * Check if the logged-in user is an Admin.
 *
 * @return bool
 */
function isAdmin() {
    if ( ! isLoggedIn() ) {
        return false;
    }
    return strtolower(trim($_SESSION['user']['role_name'] ?? '')) === 'admin';
}


/**
 * Check if the logged-in user is either an Admin or Supervisor.
 *
 * @return bool
 */
function isAdminOrSupervisor() {
    return hasRole(['Admin', 'Supervisor']);
}