<?php

    require_once __DIR__ . '/../app/functions.php';
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../app/auth.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';
    require_once __DIR__ . '/../app/handlers/profile_handler.php';

    requireLogin();

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $currentUser = currentUser();
    $isLoggedIn = isLoggedIn();
    $featuredMovies = getHomePageData($pdo)['featuredMovies'];
    $requestedUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

    $pageData = getProfilePageData($currentUser, $requestedUserId);

    $profileUser = $pageData['profile_user'];
    $profileView = $pageData['view'];
    $isOwnProfile = $pageData['is_own_profile'];
    $canEdit = $pageData['can_edit'];
    $canDelete = $pageData['can_delete'];

    if ( ! $pageData['success'] ) {
        $_SESSION['errors'] = $pageData['errors'];

        if ($requestedUserId !== null && isAdminOrSupervisor()) {
            header('Location: ' . url('admin/manage_users.php'));
            exit;
        }

        header('Location: ' . url('public/index.php'));
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $result = deleteProfile($currentUser, (int) $profileUser['user_id']);

            if (!$result['success']) {
                $_SESSION['errors'] = $result['errors'];

                $redirectUrl = $isOwnProfile ? url('public/profile.php') : url('public/profile.php?user_id=' . (int) $profileUser['user_id']);

                header('Location: ' . $redirectUrl);
                exit;
            }

            $_SESSION['success'] = 'Profile deleted successfully.';
            header('Location: ' . url('admin/manage_users.php'));
            exit;
        }

        $result = updateProfile($currentUser, (int) $profileUser['user_id'], $_POST, $_FILES);

        if (!$result['success']) {
            $_SESSION['errors'] = $result['errors'];

            $redirectUrl = $isOwnProfile
                ? url('public/profile.php')
                : url('public/profile.php?user_id=' . (int) $profileUser['user_id']);

            header('Location: ' . $redirectUrl);
            exit;
        }

        if (!empty($result['updated_session_user'])) {
            $_SESSION['user'] = array_merge($_SESSION['user'], $result['updated_session_user']);
        }

        $_SESSION['success'] = 'Profile updated successfully.';

        $redirectUrl = $isOwnProfile
            ? url('public/profile.php')
            : url('public/profile.php?user_id=' . (int) $profileUser['user_id']);

        header('Location: ' . $redirectUrl);
        exit;
    }

    $pageTitle = '876 Screens - Profile';
    $activePage = 'profile';

?>

