<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Create User Handler
 * ------------------------------------------------------------
 * 
 * This file handles the form submission for creating a new user
 * by an Admin.
 * 
 * Responsibilities:
 * - Restrict access to Admin users only
 * - Validate form input
 * - Check for duplicate email
 * - Insert new user into the database
 * - Store errors or success messages in session
 * - Redirect user accordingly
 * ------------------------------------------------------------
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../auth.php';

/**
 * Ensure only Admin users can access this handler.
 */
requireRole(['admin']);

/**
 * Only allow POST requests.
 * If accessed directly via GET, redirect back to form.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('admin/create_user.php'));
    exit;
}

// Store old input for form repopulation
$_SESSION['old_input'] = [
    'first_name' => trim($_POST['first_name'] ?? ''),
    'last_name'  => trim($_POST['last_name'] ?? ''),
    'email'      => trim($_POST['email'] ?? ''),
    'phone'      => trim($_POST['phone'] ?? ''),
    'role_id'    => trim($_POST['role_id'] ?? ''),
    'is_banned'  => trim($_POST['is_banned'] ?? '0'),
];

// Get form values
$firstName       = trim($_POST['first_name'] ?? '');
$lastName        = trim($_POST['last_name'] ?? '');
$email           = trim($_POST['email'] ?? '');
$phone           = trim($_POST['phone'] ?? '');
$roleId          = (int) ($_POST['role_id'] ?? 0);
$isBanned        = (int) ($_POST['is_banned'] ?? 0);
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

// Basic validation
if ($firstName === '') $errors[] = 'First name is required.';
if ($lastName === '') $errors[] = 'Last name is required.';

if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}

if ( ! in_array($roleId, [1, 2, 3], true) ) {
    $errors[] = 'Please choose a valid role.';
}

if ( ! in_array($isBanned, [0, 1], true) ) {
    $errors[] = 'Please choose a valid account status.';
}

if  ($password === '' ) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if ($confirmPassword === '') {
    $errors[] = 'Please confirm the password.';
} elseif ($password !== $confirmPassword) {
    $errors[] = 'Password confirmation does not match.';
}

// Check duplicate email
if ( empty($errors) ) {
    $checkUserSql = "SELECT user_id FROM user WHERE email = :email LIMIT 1";
    $checkUserStmt = $pdo->prepare($checkUserSql);
    $checkUserStmt->execute([
        'email' => $email
    ]);

    if ($checkUserStmt->fetch()) {
        $errors[] = 'That email address is already in use.';
    }
}

// Handle errors
if ( ! empty($errors) ) {
    $_SESSION['errors'] = $errors;
    header('Location: ' . url('admin/create_user.php'));
    exit;
}

$avatarPath = null;
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Prepare sql and insert new user
$sql = "INSERT INTO user (
            role_id,
            first_name,
            last_name,
            email,
            password_hash,
            phone,
            avatar_path,
            is_banned
        ) VALUES (
            :role_id,
            :first_name,
            :last_name,
            :email,
            :password_hash,
            :phone,
            :avatar_path,
            :is_banned
        )";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'role_id'       => $roleId,
    'first_name'    => $firstName,
    'last_name'     => $lastName,
    'email'         => $email,
    'password_hash' => $passwordHash,
    'phone'         => $phone !== '' ? $phone : null,
    'avatar_path'   => $avatarPath,
    'is_banned'     => $isBanned,
]);

unset($_SESSION['old_input']);

$_SESSION['success'] = 'User account created successfully.';
header('Location: ' . url('admin/create_user.php'));
exit;