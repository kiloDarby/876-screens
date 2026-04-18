<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Login Handler
 * ------------------------------------------------------------
 *
 * This file handles the login process for users.
 * It checks that the request is POST, collects and validates
 * the email and password, and then verifies the user against the database.
 *
 * If the login details are correct, a session is created and
 * user information is stored. The user is then redirected based on role.
 *
 * If validation fails or login is incorrect, error messages are stored
 * in the session and the user is redirected to the login page.
 *
 * ------------------------------------------------------------
 */

session_start();

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('public/login.php'));
    exit;
}

// Get input
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Validate input
if ($email === '') {
    $errors[] = 'Email is required.';
} elseif ( ! filter_var($email, FILTER_VALIDATE_EMAIL) ) {
    $errors[] = 'Enter a valid email address.';
}

if ($password === '') {
    $errors[] = 'Password is required.';
}

// Preserve old input
$_SESSION['old_input'] = [
    'email' => $email
];

if ( ! empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: ' . url('public/login.php'));
    exit;
}

try {
    // Fetch user and user role
    $sql = "
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.phone,
            u.email,
            u.password_hash,
            r.role_id,
            r.role_name
        FROM user u
        JOIN role r ON u.role_id = r.role_id
        WHERE u.email = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    // Check if user exists
    if ( ! $user ) {
        $_SESSION['errors'] = ['Invalid email or password.'];
        header('Location: ' . url('public/login.php'));
        exit;
    }

    // Verify password
    if ( ! password_verify($password, $user['password_hash'])) {
        $_SESSION['errors'] = ['Invalid email or password.'];
        header('Location: ' . url('public/login.php'));
        exit;
    }

    // Login success, init set session data
    $_SESSION['user'] = [
        'user_id' => (int) $user['user_id'],
        'role_id' => (int) $user['role_id'],
        'role_name' => $user['role_name'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone'],
        'email' => $user['email']
    ];

    unset($_SESSION['old_input']);

    $_SESSION['success'] = 'Welcome back, ' . $user['first_name'] . '!';

    // Role-based redirect
    if (isAdminOrSupervisor()) {
        header('Location: ' . url('admin/manage_movies.php'));
    } else {
        header('Location: ' . url('public/index.php'));
    }

    exit;

} catch (PDOException $e) {
    $_SESSION['errors'] = ['Login failed. Please try again.'];
    header('Location: ' . url('public/login.php'));
    exit;
}