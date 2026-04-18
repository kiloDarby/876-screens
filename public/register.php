<?php
  
    require_once __DIR__ . '/../app/session.php';
    require_once __DIR__ . '/../app/auth.php';
    require_once __DIR__ . '/../app/functions.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    //requireGuest();

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $featuredMovies = getHomePageData($pdo)['featuredMovies'];
    $currentUser = currentUser();
    $isLoggedIn = isLoggedIn();

    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    $pageTitle = '876 Screens - Register';
    $activePage = 'register';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>

        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper register-wrapper">
                
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="register-section">
                    <div class="register-shell">
                        <div class="register-copy">
                            <p class="register-kicker">876 Screens Membership</p>
                            <h1>Create your account</h1>
                            <p class="register-lead">
                                Join 876 Screens to book tickets faster, manage your details, and stay ready for
                                what’s showing today, this week, and this month.
                            </p>

                            <div class="register-perks">
                                <article class="perk-card">
                                    <h2>Fast checkout</h2>
                                    <p>Save time when booking your movie tickets.</p>
                                </article>

                                <article class="perk-card">
                                    <h2>Movie access</h2>
                                    <p>Stay close to showtimes, releases, and cinema updates.</p>
                                </article>

                                <article class="perk-card">
                                    <h2>Easy account management</h2>
                                    <p>Keep your personal info organized in one place.</p>
                                </article>
                            </div>
                        </div>

                        <div class="register-card">
                            <?php if (!empty($errors)): ?>
                                <div class="form-alert form-alert-error">
                                    <ul>
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="register-card-header">
                                <h2>Register</h2>
                                <p>Create your 876 Screens account below.</p>
                            </div>

                            <form id="register-form" class="register-form" action="<?= url('app/handlers/register_handler.php') ?>" method="post">
                                <div class="register-row">
                                    <div class="form-group">
                                        <label for="first-name" class="required">First Name</label>
                                        <input type="text" id="first-name" name="first_name" placeholder="John" 
                                            value="<?php echo htmlspecialchars($oldInput['first_name'] ?? ''); ?>" required> <!--  required -->
                                    </div>

                                    <div class="form-group">
                                        <label for="last-name" class="required">Last Name</label>
                                        <input type="text" id="last-name" name="last_name" placeholder="Brown" 
                                            value="<?php echo htmlspecialchars($oldInput['last_name'] ?? ''); ?>" required> <!--  required -->
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="required">Email Address</label>
                                    <input type="email" id="email" name="email" placeholder="you@example.com" 
                                        value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" required> <!--  required -->
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="required">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" placeholder="(876) 555-1234" 
                                        value="<?php echo htmlspecialchars($oldInput['phone'] ?? ''); ?>" required> <!--  required -->
                                </div>

                                <div class="form-group">
                                    <label for="password" class="required">Password</label>
                                    <input type="password" id="password" name="password" placeholder="Create a password" required> <!--  required -->
                                </div>

                                <div class="form-group">
                                    <label for="confirm-password" class="required">Confirm Password</label>
                                    <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required> <!--  required -->
                                </div>

                                <div class="form-options">
                                    <label class="checkbox-row">
                                        <input type="checkbox" name="terms" required> <!--  required -->
                                        <span class="required">I agree to the Terms &amp; Conditions and Privacy Policy.</span>
                                    </label>

                                    <label class="checkbox-row">
                                        <input type="checkbox" name="updates">
                                        <span>Send me updates about movies, offers, and showtimes.</span>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-secondary register-submit">Create Account</button>

                                <p class="register-login-link">
                                    Already have an account?
                                    <a href="<?= url('public/login.php') ?>">Login here.</a>
                                </p>
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