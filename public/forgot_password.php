<?php
  
    require_once __DIR__ . '/../app/session.php';
    require_once __DIR__ . '/../app/auth.php';
    require_once __DIR__ . '/../app/functions.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    requireGuest();

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    $featuredMovies = getHomePageData($pdo)['featuredMovies'];
    $isLoggedIn = isLoggedIn();

    $pageTitle = '876 Screens - Forgot Password';
    $activePage = 'forgot password';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>

        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper forgot-password-page">
                
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="forgot-password-section">
                    <div class="forgot-password-card">
                        <h2 class="forgot-password-heading">WELCOME BACK</h2>
                        <p class="forgot-password-eyebrow">876 Screens Access</p>

                        <p class="forgot-password-description">
                            Forgot your password? No worries. Enter the email address linked to your
                            876 Screens account and we’ll send you a reset link so you can get back
                            to managing bookings, checking showtimes, and enjoying a smoother cinema experience.
                        </p>

                        <form class="forgot-password-form">
                            <div class="form-group">
                                <label for="reset-email" class="required">Email Address</label>
                                <input
                                    type="email"
                                    id="reset-email"
                                    name="reset-email"
                                    placeholder="you@example.com"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-secondary forgot-password-btn">Send Reset Link</button>
                        </form>

                        <div class="forgot-password-bottom">
                            <a href="<?= $basePath ?>public/login.php" class="forgot-link-back">Back to Login</a>
                            <p class="forgot-register-text">
                                Don’t have an account?
                                <a href="<?= $basePath ?>public/register.php">Register here.</a>
                            </p>
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