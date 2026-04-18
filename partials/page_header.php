<header class="header">
    <nav class="site-nav">
        <a href="<?= $basePath ?>public/index.php" class="nav-logo">
            <img src="<?= $basePath ?>assets/images/logo.png" alt="876 Screens Logo">
        </a>

        <!-- Mobile nav -->
        <div class="nav-links-mobile">
            <a href="<?= $basePath ?>public/index.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
            <a href="<?= $basePath ?>public/schedule.php" class="nav-link <?= $activePage === 'schedule' ? 'active' : '' ?>">Schedule</a>

            <?php if (isLoggedIn()): ?>
                <a href="<?= $basePath ?>public/profile.php" class="nav-link <?= $activePage === 'profile' ? 'active' : '' ?>">Profile</a>
                <a href="<?= $basePath ?>public/logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="<?= $basePath ?>public/login.php" class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>">Login</a>
            <?php endif; ?>

            <button class="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-menu" type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- Desktop left -->
        <div class="nav-links-desktop nav-left">
            <a href="<?= $basePath ?>public/index.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
            <a href="<?= $basePath ?>public/schedule.php" class="nav-link <?= $activePage === 'schedule' ? 'active' : '' ?>">Schedule</a>
            <?php if (isAdminOrSupervisor()): ?>
                <a href="<?= $basePath ?>admin/manage_movies.php" class="nav-link <?= $activePage === 'manage_movies' ? 'active' : '' ?>">Manage Movies</a>
            <?php endif; ?>
        </div>

        <!-- Desktop right -->
        <div class="nav-links-desktop nav-right">
            <?php if (isLoggedIn()): ?>
                <!-- <a href="<?= $basePath ?>public/my-tickets.php" class="nav-link <?= $activePage === 'my_tickets' ? 'active' : '' ?>">My Tickets</a> -->

                <?php if (isAdminOrSupervisor()): ?>
                    <a href="<?= $basePath ?>admin/manage_users.php" class="nav-link <?= $activePage === 'manage_users' ? 'active' : '' ?>">Manage Users</a>
                <?php endif; ?>
                <a href="<?= $basePath ?>public/profile.php" class="nav-link <?= $activePage === 'profile' ? 'active' : '' ?>">Profile</a>
                <a href="<?= $basePath ?>public/logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="<?= $basePath ?>public/login.php" class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>">Login</a>
                <a href="<?= $basePath ?>public/register.php" class="nav-link <?= $activePage === 'register' ? 'active' : '' ?>">Register</a>
            <?php endif; ?>
        </div>

        <!-- Mobile dropdown menu -->
        <div class="menu-overlay"></div>
        <aside class="mobile-menu" id="mobile-menu">
            <button class="menu-close" aria-label="Close menu" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <nav class="mobile-menu-links">
                <a href="<?= $basePath ?>public/index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
                <a href="<?= $basePath ?>public/schedule.php" class="<?= $activePage === 'schedule' ? 'active' : '' ?>">Schedule</a>

                <?php if (isLoggedIn()): ?>
                    <a href="<?= $basePath ?>public/profile.php" class="<?= $activePage === 'profile' ? 'active' : '' ?>">Profile</a>
                    <a href="<?= $basePath ?>public/my-tickets.php" class="<?= $activePage === 'my_tickets' ? 'active' : '' ?>">My Tickets</a>

                    <?php if (isAdminOrSupervisor()): ?>
                        <a href="<?= $basePath ?>admin/manage_movies.php" class="<?= $activePage === 'manage_movies' ? 'active' : '' ?>">Manage Movies</a>
                        <a href="<?= $basePath ?>admin/manage_users.php" class="<?= $activePage === 'manage_users' ? 'active' : '' ?>">Manage Users</a>
                    <?php endif; ?>

                    <a href="<?= $basePath ?>public/logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?= $basePath ?>public/login.php" class="<?= $activePage === 'login' ? 'active' : '' ?>">Login</a>
                    <a href="<?= $basePath ?>public/register.php" class="<?= $activePage === 'register' ? 'active' : '' ?>">Register</a>
                <?php endif; ?>

                <a href="<?= $basePath ?>public/about.php" class="<?= $activePage === 'about' ? 'active' : '' ?>">About Us</a>
                <a href="<?= $basePath ?>public/contact.php" class="<?= $activePage === 'contact' ? 'active' : '' ?>">Contact Us</a>
            </nav>
        </aside>
    </nav>
</header>