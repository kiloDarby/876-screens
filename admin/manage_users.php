<?php

    require_once __DIR__ . '/../app/auth.php';

    requireAdminOrSupervisor();

    require_once __DIR__ . '/../app/handlers/manage_users_handler.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    $featuredMovies = getHomePageData($pdo)['featuredMovies'];
    $currentUser = currentUser();
    $isLoggedIn = isLoggedIn();

    $pageTitle = '876 Screens - Manage Users';
    $activePage = 'manage_users';

    //echo '<pre>'; var_dump($users); exit;
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>
        
        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper manage-users-page">
                
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="manage-users-section">
                    <div class="manage-users-shell">

                        <form class="manage-users-form" action="<?= url('admin/manage_users.php') ?>" method="post">

                            <div class="manage-users-toolbar">
                                <div class="manage-users-search">
                                    <label for="userSearch" class="hide-elm">Search users</label>
                                    <input
                                        type="search"
                                        id="userSearch"
                                        name="userSearch"
                                        value="<?= htmlspecialchars($search) ?>"
                                        placeholder="Search by name or email"
                                        form="manage-users-search-form"
                                    >
                                    <button type="submit" class="btn btn-secondary">Search</button>
                                </div>

                                <div class="manage-users-actions">
                                    <a href="<?= url('admin/create_user.php') ?>" class="btn btn-tertiary add-user-btn">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Add User</span>
                                    </a>
                                </div>
                            </div>

                            <div class="manage-users-list-wrap">
                                <div class="manage-users-grid-head">
                                    <span>User</span>
                                    <span>Current Role</span>
                                    <span>Update Role</span>
                                    <span>Status</span>
                                    <span>View</span>
                                    <span>Ban/Unban</span>
                                    <span>Delete</span>
                                </div>

                                <div class="manage-users-list">
                                    <?php if (empty($users)): ?>
                                        <article class="manage-user-row">
                                            <div class="manage-user-grid">
                                                <div class="manage-user-col user-col-main">
                                                    <div class="user-cell">
                                                        <div class="user-meta">
                                                            <span class="user-name">No users found.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <?php
                                            $userId = (int) $user['user_id'];
                                            $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
                                            $initials = buildUserInitials($user['first_name'], $user['last_name']);
                                            $isBanned = (int) $user['is_banned'] === 1;
                                            $isCurrentUser = $userId === (int) ($_SESSION['user']['user_id'] ?? 0);
                                            ?>
                                            <article class="manage-user-row">
                                                <div class="manage-user-grid">

                                                    <div class="manage-user-col user-col-main">
                                                        <div class="user-cell">
                                                            <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
                                                            <div class="user-meta">
                                                                <span class="user-name"><?= htmlspecialchars($fullName) ?></span>
                                                                <span class="user-id">
                                                                    User ID: <?= str_pad((string) $userId, 3, '0', STR_PAD_LEFT) ?>
                                                                </span>
                                                                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="manage-user-col">
                                                        <span class="current-role-text"><?= htmlspecialchars($user['role_name']) ?></span>
                                                    </div>

                                                    <div class="manage-user-col">
                                                        <select
                                                            name="users[<?= $userId ?>][role_id]"
                                                            class="role-select"
                                                        >
                                                            <?php foreach ($roles as $role): ?>
                                                                <option
                                                                    value="<?= (int) $role['role_id'] ?>"
                                                                    <?= (int) $role['role_id'] === (int) $user['role_id'] ? 'selected' : '' ?>
                                                                >
                                                                    <?= htmlspecialchars($role['role_name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="manage-user-col">
                                                        <span class="status-badge <?= $isBanned ? 'status-banned' : 'status-active' ?>">
                                                            <?= $isBanned ? 'Banned' : 'Active' ?>
                                                        </span>
                                                    </div>

                                                    <div class="manage-user-col">
                                                        <a href="<?= url('public/profile.php?user_id=' . $userId) ?>" class="view-link">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                    </div>

                                                    <div class="manage-user-col">
                                                        <label class="action-toggle">
                                                            <input type="checkbox" name="users[<?= $userId ?>][toggle_ban]">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </label>
                                                    </div>

                                                    <div class="manage-user-col">
                                                        <?php if ($isCurrentUser): ?>
                                                            <span class="current-user-protection">Current User</span>
                                                        <?php else: ?>
                                                            <label class="action-toggle action-delete">
                                                                <input type="checkbox" name="users[<?= $userId ?>][delete]">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </label>
                                                        <?php endif; ?>
                                                    </div>

                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="manage-users-bottom-actions">
                                <a href="<?= url('admin/manage_users.php') ?>" class="btn btn-tertiary">Discard Changes</a>
                                <button type="submit" class="btn btn-secondary">Submit Updates</button>
                            </div>

                        </form>

                        <form id="manage-users-search-form" action="<?= url('admin/manage_users.php') ?>" method="get" hidden></form>

                    </div>
                    <?php require_once __DIR__ . '/../partials/modals.php'; ?>  
                </section>

              <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>    

            </div>
        </div>

        <?php require_once __DIR__ . '/../partials/scripts.php'; ?>  

    </body>
</html>