<?php
  
  require_once __DIR__ . '/../config/app.php';
  require_once __DIR__ . '/../app/session.php';
  require_once __DIR__ . '/../app/auth.php';
  require_once __DIR__ . '/../app/functions.php';
  require_once __DIR__ . '/../app/handlers/schedule_handler.php';
  require_once __DIR__ . '/../app/handlers/homepage_handler.php';

    $errors = getFlashErrors();
    $success = getFlashSuccess();

    $homeData = getHomePageData($pdo);
    $featuredMovies = $homeData['featuredMovies'];
    $comingSoonMovies = $homeData['comingSoonMovies'];

    $currentUser = currentUser();
    $isLoggedIn = isLoggedIn();

    $pageTitle = '876 Screens - Movie Schedule';
    $activePage = 'schedule';
    $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>

        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper showtime-page">
                
                <?php require_once __DIR__ . '/../partials/hero.php'; ?>

                <section class="showtime-browser">
                    <div class="showtime-browser-inner">
                        <header class="showtime-browser-header">
                            <p class="section-kicker">876 Screens Schedule</p>
                            <h2>Browse Showtimes</h2>
                        </header>

                        <div class="showtime-layout">

                            <!-- Calendar Panel -->
                            <aside class="calendar-panel">
                                <div class="calendar-topbar">
                                    <?php if (!empty($pageData['previous_month'])): ?>
                                        <a
                                            class="calendar-nav"
                                            href="?month=<?php echo urlencode($pageData['previous_month']); ?>&date=<?php echo urlencode($pageData['selected_date']); ?>&cinema=<?php echo (int) $pageData['selected_cinema_id']; ?>"
                                            title="Previous month"
                                        >
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="calendar-nav is-disabled">
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </span>
                                    <?php endif; ?>

                                    <h3><?php echo htmlspecialchars($pageData['month_label']); ?></h3>

                                    <a
                                        class="calendar-nav"
                                        href="?month=<?php echo urlencode($pageData['next_month']); ?>&date=<?php echo urlencode($pageData['selected_date']); ?>&cinema=<?php echo (int) $pageData['selected_cinema_id']; ?>"
                                        title="Next month"
                                    >
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </div>

                                <div class="calendar-grid">
                                    <div class="calendar-day-name">Sun</div>
                                    <div class="calendar-day-name">Mon</div>
                                    <div class="calendar-day-name">Tue</div>
                                    <div class="calendar-day-name">Wed</div>
                                    <div class="calendar-day-name">Thu</div>
                                    <div class="calendar-day-name">Fri</div>
                                    <div class="calendar-day-name">Sat</div>

                                    <?php foreach ($pageData['calendar_days'] as $day): ?>
                                        <?php
                                            $dayClasses = ['calendar-day'];

                                            if (!$day['is_current_month']) {
                                              $dayClasses[] = 'is-muted';
                                            }

                                            if ($day['has_showtime']) {
                                              $dayClasses[] = 'has-showtime';
                                            }

                                            if ($day['is_active']) {
                                              $dayClasses[] = 'is-active';
                                            }

                                            if ($day['is_past_day']) {
                                              $dayClasses[] = 'is-disabled';
                                            }
                                            //var_dump($dayClasses); echo '<pre>';

                                            $dayUrl = '?month=' . urlencode($pageData['selected_month'])
                                                . '&date=' . urlencode($day['date'])
                                                . '&cinema=' . (int) $pageData['selected_cinema_id'];
                                        ?>

                                        <a
                                            href="<?php echo $dayUrl; ?>"
                                            class="<?php echo implode(' ', $dayClasses); ?>"
                                            title="<?php echo htmlspecialchars($day['date']); ?>"
                                        >
                                            <?php echo htmlspecialchars($day['day_number']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </aside>

                            <!-- Schedule Panel -->
                            <div class="schedule-panel">
                                <div class="schedule-panel-top">
                                    <div>
                                        <h3><?php echo htmlspecialchars($pageData['selected_heading']); ?></h3>
                                    </div>

                                    <form method="GET" class="schedule-filter">
                                        <input type="hidden" name="month" value="<?php echo htmlspecialchars($pageData['selected_month']); ?>">
                                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($pageData['selected_date']); ?>">

                                        <label for="cinema-filter">Filter by cinema:</label>
                                        <select id="cinema-filter" name="cinema" onchange="this.form.submit()">
                                            <option value="0" <?php echo (int) $pageData['selected_cinema_id'] === 0 ? 'selected' : ''; ?>>
                                                All Cinemas
                                            </option>

                                            <?php foreach ($pageData['cinema_options'] as $cinema): ?>
                                                <option
                                                    value="<?php echo (int) $cinema['cinema_id']; ?>"
                                                    <?php echo (int) $pageData['selected_cinema_id'] === (int) $cinema['cinema_id'] ? 'selected' : ''; ?>
                                                >
                                                    <?php echo htmlspecialchars($cinema['cinema_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>

                                <div class="schedule-list">
                                    <?php if (empty($pageData['schedule_items'])): ?>
                                        <article class="schedule-card">
                                          <div class="">
                                                &nbsp;
                                            </div>
                                            <div class="schedule-card-body">
                                                <h4>No movies scheduled</h4>
                                                <p class="schedule-card-meta">
                                                    There are no showtimes for this date yet.
                                                </p>
                                            </div>
                                        </article>
                                    <?php else: ?>
                                        <?php foreach ($pageData['schedule_items'] as $item): ?>
                                            <article class="schedule-card">
                                                <div class="schedule-card-poster">
                                                    <img
                                                        width="248"
                                                        height="380"
                                                        src="<?php echo '../' . htmlspecialchars($item['image_url']); ?>"
                                                        alt="<?php echo htmlspecialchars($item['title']); ?> poster"
                                                    >
                                                </div>

                                                <div class="schedule-card-body">
                                                    <h4><?php echo htmlspecialchars($item['title']); ?></h4>

                                                    <p class="schedule-card-meta movie-rating-text">
                                                        <?php echo htmlspecialchars($item['rating_code']); ?>
                                                        • <?php echo htmlspecialchars($item['duration']); ?>
                                                        • <?php echo htmlspecialchars($item['cinema_label']); ?>
                                                    </p>

                                                    <div class="schedule-card-times">
                                                        <span class="time-label">Showtimes</span>

                                                        <div class="time-buttons">
                                                            <?php foreach ($item['showtimes'] as $showtime): ?>
                                                                <span
                                                                    class="time-chip"
                                                                >
                                                                    <?php echo htmlspecialchars($showtime['display_time']); ?>
                                                            </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>

                                                    <div class="schedule-card-actions">
                                                        <?php if (!empty($item['trailer_url'])): ?>
                                                            <button
                                                                class="btn btn-tertiary watch-trailer-btn"
                                                                data-trailer="<?php echo htmlspecialchars($item['trailer_url']); ?>"
                                                                data-title="<?php echo htmlspecialchars($item['title']); ?> Trailer"
                                                                type="button"
                                                            >
                                                                <i class="fa-solid fa-play"></i> Watch Trailer
                                                            </button>
                                                        <?php endif; ?>

                                                        <?php if (!empty($item['showtimes'])): ?>
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
                                                      <?php endif; ?>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
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