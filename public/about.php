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

    $pageTitle = '876 Screens - About Us';
    $activePage = 'about';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>
        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">

            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
            <div class="shadow-wrapper about-wrapper">
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="about-section">
                    <div class="about-intro">
                        <p class="about-kicker">Who We Are</p>
                        <h2>About 876 Screens</h2>
                        <p class="about-lead">
                            876 Screens is a modern cinema platform designed to make it easy for users to discover movies,
                            explore schedules, and purchase tickets online. We bring together convenience, strong visual
                            design, and a smooth booking flow to create a better cinema experience.
                        </p>
                    </div>

                    <div class="about-grid">
                        <article class="about-card">
                            <h3>Our Mission</h3>
                            <p>
                                To make movie-going easier and more enjoyable by giving users a clean, fast, and reliable way
                                to find films and book tickets.
                            </p>
                        </article>

                        <article class="about-card">
                            <h3>What We Offer</h3>
                            <p>
                                Users can explore what’s showing today, this week, and this month, then choose a showtime and
                                purchase tickets in a simple, modern flow.
                            </p>
                        </article>

                        <article class="about-card">
                            <h3>Why 876 Screens</h3>
                            <p>
                                We aim to combine the excitement of cinema with the convenience of digital booking, while
                                keeping the platform visually cinematic and easy to use.
                            </p>
                        </article>
                    </div>

                    <div class="about-story">
                        <div class="about-story-block">
                            <p class="about-kicker">The Experience</p>
                            <h3>Built for a smoother movie journey</h3>
                            <p>
                                From browsing upcoming releases to securing your seat, 876 Screens is designed to reduce
                                friction and make every step feel clear and enjoyable. The goal is not just to sell tickets,
                                but to create a platform that feels modern, premium, and connected to the cinema experience.
                            </p>
                        </div>

                        <div class="about-story-block">
                            <p class="about-kicker">The Vision</p>
                            <h3>Growing cinema access digitally</h3>
                            <p>
                                876 Screens is built around the idea that cinema platforms should feel exciting, accessible,
                                and easy to navigate on any device. Whether users are checking what’s on today or planning a
                                movie outing for later in the month, the experience should feel seamless.
                            </p>
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