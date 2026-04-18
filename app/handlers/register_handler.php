<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Register Handler
 * ------------------------------------------------------------
 *
 * This file handles the user registration process.
 * It collects form data, validates the inputs,
 * checks if the email already exists, creates a new user account,
 * and then logs the user in after successful registration.
 */

require_once __DIR__ . '/../../app/functions.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/auth.php';

// Only allow form submissions using POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('public/register.php'));
    exit;
}

$firstName = sanitizeInput($_POST['first_name'] ?? '');
$lastName = sanitizeInput($_POST['last_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$termsAccepted = isset($_POST['terms']);

$errors = [];

// Validate first name
if ($firstName === '') {
    $errors[] = 'First name is required.';
} elseif (mb_strlen($firstName) > 100) {
    $errors[] = 'First name must not exceed 100 characters.';
}

// Validate last name
if ($lastName === '') {
    $errors[] = 'Last name is required.';
} elseif (mb_strlen($lastName) > 100) {
    $errors[] = 'Last name must not exceed 100 characters.';
}

// Validate email
if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
} elseif (mb_strlen($email) > 150) {
    $errors[] = 'Email must not exceed 150 characters.';
}

// Validate phone
if ($phone !== '' && mb_strlen($phone) > 20) {
    $errors[] = 'Phone number must not exceed 20 characters.';
}

// Validate password
if ($password === '') {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if ($confirmPassword === '') {
    $errors[] = 'Please confirm your password.';
} elseif ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

// Validate terms
if (!$termsAccepted) {
    $errors[] = 'You must agree to the Terms & Conditions and Privacy Policy.';
}

// Preserve old input except passwords
$_SESSION['old_input'] = [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'phone' => $phone
];

// If there are validation errors, send user back to register page
if ( ! empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: ' . url('public/register.php'));
    exit;
}

try {
    // Check if email already exists
    $checkEmailSql = "SELECT user_id FROM user WHERE email = ? LIMIT 1";
    $checkStmt = $pdo->prepare($checkEmailSql);
    $checkStmt->execute([$email]);

    if ($checkStmt->fetch()) {
        $_SESSION['errors'] = ['An account with that email already exists.'];
        header('Location: ' . url('public/register.php'));
        exit;
    }

    // Get default role_id for User
    $roleSql = "SELECT role_id, role_name FROM role WHERE role_name = ? LIMIT 1";
    $roleStmt = $pdo->prepare($roleSql);
    $roleStmt->execute(['User']);

    $role = $roleStmt->fetch();

    if (!$role) {
        $_SESSION['errors'] = ['Default user role was not found in the database.'];
        header('Location: ' . url('public/register.php'));
        exit;
    }

    $roleId = $role['role_id'];
    $roleName = $role['role_name'];

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $insertSql = "
        INSERT INTO user (role_id, first_name, last_name, email, password_hash, phone)
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        $roleId,
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $phone
    ]);

    $userId = $pdo->lastInsertId();

    unset($_SESSION['old_input']);

    // Auto-login user
    $_SESSION['user'] = [
        'user_id' => $userId,
        'role_id' => $roleId,
        'role_name' => $roleName,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email
    ];

    $_SESSION['success'] = 'Welcome to 876 Screens! Your account has been created successfully.';

    header('Location: ' . url('public/index.php'));
    exit;

} catch (PDOException $e) {
    $_SESSION['errors'] = ['Registration failed. Please try again.'];
    header('Location: ' . url('public/register.php'));
    exit;
}