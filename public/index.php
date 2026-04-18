<?php
  
  require_once __DIR__ . '/../app/session.php';
  require_once __DIR__ . '/../app/auth.php';
  require_once __DIR__ . '/../app/functions.php';
  require_once __DIR__ . '/../app/handlers/homepage_handler.php';

  $errors = getFlashErrors();
  $success = getFlashSuccess();

  $homeData = getHomePageData($pdo);
  $currentUser = currentUser();
  $isLoggedIn = isLoggedIn();

//   echo '<pre>';
//   var_dump($currentUser);
//   echo '</pre>'; exit;

  $featuredMovies = $homeData['featuredMovies'];
  $nowShowingMovies = $homeData['nowShowingMovies'];
  $comingSoonMovies = $homeData['comingSoonMovies'];

  $pageTitle = '876 Screens';
  $activePage = 'home';
  $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>

        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper">
                
              <?php require_once __DIR__ . '/../partials/hero.php'; ?>

              <section class="now-showing">
                <div class="section-title">
                    <h2>Now Showing</h2>
                    <span class="line"></span>
                </div>

                <div class="movie-grid">
                    <?php if (!empty($nowShowingMovies)): ?>
                        <?php foreach ($nowShowingMovies as $movie): ?>
                            <article class="movie-card">
                                <div class="movie-image">
                                    <img
                                        width="248"
                                        height="380"
                                        src="../<?= htmlspecialchars($movie['image_url']) ?>"
                                        alt="<?= htmlspecialchars($movie['title']) ?>"
                                    >
                                </div>

                                <div class="movie-content">
                                    <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>

                                    <div class="card-movie-meta">
                                        <span><?= htmlspecialchars(formatHomepageMovieDuration((int) $movie['duration_minute'])) ?></span>
                                        <span class="rating"><?= htmlspecialchars($movie['rating_code']) ?></span>
                                    </div>

                                    <div class="movie-actions">
                                        <?php if (!empty($movie['trailer_url'])): ?>
                                            <a
                                                href="#"
                                                class="btn btn-tertiary watch-trailer-btn"
                                                data-trailer="<?= htmlspecialchars($movie['trailer_url']) ?>"
                                                data-title="<?= htmlspecialchars($movie['title']) ?> Trailer"
                                            >
                                                Watch Trailer
                                            </a>
                                        <?php endif; ?>

                                        <?php if (isLoggedIn()): ?>
                                            <a
                                                href="#"
                                                class="btn btn-primary movie-btn buy-ticket"
                                                data-is-logged-in="<?= $isLoggedIn ? '1' : '0' ?>"
                                                data-movie-id="<?= (int) $movie['movie_id'] ?>"
                                                data-title="<?= htmlspecialchars($movie['title']) ?>"
                                                data-rating="<?= htmlspecialchars($movie['rating_code']) ?>"
                                                data-meta="<?= htmlspecialchars(formatMovieMeta($movie)) ?>"
                                                data-description="<?= htmlspecialchars($movie['description']) ?>"
                                                data-poster="<?= htmlspecialchars($movie['image_url'] ?: '../assets/images/placeholders/movie-poster.jpg') ?>"
                                            >
                                                Buy Ticket
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= url('public/login.php')?>" class="btn btn-primary movie-btn">Buy Ticket</a>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">No movies are currently showing.</p>
                    <?php endif; ?>
                </div>
                
                <?php require_once __DIR__ . '/../partials/modals.php'; ?>

              </section>

              <section class="coming-soon">
                <div class="section-title">
                    <h2>Coming Soon</h2>
                    <span class="line"></span>
                </div>

                <div class="carousel">
                    <button class="carousel-btn carousel-btn--left" title="Previous slide">‹</button>

                    <div class="carousel-track">
                        <?php if (!empty($comingSoonMovies)): ?>
                            <?php foreach ($comingSoonMovies as $movie): ?>
                                <article class="movie-slide">
                                    <img
                                        src="<?= '../' . htmlspecialchars($movie['image_url']) ?>"
                                        alt="<?= htmlspecialchars($movie['title']) ?>"
                                    >

                                    <div class="movie-overlay">
                                        <p class="movie-tag">Coming Soon</p>
                                        <h1 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h1>
                                        <p class="movie-meta">
                                            <?= htmlspecialchars(formatHomepageMovieDuration((int) $movie['duration_minute'])) ?>
                                            <?php if (!empty($movie['rating_code'])): ?>
                                                • <?= htmlspecialchars($movie['rating_code']) ?>
                                            <?php endif; ?>
                                        </p>

                                        <?php if (!empty($movie['trailer_url'])): ?>
                                            <a
                                                href="#"
                                                class="btn btn-primary movie-btn watch-trailer-btn"
                                                data-trailer="<?= htmlspecialchars($movie['trailer_url']) ?>"
                                                data-title="<?= htmlspecialchars($movie['title']) ?> Trailer"
                                            >
                                                Watch Trailer
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <article class="movie-slide">
                                <img
                                    src="../assets/images/hero-banner.jpg"
                                    alt="Coming soon"
                                >

                                <div class="movie-overlay">
                                    <p class="movie-tag">Coming Soon</p>
                                    <h1 class="movie-title">More Movies Soon</h1>
                                    <p class="movie-meta">New titles will appear here soon.</p>
                                </div>
                            </article>
                        <?php endif; ?>
                    </div>

                    <button class="carousel-btn carousel-btn--right" title="Next slide">›</button>
                </div>
            </section>

              <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>    

            </div>
        </div>
        <?php require_once __DIR__ . '/../partials/scripts.php'; ?>  
    </body>
</html>