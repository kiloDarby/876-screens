<?php
  
    require_once __DIR__ . '/../app/session.php';
    require_once __DIR__ . '/../app/auth.php';
    require_once __DIR__ . '/../app/functions.php';
    require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    //requireGuest();

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    $featuredMovies = getHomePageData($pdo)['featuredMovies'];
    $currentUser = currentUser();
    $isLoggedIn = isLoggedIn();

    // echo '<pre>';
    // var_dump($currentUser);
    // echo '<pre>'; exit;

    $pageTitle = '876 Screens - Contact Us';
    $activePage = 'contact';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>
        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">

            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
            <div class="shadow-wrapper contact-wrapper">
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="contact-section">
                    <div class="contact-intro">
                        <p class="contact-kicker">Get In Touch</p>
                        <h2>Contact Us</h2>
                        <p class="contact-lead">
                            Have a question about tickets, showtimes, cinemas, or your account?
                            Send us a message and we’ll get back to you as soon as possible.
                        </p>
                    </div>

                    <div class="contact-shell">
                        <div class="contact-info">
                            <article class="contact-card">
                                <h3>Email</h3>
                                <p>support@876screens.com</p>
                            </article>

                            <article class="contact-card">
                                <h3>Phone</h3>
                                <p>(876) 555-1234</p>
                            </article>

                            <article class="contact-card">
                                <h3>Hours</h3>
                                <p>Monday - Sunday<br>9:00 AM - 11:00 PM</p>
                            </article>

                            <article class="contact-card">
                                <h3>Support</h3>
                                <p>Booking help, account support, and general inquiries.</p>
                            </article>
                        </div>

                        <div class="contact-form-card">
                            <div class="contact-form-header">
                                <h3>Send a Message</h3>
                                <p>Fill out the form below and we’ll respond as soon as we can.</p>
                            </div>

                            <form class="contact-form" action="#" method="post">
                                <div class="contact-row">
                                    <div class="form-group">
                                        <label for="contact-first-name" class="required">First Name</label>
                                        <input
                                            type="text"
                                            id="contact-first-name"
                                            name="first_name"
                                            value="<?= $currentUser['first_name'] ?? '' ?>"
                                            placeholder="John"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="contact-last-name" class="required">Last Name</label>
                                        <input
                                            type="text"
                                            id="contact-last-name"
                                            name="last_name"
                                            value="<?= $currentUser['last_name'] ?? '' ?>"
                                            placeholder="Brown"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="contact-email" class="required">Email Address</label>
                                    <input
                                        type="email"
                                        id="contact-email"
                                        name="email"
                                        placeholder="you@example.com"
                                        value="<?= $currentUser['email'] ?? '' ?>"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="contact-subject" class="required">Subject</label>
                                    <input
                                        type="text"
                                        id="contact-subject"
                                        name="subject"
                                        placeholder="How can we help?"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="contact-message" class="required">Message</label>
                                    <textarea
                                        id="contact-message"
                                        name="message"
                                        placeholder="Write your message here..."
                                        rows="6"
                                        required
                                    ></textarea>
                                </div>

                                <button type="submit" class="btn btn-secondary contact-submit">Send Message</button>
                            </form>
                        </div>
                    </div>
                </section>

                <?php require_once __DIR__ . '/../partials/modals.php'; ?>
                <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>
            </div>
            <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
        </div>
    </body>
</html>