<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Manage Users Handler
 * ------------------------------------------------------------
 *
 * This file handles the logic for managing users.
 * It allows admins/supervisors to:
 * - View users with pagination and search
 * - Update user roles
 * - Ban or unban users
 * - Delete users
 *
 * It also handles form submission, validates input,
 * updates the database, and shows success or error messages.
 */

require_once __DIR__ . '/../../app/functions.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/auth.php';

function redirectToManageUsers($queryParams = []) {
    $url = 'manage_users.php';

    if ( ! empty($queryParams) ) {
        $url .= '?' . http_build_query($queryParams);
    }

    header('Location: ' . $url);
    exit;
}

// Fetch all user roles from the database.
function fetchUserRoles($pdo) {
    $stmt = $pdo->query("
        SELECT role_id, role_name
        FROM role
        ORDER BY role_name ASC
    ");

    return $stmt->fetchAll();
}

/**
 * Count how many users exist in the database.
 * If a search term is provided, only matching users are counted.
 */
function fetchUsersCount($pdo, $search = '') {
    if ($search !== '') {
        $sql = "
            SELECT COUNT(*) AS total
            FROM user u
            INNER JOIN role r ON u.role_id = r.role_id
            WHERE
                u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR u.email LIKE ?
        ";

        $searchTerm = '%' . $search . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

        return (int) $stmt->fetchColumn();
    }

    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM user
    ");

    return (int) $stmt->fetchColumn();
}

/**
 * Fetch users from the database.
 * Supports optional search, limit, and offset for pagination.
 */
function fetchUsers($pdo, $search = '', $limit = 10, $offset = 0) {
    if ($search !== '') {
        $sql = "
            SELECT
                u.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.is_banned,
                u.created_at,
                r.role_id,
                r.role_name
            FROM user u
            INNER JOIN role r ON u.role_id = r.role_id
            WHERE
                u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR u.email LIKE ?
            ORDER BY u.user_id DESC
            LIMIT ? OFFSET ?
        ";

        $searchTerm = '%' . $search . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->bindValue(5, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    $sql = "
        SELECT
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            u.is_banned,
            u.created_at,
            r.role_id,
            r.role_name
        FROM user u
        INNER JOIN role r ON u.role_id = r.role_id
        ORDER BY u.user_id DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Create initials from a user's first and last name.
 * Example: John Brown becomes JB.
 */
function buildUserInitials($firstName, $lastName) {
    $firstInitial = $firstName !== '' ? strtoupper(mb_substr($firstName, 0, 1)) : '';
    $lastInitial = $lastName !== '' ? strtoupper(mb_substr($lastName, 0, 1)) : '';

    return $firstInitial . $lastInitial;
}

/**
 * Process all submitted user updates from the Manage Users form.
 * This function can:
 * - update roles
 * - ban/unban users
 * - delete users
 *
 * A transaction is used so the updates are handled safely.
 */
function processManageUsersSubmission($pdo, $submittedUsers, $currentUserId) {
    $pdo->beginTransaction();

    foreach ($submittedUsers as $userId => $userData) {
        $userId = (int) $userId;
        
        // Skip invalid user ids
        if ($userId <= 0) {
            continue;
        }

        $roleId = isset($userData['role_id']) ? (int) $userData['role_id'] : 0;
        $toggleBan = isset($userData['toggle_ban']);
        $deleteUser = isset($userData['delete']);

        if ($deleteUser && $userId === $currentUserId) {
            continue;
        }

        if ($deleteUser) {
            $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
            $stmt->execute([$userId]);
            continue;
        }

        if ($toggleBan) {
            $stmt = $pdo->prepare("
                SELECT is_banned
                FROM user
                WHERE user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch();

            if ($currentUser && (int) $currentUser['is_banned'] === 1) {
                $stmt = $pdo->prepare("
                    UPDATE user
                    SET is_banned = 0
                    WHERE user_id = ?
                ");
            } else {
                $stmt = $pdo->prepare("
                    UPDATE user
                    SET is_banned = 1
                    WHERE user_id = ?
                ");
            }

            $stmt->execute([$userId]);
        }

        if ($roleId > 0) {
            $stmt = $pdo->prepare("
                UPDATE user
                SET role_id = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$roleId, $userId]);
        }
    }

    $pdo->commit();
}

// Get flash messages
$errors = getFlashErrors();
$success = getFlashSuccess();

/**
 * Get search term and current page from the URL.
 * These are used for filtering and pagination.
 */
$search = trim($_GET['userSearch'] ?? '');
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = 5;

// Page number cannot be less than 1
if ($currentPage < 1) {
    $currentPage = 1;
}

$roles = [];
$users = [];
$totalUsers = 0;
$totalPages = 1;
$offset = 0;

// Process submitted form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['users']) || !is_array($_POST['users'])) {
            $_SESSION['errors'] = ['No user data was submitted.'];
            redirectToManageUsers([
                'userSearch' => $search,
                'page' => $currentPage
            ]);
        }

        processManageUsersSubmission(
            $pdo,
            $_POST['users'],
            (int) ($_SESSION['user']['user_id'] ?? 0)
        );

        $_SESSION['success'] = 'User updates saved successfully.';
        redirectToManageUsers([
            'userSearch' => $search,
            'page' => $currentPage
        ]);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Manage Users Error: ' . $e->getMessage());
        $_SESSION['errors'] = ['Something went wrong while updating users.'];
        redirectToManageUsers([
            'userSearch' => $search,
            'page' => $currentPage
        ]);
    }
}

// Load roles and users for display on the page.
try {
    $roles = fetchUserRoles($pdo);

    $totalUsers = fetchUsersCount($pdo, $search);
    $totalPages = max(1, (int) ceil($totalUsers / $perPage));

    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    $offset = ($currentPage - 1) * $perPage;

    $users = fetchUsers($pdo, $search, $perPage, $offset);

} catch (PDOException $e) {
    error_log('Manage Users Fetch Error: ' . $e->getMessage());
    // echo "<pre>"; var_dump($e->getMessage()); exit; 
    $errors[] = 'Unable to load users right now.';
}