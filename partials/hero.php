<section class="hero">
    <div class="carousel">
        <button class="carousel-btn carousel-btn--left" aria-label="Previous slide">‹</button>

        <div class="carousel-track">
            <?php if (!empty($featuredMovies)): ?>
                <?php foreach ($featuredMovies as $movie): ?>
                    <article class="movie-slide">
                        <img
                            src="<?= '../' . htmlspecialchars($movie['image_url']) ?>"
                            alt="<?= htmlspecialchars($movie['title']) ?>"
                        >

                        <div class="movie-overlay">
                            <p class="movie-tag">Featured Movie</p>
                            <h1 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h1>
                            <p class="movie-meta">
                                <?= htmlspecialchars(formatHomepageMovieDuration((int) $movie['duration_minute'])) ?>
                                <?php if (!empty($movie['rating_code'])): ?>
                                    • <?= htmlspecialchars($movie['rating_code']) ?>
                                <?php endif; ?>
                            </p>

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
                <article class="movie-slide">
                    <img src="../assets/images/hero-banner.jpg" alt="876 Screens">
                    <div class="movie-overlay">
                        <p class="movie-tag">Welcome to 876 Screens</p>
                        <h1 class="movie-title">Book Your Next Movie Night</h1>
                        <p class="movie-meta">Featured movies will appear here soon.</p>
                        <a href="./schedule.php" class="btn btn-primary movie-btn">Browse Schedule</a>
                    </div>
                </article>
            <?php endif; ?>
        </div>

        <button class="carousel-btn carousel-btn--right" aria-label="Next slide">›</button>
    </div>
</section>