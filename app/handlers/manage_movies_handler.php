<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Manage Movies Handler
 * ------------------------------------------------------------
 *
 * This file is responsible for loading and preparing
 * movie data for the Manage Movies page.
 *
 * Purpose:
 * - Access control (Admin/Supervisor only)
 * - Search and pagination
 * - Fetching movies from the database
 * - Fetching related showtimes and cinemas
 * - Formatting data for display
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/functions.php';

requireAdminOrSupervisor(); // Restrict access to Admins or Supervisors

// Default page data
$pageData = [
    'success' => true,
    'errors' => [],
    'movies' => [],
    'searchTerm' => '',
    'currentPage' => 1,
    'perPage' => 10,
    'totalMovies' => 0,
    'totalPages' => 1,
];

try {
    // Get search and pagination values
    $searchTerm = trim($_GET['q'] ?? '');
    $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $perPage = 10;

    if ($currentPage < 1) {
        $currentPage = 1;
    }

    $pageData['searchTerm'] = $searchTerm;
    $pageData['currentPage'] = $currentPage;
    $pageData['perPage'] = $perPage;

    // Count Movies
    $countSql = "
        SELECT COUNT(DISTINCT m.movie_id) AS total
        FROM movie m
        INNER JOIN rating r
            ON r.rating_id = m.rating_id
        LEFT JOIN showtime s
            ON s.movie_id = m.movie_id
        LEFT JOIN cinema c
            ON c.cinema_id = s.cinema_id
        WHERE 1 = 1
    ";

    $countParams = [];

    if ($searchTerm !== '') {
        $countSql .= "
            AND (
                m.title LIKE :searchTitle
                OR r.rating_code LIKE :searchRating
                OR c.cinema_name LIKE :searchCinema
            )
        ";

        $likeSearch = '%' . $searchTerm . '%';

        $countParams['searchTitle'] = $likeSearch;
        $countParams['searchRating'] = $likeSearch;
        $countParams['searchCinema'] = $likeSearch;
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);

    $totalMovies = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalMovies / $perPage));

    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    $offset = ($currentPage - 1) * $perPage;

    $pageData['currentPage'] = $currentPage;
    $pageData['totalMovies'] = $totalMovies;
    $pageData['totalPages'] = $totalPages;

    // Fetch Movies
    $movieSql = "
        SELECT
            m.movie_id,
            m.title,
            m.description,
            m.image_url,
            m.duration_minute,
            m.is_featured,
            r.rating_code
        FROM movie m
        INNER JOIN rating r
            ON r.rating_id = m.rating_id
        LEFT JOIN showtime s
            ON s.movie_id = m.movie_id
        LEFT JOIN cinema c
            ON c.cinema_id = s.cinema_id
        WHERE 1 = 1
    ";

    $movieParams = [];

    if ($searchTerm !== '') {
        $movieSql .= "
            AND (
                m.title LIKE :searchTitle
                OR r.rating_code LIKE :searchRating
                OR c.cinema_name LIKE :searchCinema
            )
        ";

        $likeSearch = '%' . $searchTerm . '%';

        $movieParams['searchTitle'] = $likeSearch;
        $movieParams['searchRating'] = $likeSearch;
        $movieParams['searchCinema'] = $likeSearch;
    }

    $movieSql .= "
        GROUP BY
            m.movie_id,
            m.title,
            m.description,
            m.image_url,
            m.duration_minute,
            m.is_featured,
            r.rating_code
        ORDER BY m.movie_id DESC
        LIMIT :limit OFFSET :offset
    ";

    $movieStmt = $pdo->prepare($movieSql);

    foreach ($movieParams as $key => $value) {
        $movieStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }

    $movieStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $movieStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $movieStmt->execute();

    $movies = $movieStmt->fetchAll();

    if (!$movies) {
        return $pageData;
    }

    // Fetch showtimes for movies
    $movieIds = array_column($movies, 'movie_id');
    $placeholders = implode(',', array_fill(0, count($movieIds), '?'));

    $showtimeSql = "
        SELECT
            s.showtime_id,
            s.movie_id,
            s.show_date,
            s.start_time,
            c.cinema_name
        FROM showtime s
        INNER JOIN cinema c
            ON c.cinema_id = s.cinema_id
        WHERE s.movie_id IN ($placeholders)
        ORDER BY s.show_date ASC, s.start_time ASC
    ";

    $showtimeStmt = $pdo->prepare($showtimeSql);
    $showtimeStmt->execute($movieIds);
    $showtimeRows = $showtimeStmt->fetchAll();

    $showtimesByMovie = [];

    foreach ($showtimeRows as $row) {
        $movieId = (int) $row['movie_id'];

        if (!isset($showtimesByMovie[$movieId])) {
            $showtimesByMovie[$movieId] = [];
        }

        $showtimesByMovie[$movieId][] = [
            'showtime_id' => (int) $row['showtime_id'],
            'show_date'   => $row['show_date'],
            'show_time'   => $row['start_time'],
            'cinema_name' => $row['cinema_name'],
        ];
    }

    // Build final movie data
    $finalMovies = [];

    foreach ($movies as $movie) {
        $movieId = (int) $movie['movie_id'];
        $movieShowtimes = $showtimesByMovie[$movieId] ?? [];

        $cinemas = [];

        foreach ($movieShowtimes as $showtime) {
            $cinemas[] = $showtime['cinema_name'];
        }

        $finalMovies[] = [
            'movie_id' => $movieId,
            'title' => $movie['title'],
            'description' => $movie['description'],
            'image_url' => $movie['image_url'],
            'rating_code' => $movie['rating_code'],
            'duration_minute' => (int) $movie['duration_minute'],
            'duration_label' => formatMovieDuration((int) $movie['duration_minute']),
            'is_featured' => (int) $movie['is_featured'],
            'cinemas' => array_values(array_unique($cinemas)),
            'status' => getMovieStatus($movieShowtimes),
            'status_label' => getMovieStatusLabel($movieShowtimes),
            'edit_url' => './manage_movie.php?id=' . $movieId,
        ];
    }

    $pageData['movies'] = $finalMovies;

    return $pageData;

} catch (Throwable $e) {
    // Return error state if something fails
    return [
        'success' => false,
        'errors' => ['Something went wrong while loading movies: ' . $e->getMessage()],
        'movies' => [],
        'searchTerm' => '',
        'currentPage' => 1,
        'perPage' => 10,
        'totalMovies' => 0,
        'totalPages' => 1,
    ];
}

