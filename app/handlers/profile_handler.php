<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Profile Handler
 * ------------------------------------------------------------
 *
 * This file handles the main profile logic in the system.
 * It is responsible for:
 * - loading profile data
 * - checking if a user can view, edit, or delete a profile
 * - updating profile details
 * - changing password
 * - uploading profile avatars
 * - deleting a profile
 */

require_once __DIR__ . '/../../app/functions.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/auth.php';

/**
 * Get all data needed for the profile page.
 * This includes the selected profile, roles,
 * and extra values for display.
 */
function getProfilePageData($currentUser, $requestedUserId = null) {
    $currentUserId = (int) ($currentUser['user_id'] ?? 0);
    $profileUserId = $requestedUserId ?: $currentUserId;
    $isOwnProfile = $profileUserId === $currentUserId;

    // Prevent normal users from opening other users' profiles
    if ( ! $isOwnProfile && ! isAdminOrSupervisor()) {
        return [
            'success' => false,
            'errors' => ['You are not allowed to view that profile.'],
        ];
    }

    $profileUser = findProfileUserById($profileUserId);
    
    // Stop if the requested profile does not exist
    if ( ! $profileUser) {
        return [
            'success' => false,
            'errors' => ['User profile not found.'],
        ];
    }

    return [
        'success' => true,
        'errors' => [],
        'profile_user' => $profileUser,
        'is_own_profile' => $isOwnProfile,
        'can_edit' => canEditProfile($currentUser, $profileUser),
        'can_delete' => canDeleteProfile($currentUser, $profileUser),
        'view' => buildProfileViewData($profileUser),
    ];
}

/**
 * Find one user profile by id.
 * This fetches the user details along with the role name.
 */
