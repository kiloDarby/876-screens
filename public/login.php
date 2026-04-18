<?php
  
    require_once __DIR__ . '/../app/functions.php';
    require_once __DIR__ . '/../app/session.php';
    require_once __DIR__ . '/../app/auth.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    requireGuest();

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $isLoggedIn = isLoggedIn();

    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    $featuredMovies = getHomePageData($pdo)['featuredMovies'];

    $pageTitle = '876 Screens - Login';
    $activePage = 'login';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>

        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper login-wrapper">
                
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="login-section">
                    <div class="login-card">
                        <div class="login-card-header">
                            <h2>Welcome back</h2>
                            <p class="login-kicker">876 Screens Access</p>
                            <p class="login-lead">
                                Sign in to your 876 Screens account to manage your bookings, stay updated on
                                showtimes, and enjoy a smoother cinema experience.
                            </p>
                        </div>

                        <form class="login-form" action="<?= url('app/handlers/login_handler.php') ?>" method="post">
                            <div class="form-group">
                                <label for="login-email" class="required">Email Address</label>
                                <input
                                    type="email"
                                    id="login-email"
                                    name="email"
                                    placeholder="you@example.com"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="login-password" class="required">Password</label>
                                <input
                                    type="password"
                                    id="login-password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >
                            </div>

                            <div class="login-options">
                                <label class="checkbox-row" style="visibility:hidden">
                                    <input type="checkbox" name="remember_me">
                                    <span>Remember me</span>
                                </label>

                                <a href="../public/forgot_password.php" class="forgot-password">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn btn-secondary login-submit">Login</button>

                            <p class="login-register-link">
                                Don’t have an account?
                                <a href="../public/register.php">Register here.</a>
                            </p>
                        </form>
                    </div>
                    <?php require_once __DIR__ . '/../partials/modals.php'; ?>  
                </section>

              <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>    

            </div>
        </div>
        <?php require_once __DIR__ . '/../partials/scripts.php'; ?>  
    </body>
</html>