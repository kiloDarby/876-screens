<?php

    require_once __DIR__ . '/../app/functions.php';
    require_once __DIR__ . '/../app/session.php';
    require_once __DIR__ . '/../app/auth.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    requireRole(['admin']);

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $featuredMovies = getHomePageData($pdo)['featuredMovies']; //Fetch featured movies (used in hero section)
    $currentUser = currentUser();
    $isLoggedIn = isLoggedIn();

    //Retrieve previously entered form values (for repopulating form on error)
    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    $pageTitle = '876 Screens - Create User';
    $activePage = 'manage_users';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>

    <body>
        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>

            <div class="shadow-wrapper profile-wrapper create-user-wrapper">
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="profile-section create-user-section">
                    <div class="profile-shell create-user-shell">
                        <div class="profile-summary create-user-summary">
                            <p class="profile-kicker">876 Screens Admin</p>
                            <h1>Create User</h1>
                            <p class="profile-lead">
                                Add a new user account, assign a role, and set up login details
                                from one clean page.
                            </p>

                            <div class="profile-overview-card create-user-overview">
                                <div class="profile-avatar">
                                    <span>NU</span>
                                </div>

                                <div class="profile-overview-text">
                                    <h2>New User Account</h2>
                                    <p>Create admin, supervisor, or regular user accounts.</p>
                                </div>
                            </div>

                            <div class="profile-highlights">
                                <article class="profile-highlight-card">
                                    <h2>Role Setup</h2>
                                    <p>Assign the correct access level before saving the account.</p>
                                </article>

                                <article class="profile-highlight-card">
                                    <h2>Avatar</h2>
                                    <p>The user will upload their own preferred profile picture after account creation.</p>
                                </article>

                                <article class="profile-highlight-card">
                                    <h2>Quick Access</h2>
                                    <p>Once created, the new account will appear in Manage Users.</p>
                                </article>
                            </div>
                        </div>

                        <div class="profile-card create-user-card">
                            <div class="profile-card-header">
                                <h2>New User Details</h2>
                                <p>Fill in the fields below to create a new account.</p>
                            </div>

                            <form
                                class="profile-form create-user-form"
                                id="create-user-form"
                                action="<?= url('app/handlers/create_user_handler.php') ?>"
                                method="post"
                            >
                                <div class="form-group">
                                    <label>Profile Avatar</label>

                                    <div class="profile-upload">
                                        <div class="profile-upload-preview">
                                            <span>NU</span>
                                        </div>

                                        <div class="profile-upload-fields">
                                            <p class="profile-upload-note">
                                                Avatar is left empty on account creation. The user will upload their own photo from their profile page.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-row">
                                    <div class="form-group">
                                        <label for="first-name">First Name</label>
                                        <input
                                            type="text"
                                            id="first-name"
                                            name="first_name"
                                            value="<?= htmlspecialchars($oldInput['first_name'] ?? '') ?>"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="last-name">Last Name</label>
                                        <input
                                            type="text"
                                            id="last-name"
                                            name="last_name"
                                            value="<?= htmlspecialchars($oldInput['last_name'] ?? '') ?>"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="profile-row">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>"
                                        >
                                    </div>
                                </div>

                                <div class="profile-row">
                                    <div class="form-group">
                                        <label for="role-id">Role</label>
                                        <select id="role-id" name="role_id" class="role-select" required>
                                            <option value="">Select role</option>
                                            <option value="1" <?= (($oldInput['role_id'] ?? '') === '1') ? 'selected' : '' ?>>Admin</option>
                                            <option value="2" <?= (($oldInput['role_id'] ?? '') === '2') ? 'selected' : '' ?>>Supervisor</option>
                                            <option value="3" <?= (($oldInput['role_id'] ?? '') === '3') ? 'selected' : '' ?>>User</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="is_banned" class="role-select" required>
                                            <option value="0" <?= (($oldInput['is_banned'] ?? '0') === '0') ? 'selected' : '' ?>>Active</option>
                                            <option value="1" <?= (($oldInput['is_banned'] ?? '') === '1') ? 'selected' : '' ?>>Banned</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="profile-divider">
                                    <h3>Login Setup</h3>
                                    <p>Set the password for the new user account below.</p>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        placeholder="Enter password"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="confirm-password">Confirm Password</label>
                                    <input
                                        type="password"
                                        id="confirm-password"
                                        name="confirm_password"
                                        placeholder="Confirm password"
                                        required
                                    >
                                </div>

                                <div class="profile-actions create-user-actions">
                                    <a href="./manage_users.php" class="btn btn-tertiary create-user-cancel">
                                        Back to Manage Users
                                    </a>

                                    <button type="submit" class="btn btn-secondary profile-submit create-user-submit">
                                        Create User
                                    </button>
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