function findProfileUserById($userId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.role_id,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            u.avatar_path,
            u.created_at,
            u.is_banned,
            u.password_hash,
            r.role_name
        FROM user u
        INNER JOIN role r ON r.role_id = u.role_id
        WHERE u.user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute(['user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

/**
 * Build extra profile data for display on the page.
 * This includes full name, initials, avatar URL,
 * member since date, and account status text.
 */
function buildProfileViewData($user) {
    $firstName = trim($user['first_name'] ?? '');
    $lastName = trim($user['last_name'] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);

    $initials = strtoupper(
        ($firstName !== '' ? substr($firstName, 0, 1) : '') .
        ($lastName !== '' ? substr($lastName, 0, 1) : '')
    );

    $avatarPath = trim($user['avatar_path'] ?? '');
    $avatarUrl = $avatarPath !== '' ? url($avatarPath) : '';

    $memberSince = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'Unknown';

    $isBanned = (int) ($user['is_banned'] ?? 0) === 1;

    return [
        'full_name' => $fullName !== '' ? $fullName : 'User',
        'initials' => $initials !== '' ? $initials : 'U',
        'avatar_url' => $avatarUrl,
        'member_since' => $memberSince,
        'status_text' => $isBanned ? 'Banned' : 'Active member',
    ];
}

/**
 * Check if the current user is allowed to edit a profile.
 * A user can edit their own profile.
 * Only an admin can edit someone else's profile.
 */
function canEditProfile($currentUser, $profileUser) {
    $currentUserId = (int) ($currentUser['user_id'] ?? 0);
    $profileUserId = (int) ($profileUser['user_id'] ?? 0);

    if ($currentUserId === $profileUserId) {
        return true;
    }

    return isAdmin();
}

/**
 * Check if the current user is allowed to delete a profile.
 * Admins and supervisors can delete profiles,
 * but users cannot delete themselves.
 * A supervisor also cannot delete an admin.
 */
function canDeleteProfile($currentUser, $profileUser) {
    $currentUserId = (int) ($currentUser['user_id'] ?? 0);
    $profileUserId = (int) ($profileUser['user_id'] ?? 0);

    $currentRole = strtolower(trim($currentUser['role_name'] ?? ''));
    $profileRole = strtolower(trim($profileUser['role_name'] ?? ''));

    if ( ! isAdminOrSupervisor()) {
        return false;
    }

    if ($currentUserId === $profileUserId) {
        return false;
    }

    if ($currentRole === 'supervisor' && $profileRole === 'admin') {
        return false;
    }

    return true;
}

/**
 * Update a user's profile details.
 * This includes name, email, phone, avatar,
 * and password if the account owner is changing it.
 */
function updateProfile($currentUser, $profileUserId, $post, $files) {
    global $pdo;

    $profileUser = findProfileUserById($profileUserId);

    if ( ! $profileUser) {
        return [
            'success' => false,
            'errors' => ['User profile not found.'],
        ];
    }

    if (!canEditProfile($currentUser, $profileUser)) {
        return [
            'success' => false,
            'errors' => ['You are not allowed to edit that profile.'],
        ];
    }

    $errors = [];

    $firstName = trim($post['first_name'] ?? '');
    $lastName = trim($post['last_name'] ?? '');
    $email = trim($post['email'] ?? '');
    $phone = trim($post['phone'] ?? '');

    $currentPassword = $post['current_password'] ?? '';
    $newPassword = $post['new_password'] ?? '';
    $confirmPassword = $post['confirm_password'] ?? '';

    // Validate basic profile fields
    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '') $errors[] = 'Last name is required.';
    
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Check if another user already has this email
    if ($email !== $profileUser['email'] && emailBelongsToAnotherUser($email, $profileUserId)) {
        $errors[] = 'That email address is already in use.';
    }

    $isOwnProfile = (int) ($currentUser['user_id'] ?? 0) === $profileUserId;
    $isTryingToChangePassword = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';

    // Password change rules
    if ( $isTryingToChangePassword ) {
        if (!$isOwnProfile) {
            $errors[] = 'Only the account owner can change this password here.';
        } else {
            if ($currentPassword === '') {
                $errors[] = 'Enter your current password to change it.';
            } elseif (!password_verify($currentPassword, $profileUser['password_hash'])) {
                $errors[] = 'Your current password is incorrect.';
            }

            if ($newPassword === '') {
                $errors[] = 'Enter a new password.';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'Your new password must be at least 8 characters long.';
            }

            if ($confirmPassword === '') {
                $errors[] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            }
        }
    }

    // Keep the old avatar unless a new one is uploaded
    $avatarPath = $profileUser['avatar_path'];

    if ( ! empty($files['avatar']['name'])) {
        $uploadResult = uploadProfileAvatar($files['avatar'], $profileUserId);

        if (!$uploadResult['success']) {
            $errors = array_merge($errors, $uploadResult['errors']);
        } else {
            $avatarPath = $uploadResult['path'];
        }
    }

    // Return validation errors if any exist
    if ( ! empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors,
        ];
    }

    // Keep the old password unless the owner changes it successfully
    $passwordHash = $profileUser['password_hash'];

    if ($isOwnProfile && $isTryingToChangePassword && $newPassword !== '' && $newPassword === $confirmPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Save updated profile data
    $stmt = $pdo->prepare("
        UPDATE user
        SET
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            phone = :phone,
            avatar_path = :avatar_path,
            password_hash = :password_hash
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone !== '' ? $phone : null,
        'avatar_path' => $avatarPath !== '' ? $avatarPath : null,
        'password_hash' => $passwordHash,
        'user_id' => $profileUserId,
    ]);

    return [
        'success' => true,
        'errors' => [],
        'updated_session_user' => $isOwnProfile ? [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ] : [],
    ];
}

/**
 * Delete a user profile if the current user has permission.
 */
function deleteProfile($currentUser, $profileUserId) {
    global $pdo;

    $profileUser = findProfileUserById($profileUserId);

    // Stop if the profile does not exist
    if ( ! $profileUser) {
        return [
            'success' => false,
            'errors' => ['User profile not found.'],
        ];
    }

    // Stop if the user is not allowed to delete this profile
    if ( ! canDeleteProfile($currentUser, $profileUser)) {
        return [
            'success' => false,
            'errors' => ['You are not allowed to delete that profile.'],
        ];
    }

    $stmt = $pdo->prepare("
        DELETE FROM user
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute(['user_id' => $profileUserId]);

    return [
        'success' => true,
        'errors' => [],
    ];
}

/**
 * Check if an email address already belongs to another user.
 * This helps prevent duplicate emails in the system.
 */
function emailBelongsToAnotherUser($email, $userId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM user
        WHERE email = :email
          AND user_id != :user_id
        LIMIT 1
    ");

    $stmt->execute([
        'email' => $email,
        'user_id' => $userId,
    ]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Upload a profile avatar image.
 * Only JPG, PNG, and WEBP files are allowed,
 * and the image must be 2MB or less.
 */
function uploadProfileAvatar($file, $userId) {
    $errors = [];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'errors' => ['Avatar upload failed.'],
        ];
    }

    $tmpPath = $file['tmp_name'] ?? '';
    $fileSize = (int) ($file['size'] ?? 0);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mimeType = mime_content_type($tmpPath);

    // Validate file type
    if ( ! isset($allowedTypes[$mimeType])) {
        $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
    }

    // Validate file size
    if ($fileSize > 2 * 1024 * 1024) {
        $errors[] = 'Avatar image must be 2MB or less.';
    }

    if ( ! empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors,
        ];
    }

    $extension = $allowedTypes[$mimeType];
    $fileName = 'user-' . $userId . '-' . time() . '.' . $extension;

    $uploadFolder = __DIR__ . '/../../uploads/avatars/';
    $relativePath = 'uploads/avatars/' . $fileName;
    $destination = $uploadFolder . $fileName;

    if (!is_dir($uploadFolder)) {
        mkdir($uploadFolder, 0777, true);
    }

    // Move uploaded file into the avatar folder
    if ( ! move_uploaded_file($tmpPath, $destination)) {
        return [
            'success' => false,
            'errors' => ['Unable to save uploaded avatar.'],
        ];
    }

    return [
        'success' => true,
        'errors' => [],
        'path' => $relativePath,
    ];
}