/**
 * Determine movie status based on showtimes
 */
function getMovieStatus($showtimes) {
    if (empty($showtimes)) {
        return 'coming_soon';
    }

    $today = date('Y-m-d');
    $now = time();

    $hasToday = false;
    $hasFuture = false;

    foreach ($showtimes as $showtime) {
        $showDate = $showtime['show_date'] ?? '';
        $showTime = $showtime['show_time'] ?? '';
        $showTimestamp = strtotime($showDate . ' ' . $showTime);

        if ($showDate === $today) {
            $hasToday = true;
        }

        if ($showTimestamp !== false && $showTimestamp > $now) {
            $hasFuture = true;
        }
    }

    if ($hasToday) {
        return 'showing';
    }

    if ($hasFuture) {
        return 'upcoming';
    }

    return 'archived';
}

/**
 * Convert status into user-friendly label
 */
function getMovieStatusLabel($showtimes) {
    $status = getMovieStatus($showtimes);

    return match ($status) {
        'showing' => 'Now Showing',
        'upcoming' => 'Upcoming',
        'coming_soon' => 'Coming Soon',
        default => 'Archived',
    };
}

/**
 * Convert minutes into readable format (e.g. 2h 15m)
 */
function formatMovieDuration($minutes) {
    if ($minutes <= 0) {
        return 'N/A';
    }

    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;

    if ($hours > 0) {
        return sprintf('%dh %02dm', $hours, $remainingMinutes);
    }

    return sprintf('%dm', $remainingMinutes);
}

/**
 * Converts time into 12-hour format (e.g. 2:30 PM).
 * If the time is invalid, it returns the original value.
 */
function formatShowtimeForDisplay($time) {
    $timestamp = strtotime($time);

    if ($timestamp === false) {
        return $time;
    }

    return date('g:i A', $timestamp);
}