<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../partials/head.php'; ?>
<body>

    <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

    <div class="page">
        <?php require_once __DIR__ . '/../partials/page_header.php'; ?>

        <div class="shadow-wrapper profile-wrapper">
            <?php require_once __DIR__ . '/../partials/hero.php'; ?>

            <section class="profile-section">
                <div class="profile-shell">
                    <div class="profile-summary">
                        <p class="profile-kicker">876 Screens Account</p>

                        <h1><?= $isOwnProfile ? 'My Profile' : 'User Profile'; ?></h1>

                        <p class="profile-lead">
                            View your account details, update your personal information, and keep your profile ready
                            for faster ticket booking.
                        </p>

                        <div class="profile-overview-card">
                            <div class="profile-avatar">
                                <?php if (!empty($profileView['avatar_url'])): ?>
                                    <img
                                        src="<?= htmlspecialchars($profileView['avatar_url']); ?>"
                                        alt="<?= htmlspecialchars($profileView['full_name']); ?>"
                                    >
                                <?php else: ?>
                                    <span><?= htmlspecialchars($profileView['initials']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="profile-overview-text">
                                <h2><?= htmlspecialchars($profileView['full_name']); ?></h2>
                                <p>Member since <?= htmlspecialchars($profileView['member_since']); ?></p>
                            </div>
                        </div>

                        <div class="profile-highlights">
                            <article class="profile-highlight-card">
                                <h2>Email</h2>
                                <p><?= htmlspecialchars($profileUser['email']); ?></p>
                            </article>

                            <article class="profile-highlight-card">
                                <h2>Phone</h2>
                                <p><?= htmlspecialchars($profileUser['phone'] ?: 'N/A'); ?></p>
                            </article>

                            <article class="profile-highlight-card">
                                <h2>Status</h2>
                                <p><?= htmlspecialchars($profileView['status_text']); ?></p>
                            </article>
                        </div>
                    </div>

                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2><?= $canEdit ? 'Edit Profile' : 'Profile Details'; ?></h2>
                            <p>
                                <?= $canEdit ? 'Update the details below.' : 'Viewing this user account.'; ?>
                            </p>
                        </div>

                        <form class="profile-form" id="profile-form" action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="avatar">Upload Avatar / Photo</label>

                                <div class="profile-upload">
                                    <div class="profile-upload-preview">
                                        <?php if (!empty($profileView['avatar_url'])): ?>
                                            <img
                                                src="<?= htmlspecialchars($profileView['avatar_url']); ?>"
                                                alt="Current profile picture"
                                            >
                                        <?php else: ?>
                                            <span><?= htmlspecialchars($profileView['initials']); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="profile-upload-fields">
                                        <?php if ($canEdit): ?>
                                            <input type="file" id="avatar" name="avatar" accept="image/*">
                                            <p class="profile-upload-note">Upload a clear profile photo.</p>
                                        <?php else: ?>
                                            <p class="profile-upload-note">You do not have permission to update this profile photo.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-row">
                                <div class="form-group">
                                    <label for="first-name" class="required">First Name</label>
                                    <input
                                        type="text"
                                        id="first-name"
                                        name="first_name"
                                        value="<?= htmlspecialchars($profileUser['first_name']); ?>"
                                        required
                                        <?= $canEdit ? '' : 'readonly'; ?>
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="last-name" class="required">Last Name</label>
                                    <input
                                        type="text"
                                        id="last-name"
                                        name="last_name"
                                        value="<?= htmlspecialchars($profileUser['last_name']); ?>"
                                        required
                                        <?= $canEdit ? '' : 'readonly'; ?>
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="required">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($profileUser['email']); ?>"
                                    required
                                    <?= $canEdit ? '' : 'readonly'; ?>
                                >
                            </div>

                            <div class="form-group">
                                <label for="phone" class="required">Phone Number</label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="<?= htmlspecialchars($profileUser['phone'] ?? ''); ?>"
                                    <?= $canEdit ? '' : 'readonly'; ?>
                                >
                            </div>

                            <?php if ($isOwnProfile): ?>
                                <div class="profile-divider">
                                    <h3>Change Password</h3>
                                    <p>Leave these blank if you do not want to change your password.</p>
                                </div>

                                <div class="form-group">
                                    <label for="current-password">Current Password</label>
                                    <input
                                        type="password"
                                        id="current-password"
                                        name="current_password"
                                        placeholder="Enter current password"
                                    >
                                </div>

                                <div class="profile-row">
                                    <div class="form-group">
                                        <label for="new-password">New Password</label>
                                        <input
                                            type="password"
                                            id="new-password"
                                            name="new_password"
                                            placeholder="Enter new password"
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm-password">Confirm New Password</label>
                                        <input
                                            type="password"
                                            id="confirm-password"
                                            name="confirm_password"
                                            placeholder="Confirm new password"
                                        >
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="profile-actions">
                                <?php if (!$isOwnProfile && isAdminOrSupervisor()): ?>
                                    <a href="<?= url('admin/manage_users.php') ?>" class="btn btn-tertiary">Back to Manage Users</a>
                                <?php endif; ?>

                                <?php if ($canEdit): ?>
                                    <button
                                        type="submit"
                                        name="action"
                                        value="save"
                                        class="btn btn-secondary profile-submit"
                                    >
                                        Save Changes
                                    </button>
                                <?php endif; ?>

                                <?php if ($canDelete): ?>
                                    <button
                                        type="submit"
                                        name="action"
                                        value="delete"
                                        class="btn btn-danger profile-delete"
                                        onclick="return confirm('Are you sure you want to delete this profile?');"
                                    >
                                        Delete Profile
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php require_once __DIR__ . '/../partials/modals.php'; ?>
            </section>

            <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>