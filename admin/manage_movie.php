<?php

  require_once __DIR__ . '/../app/handlers/manage_movie_handler.php';
  require_once __DIR__ . '/../app/handlers/homepage_handler.php';

  $featuredMovies = getHomePageData($pdo)['featuredMovies'];
  $currentUser = currentUser();
  $isLoggedIn = isLoggedIn();

  $pageTitle = $isEditMode ? '876 Screens - Edit Movie' : '876 Screens - Create Movie';
  $activePage = 'manage_movies';
  $basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">
    <?php require_once __DIR__ . '/../partials/head.php'; ?>
    <body>

        <?php require_once __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="page">
            
            <?php require_once __DIR__ . '/../partials/page_header.php'; ?>
        
            <div class="shadow-wrapper movie-admin-wrapper">
                
              <?php require_once __DIR__ . '/../partials/hero.php'; ?>

              <!-- CREATE MOVIE SECTION -->
              <section class="movie-admin-section">
                
                <div class="movie-admin-container">
                  <!-- PAGE INTRO -->
                  <div class="page-intro">
                    <p class="section-eyebrow">876 Screens Admin</p>
                    <h1><?= $isEditMode ? 'Edit Movie' : 'Create Movie'; ?></h1>
                    <p class="section-lead">
                      <?= $isEditMode
                          ? 'Update this movie, replace its poster if needed, and manage its showtimes.'
                          : 'Add a new movie, upload its poster, include a trailer link, and assign showtimes.'; ?>
                    </p>
                  </div>

                  <form
                    action="<?= url('app/handlers/manage_movie_handler.php') . ($isEditMode ? '?id=' . (int) $formMovie['movie_id'] : '') ?>"
                    method="POST"
                    enctype="multipart/form-data"
                    class="movie-admin-form"
                  >
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="movie_id" value="<?= (int) ($formMovie['movie_id'] ?: 0); ?>">

                    <!-- MOVIE DETAILS -->
                    <section class="movie-admin-card">
                      <h2 class="movie-admin-title">Movie Details</h2>

                      <div class="movie-details-layout">

                        <!-- POSTER -->
                        <div class="poster-column">
                          <?php
                            $currentPoster = trim($formMovie['image_url'] ?? '');
                            $hasPoster = $isEditMode && $currentPoster !== '';
                          ?>

                          <label for="moviePoster" class="poster-upload-box <?= $hasPoster ? 'has-poster' : ''; ?>">
                            <?php if ($hasPoster): ?>
                              <img
                                src="<?= url($currentPoster); ?>"
                                alt="Current poster for <?= htmlspecialchars($formMovie['title'] ?? 'movie'); ?>"
                                class="poster-preview-image"
                              >
                              <span class="poster-upload-overlay">
                                <i class="fa-solid fa-camera"></i>
                                <span class="required">Change Poster</span>
                              </span>
                            <?php else: ?>
                              <span class="poster-upload-icon">
                                <i class="fa-solid fa-camera"></i>
                              </span>
                              <span class="poster-upload-text">Upload Poster</span>
                            <?php endif; ?>
                          </label>

                          <input type="file" id="moviePoster" name="image_url" hidden>

                        </div>

                        <!-- FIELDS -->
                        <div class="details-form-grid">

                          <div class="form-row">
                            <label class="required" for="movieTitle">Title:</label>
                            <input
                              type="text"
                              id="movieTitle"
                              name="title"
                              placeholder="Enter movie title"
                              value="<?= htmlspecialchars($formMovie['title'] ?? ''); ?>"
                              required
                            >
                          </div>

                          <div class="form-row">
                            <label for="movieTrailerUrl" class="required">Trailer URL:</label>
                            <input
                              type="url"
                              id="movieTrailerUrl"
                              name="trailer_url"
                              placeholder="Enter trailer URL"
                              value="<?= htmlspecialchars($formMovie['trailer_url'] ?? ''); ?>"
                              required
                            >
                          </div>

                          <div class="form-row">
                            <label for="movieRating" class="required">Rating:</label>
                            <select id="movieRating" name="rating_id" required>
                              <option value="">— Select Rating —</option>

                              <?php foreach ($ratings as $rating): ?>
                                <option
                                  value="<?= (int) $rating['rating_id']; ?>"
                                  <?= ((int) $formMovie['rating_id'] === (int) $rating['rating_id']) ? 'selected' : ''; ?>
                                >
                                  <?= htmlspecialchars($rating['rating_code']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="form-row duration-row">
                            <label class="required">Duration:</label>
                            <div class="duration-fields">
                              <input
                                type="number"
                                name="duration_hour"
                                min="0"
                                value="<?= htmlspecialchars((string) ($formMovie['duration_hour'] ?? '')); ?>"
                                required
                                min="1"
                              >
                              <span>hours</span>

                              <input
                                type="number"
                                name="duration_minute"
                                min="0"
                                max="59"
                                value="<?= htmlspecialchars((string) ($formMovie['duration_minute'] ?? '')); ?>"
                                required
                              >
                              <span>minutes</span>
                            </div>
                          </div>

                          <div class="form-row">
                            <label for="movieDescription" class="required">Description:</label>
                            <textarea
                              id="movieDescription"
                              name="description"
                              placeholder="Enter movie description..."
                              minlength="30" 
                              required
                            ><?= htmlspecialchars($formMovie['description'] ?? ''); ?></textarea>
                          </div>

                          <div class="form-row">
                            <label class="checkbox-row">
                              <input
                                type="checkbox"
                                id = "isFeatured"
                                name="is_featured"
                                value="1"
                                
                                <?php echo !empty($formMovie['is_featured']) && !empty($existingShowtimes) ? 'checked' : ''; ?>
                                <?php echo empty($existingShowtimes) ? 'disabled' : ''; ?>
                              >
                              <span>
                                Feature this movie
                                <span class="form-note featured-note <?php echo !empty($existingShowtimes) ? 'is-hidden' : ''; ?>" id="featuredNote">
                                  (A movie can only be featured after at least one showtime is added.)
                                </span>
                              </span>
                            </label>
                            
                          </div>

                        </div>
                      </div>
                    </section>

                    <!-- SCHEDULING -->
                    <section class="movie-admin-card">

                      <h2 class="movie-admin-title">Scheduling</h2>

                      <p class="panel-subtitle">
                        Set showtimes for this movie. Select a cinema and date.
                      </p>

                      <div id="scheduleErrorBox" class="form-alert form-alert-error" hidden></div>

                      <div class="schedule-controls-box">
                        <div class="schedule-controls-grid">

                          <div class="schedule-field">
                            <label class="required" for="scheduleCinema">Select Cinema</label>
                            <select id="scheduleCinema">
                              <option value="">— Select Cinema —</option>

                              <?php foreach ($cinemas as $cinema): ?>
                                <option value="<?= (int) $cinema['cinema_id']; ?>">
                                  <?= htmlspecialchars($cinema['cinema_name']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="schedule-field">
                            <label for="scheduleDate" class="required">Select Date</label>
                            <input
                              type="date"
                              id="scheduleDate"
                              min="<?= date('Y-m-d'); ?>"
                            >
                          </div>

                          <div class="schedule-field">
                            <label class="required">Time Slots</label>

                            <div class="time-slot-list">
                              <label class="time-slot-option">
                                <input type="checkbox" class="time-slot-checkbox" value="17:30" data-label="5:30 PM">
                                <span class="time-slot-pill">5:30 PM</span>
                              </label>

                              <label class="time-slot-option">
                                <input type="checkbox" class="time-slot-checkbox" value="19:00" data-label="7:00 PM">
                                <span class="time-slot-pill">7:00 PM</span>
                              </label>

                              <label class="time-slot-option">
                                <input type="checkbox" class="time-slot-checkbox" value="20:30" data-label="8:30 PM">
                                <span class="time-slot-pill">8:30 PM</span>
                              </label>

                              <button type="button" class="btn btn-secondary add-slot-btn" id="addShowtimeBtn">
                                Add Slot
                              </button>
                            </div>
                          </div>

                        </div>
                      </div>

                      <div class="showtimes-block">
                        <div class="showtimes-header-row">
                          <h3>Scheduled Showtimes</h3>
                          <button
                            type="button"
                            class="delete-all-btn"
                            id="deleteAllShowtimesBtn"
                            hidden
                          >
                            Delete All
                          </button>
                        </div>

                        <div class="showtime-groups" id="showtimesList">
                          <div class="showtimes-empty-card" id="showtimesEmptyState">
                            <p>No showtimes added yet.</p>
                          </div>
                        </div>

                        <div id="showtimesHiddenInputs"></div>
                      </div>

                    </section>

                    <!-- ACTIONS -->
                    <div class="movie-admin-actions">

                      <!-- Cancel -->
                      <button 
                        type="button" 
                        class="btn btn-tertiary" 
                        onclick="window.location.href='<?= url('/admin/manage_movies.php'); ?>'">
                        Cancel
                      </button>

                      <?php if ($isEditMode): ?>
                        <!-- Delete -->
                        <button 
                          type="submit"
                          name="action"
                          value="delete"
                          class="btn btn-danger delete-all-btn"
                          onclick="return confirm('Are you sure you want to delete this movie? This is a permanent action and cannot be undone.');"
                        >
                          Delete Movie
                        </button>
                      <?php endif; ?>

                      <!-- Save -->
                      <button 
                        type="submit" 
                        name="action"
                        value="save"
                        class="btn btn-secondary"
                      >
                        <?= $isEditMode ? 'Update Movie' : 'Create Movie'; ?>
                      </button>

                    </div>

                  </form>
                </div>

                <?php require_once __DIR__ . '/../partials/modals.php'; ?>

              </section>

              <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>    

            </div>
        </div>
        <script>
          window.initialShowtimes = <?= json_encode($existingShowtimes ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        </script>
        <?php require_once __DIR__ . '/../partials/scripts.php'; ?>  
    </body>
</html>