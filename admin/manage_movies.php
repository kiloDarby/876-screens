<?php

  require __DIR__ . '../../app/handlers/manage_movies_handler.php';
  require_once __DIR__ . '/../app/handlers/homepage_handler.php';

  $featuredMovies = getHomePageData($pdo)['featuredMovies'];
  $currentUser = currentUser();
  $isLoggedIn = isLoggedIn();

  $errors = getFlashErrors();
  $success = getFlashSuccess();

  $pageTitle = '876 Screens - Manage Movies';
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

              <section class="manage-movies-section">
                <div class="manage-movies-shell">
                  <div class="manage-movies-header">
                    <p class="manage-movies-eyebrow">876 Screens Admin</p>
                    <h2>Manage Movies</h2>
                    <p class="manage-movies-intro">
                      Review all movies currently in the system and manage their details.
                    </p>
                  </div>

                  <form method="GET" class="manage-movies-toolbar" action="<?= url('/admin/manage_movies.php') ?>">
                    <div class="manage-movies-search">
                      <label for="movieSearch" class="hide-elm">Search movies</label>
                      <input
                        type="search"
                        id="movieSearch"
                        name="q"
                        placeholder="Search by title, rating, or cinema"
                        value="<?php echo htmlspecialchars($pageData['searchTerm']); ?>"
                      >
                      <button type="submit" class="btn btn-secondary">Search</button>
                    </div>

                    <div class="manage-movies-actions">
                      <a href="<?= url('/admin/manage_movie.php')?>" class="btn btn-tertiary add-movie-btn">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Movie</span>
                      </a>
                    </div>

                  </form>

                  <div class="manage-movies-list-wrap">
                    <div class="manage-movies-grid-head">
                      <span>Movie</span>
                      <span>Rating</span>
                      <span>Duration</span>
                      <span>Cinemas</span>
                      <span>Status</span>
                      <span>Edit</span>
                    </div>

                    <div class="manage-movies-list">
                      <?php if (empty($pageData['movies'])): ?>
                        <article class="manage-movie-row">
                          <div class="manage-movie-grid">
                            <div class="manage-movie-col movie-col-main" data-label="Movie">
                              <p>No movies found.</p>
                            </div>
                          </div>
                        </article>
                      <?php else: ?>
                        <?php foreach ($pageData['movies'] as $movie): ?>
                          <article class="manage-movie-row">
                            <div class="manage-movie-grid">
                              <div class="manage-movie-col movie-col-main" data-label="Movie">
                                <div class="movie-cell">
                                  <div class="movie-thumb">
                                    <img
                                      src="<?= url($movie['image_url']); ?>"
                                      alt="Poster for <?php echo htmlspecialchars($movie['title']); ?>"
                                    >
                                  </div>

                                  <div class="movie-meta">
                                    <span class="movie-name">
                                      <?php echo htmlspecialchars($movie['title']); ?>
                                      <?php if (!empty($movie['is_featured'])): ?>
                                        <i class="fa-solid fa-star featured-icon" title="Featured movie"></i>
                                      <?php endif; ?>
                                    </span>
                                    <span class="movie-id">Movie ID: <?php echo str_pad($movie['movie_id'], 3, '0', STR_PAD_LEFT); ?></span>
                                  </div>
                                </div>
                              </div>

                              <div class="manage-movie-col" data-label="Rating">
                                <span class="movie-rating-text"><?php echo htmlspecialchars($movie['rating_code']); ?></span>
                              </div>

                              <div class="manage-movie-col" data-label="Duration">
                                <span class="movie-value"><?php echo htmlspecialchars($movie['duration_label']); ?></span>
                              </div>

                              <div class="manage-movie-col" data-label="Cinemas">
                                <div class="cinema-list">
                                  <?php if (!empty($movie['cinemas'])): ?>
                                    <?php foreach ($movie['cinemas'] as $cinema): ?>
                                      <span><?php echo htmlspecialchars($cinema); ?></span>
                                    <?php endforeach; ?>
                                  <?php else: ?>
                                    <span>N/A</span>
                                  <?php endif; ?>
                                </div>
                              </div>

                              <div class="manage-movie-col" data-label="Status">
                                <span class="status-badge status-<?php echo htmlspecialchars($movie['status']); ?>">
                                  <?php echo htmlspecialchars($movie['status_label']); ?>
                                </span>
                              </div>

                              <div class="manage-movie-col" title="Edit <?= $movie['title']?>" data-label="Edit">
                                <a href="<?php echo htmlspecialchars($movie['edit_url']); ?>" class="table-link table-link-edit">
                                  <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                              </div>
                            </div>
                          </article>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                    
                  </div>

                  <?php
                    $searchQuery = $pageData['searchTerm'] !== ''
                        ? '&q=' . urlencode($pageData['searchTerm'])
                        : '';

                    $previousPage = max(1, $pageData['currentPage'] - 1);
                    $nextPage = min($pageData['totalPages'], $pageData['currentPage'] + 1);
                  ?>

                  <nav class="manage-movies-pagination">
                    <?php if ($pageData['totalPages'] > 1): ?>
                      <?php if ($pageData['currentPage'] > 1): ?>
                        <a href="?page=<?php echo $previousPage . $searchQuery; ?>" class="pagination-btn pagination-arrow">
                          <span>&larr;</span>
                          <span>Previous</span>
                        </a>
                      <?php else: ?>
                        <span class="pagination-btn pagination-arrow is-disabled">
                          <span>&larr;</span>
                          <span>Previous</span>
                        </span>
                      <?php endif; ?>

                      <div class="pagination-pages">
                        <?php for ($page = 1; $page <= $pageData['totalPages']; $page++): ?>
                          <a
                            href="?page=<?php echo $page . $searchQuery; ?>"
                            class="pagination-number <?php echo $page === $pageData['currentPage'] ? 'is-active' : ''; ?>"
                          >
                            <?php echo $page; ?>
                          </a>
                        <?php endfor; ?>
                      </div>

                      <?php if ($pageData['currentPage'] < $pageData['totalPages']): ?>
                        <a href="?page=<?php echo $nextPage . $searchQuery; ?>" class="pagination-btn pagination-arrow">
                          <span>Next</span>
                          <span>&rarr;</span>
                        </a>
                      <?php else: ?>
                        <span class="pagination-btn pagination-arrow is-disabled">
                          <span>Next</span>
                          <span>&rarr;</span>
                        </span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </nav>

                </div>
                <?php require_once __DIR__ . '/../partials/modals.php'; ?> 
              </section>

              <?php require_once __DIR__ . '/../partials/page_footer.php'; ?>    

            </div>
        </div>
        <?php require_once __DIR__ . '/../partials/scripts.php'; ?>  
    </body>
</html>