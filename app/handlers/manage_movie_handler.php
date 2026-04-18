<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Manage Movie Handler
 * ------------------------------------------------------------
 * This file handles the create and edit movie process.
 *
 * Main responsibilities:
 * - Restrict access to Admins and Supervisors only
 * - Load ratings, cinemas, and existing movie details
 * - Process the movie form when submitted
 * - Validate movie details and showtimes
 * - Upload movie poster images
 * - Save movie and showtime data into the database
 * - Show success or error messages using session flash data
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../auth.php';

requireAdminOrSupervisor(); // Only Admins and Supervisors can access this file

$errors = getFlashErrors();
$success = getFlashSuccess();

$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$movieId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEditMode = $movieId > 0;

$ratings = fetchRatings($pdo);
$cinemas = fetchCinemas($pdo);
$existingShowtimes = [];
$movie = null;

// If editing, fetch the movie and its showtimes
if ($isEditMode) {
    $movie = fetchMovieById($pdo, $movieId);

    if (!$movie) {
        $_SESSION['errors'] = ['Movie not found.'];
        header('Location: ' . url('/admin/manage_movies.php'));
        exit;
    }

    $existingShowtimes = fetchMovieShowtimes($pdo, $movieId);
}

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? 'save');

    if ($action === 'save') {
        handleSaveMovie($pdo, $cinemas);
    }
}

$formMovie = buildFormMovieData($oldInput, $movie); // Build the final form data to display on the page

/**
 * Handle saving a movie.
 * This function is used for both creating a new movie
 * and updating an existing movie.
 */
function handleSaveMovie($pdo, $cinemas) {
    $movieId = isset($_POST['movie_id']) ? (int) $_POST['movie_id'] : 0;
    $isEditMode = $movieId > 0;

    $title = trim($_POST['title'] ?? '');
    $trailerUrl = trim($_POST['trailer_url'] ?? '');
    $trailerUrl = convertToEmbedUrl($trailerUrl);
    $ratingId = isset($_POST['rating_id']) ? (int) $_POST['rating_id'] : 0;
    $durationHour = isset($_POST['duration_hour']) ? (int) $_POST['duration_hour'] : 0;
    $durationMinute = isset($_POST['duration_minute']) ? (int) $_POST['duration_minute'] : 0;
    $description = trim($_POST['description'] ?? '');
    $showtimes = $_POST['showtimes'] ?? [];
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    $currentUser = currentUser();
    $createdBy = isset($currentUser['user_id']) ? (int) $currentUser['user_id'] : 0;

    $errors = [];

    // Save old input in session so form can be refilled after errors
    $_SESSION['old_input'] = [
        'movie_id' => $movieId,
        'title' => $title,
        'trailer_url' => $trailerUrl,
        'rating_id' => $ratingId,
        'duration_hour' => $durationHour,
        'duration_minute' => $durationMinute,
        'description' => $description,
        'showtimes' => $showtimes,
        'is_featured' => $isFeatured,
    ];

    // Simple validation
    if ($title === '') $errors[] = 'Movie title is required.';
    if ($ratingId <= 0) $errors[] = 'Please select a valid rating.';
    if ($description === '') $errors[] = 'Movie description is required.';
    if ($durationHour < 0) $errors[] = 'Hours cannot be negative.';
    if ($durationMinute < 0 || $durationMinute > 59) $errors[] = 'Minutes must be between 0 and 59.';
    $totalDurationMinutes = ($durationHour * 60) + $durationMinute;
    if ($totalDurationMinutes <= 0) $errors[] = 'Movie duration must be greater than 0 minutes.';
    if ($trailerUrl !== '' && !filter_var($trailerUrl, FILTER_VALIDATE_URL)) $errors[] = 'Trailer URL must be a valid URL.';
    if ($createdBy <= 0 && !$isEditMode) $errors[] = 'Unable to identify the logged in user.';
    if ( ! ratingExists($pdo, $ratingId) ) $errors[] = 'Selected rating does not exist.';
    
    $validatedShowtimes = [];
    if ( ! empty($showtimes) ) $validatedShowtimes = validateShowtimes($showtimes, $cinemas, $errors);

    $imageUrl = null;
    if ($isEditMode) {
        $existingMovie = fetchMovieById($pdo, $movieId);

        if (!$existingMovie) {
            $errors[] = 'Movie not found.';
        } else {
            $imageUrl = $existingMovie['image_url'];
        }
    }

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedImage = uploadMoviePoster($_FILES['image_url'], $errors);

        if ($uploadedImage !== null) {
            $imageUrl = $uploadedImage;
        }
    }

    if ( ! empty($errors) ) {
        $_SESSION['errors'] = $errors;

        $redirectUrl = '/admin/manage_movie.php';
        if ($isEditMode) {
            $redirectUrl .= '?id=' . $movieId;
        }

        header('Location: ' . url($redirectUrl));
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        // Update existing movie
        if ($isEditMode) {
            $movieStmt = $pdo->prepare("
                UPDATE movie
                SET
                    rating_id = ?,
                    title = ?,
                    description = ?,
                    image_url = ?,
                    trailer_url = ?,
                    duration_minute = ?,
                    is_featured = ?
                WHERE movie_id = ?
            ");

            $movieStmt->execute([
                $ratingId,
                $title,
                $description,
                $imageUrl,
                $trailerUrl !== '' ? $trailerUrl : null,
                $totalDurationMinutes,
                $isFeatured,
                $movieId
            ]);

            // Delete old showtimes so the new list can replace them
            $deleteShowtimesStmt = $pdo->prepare("
                DELETE FROM showtime
                WHERE movie_id = ?
            ");
            $deleteShowtimesStmt->execute([$movieId]);

            $savedMovieId = $movieId;
        } else {
            // Prepare query for inserting showtimes
            $movieStmt = $pdo->prepare("
                INSERT INTO movie (
                    rating_id,
                    created_by,
                    title,
                    description,
                    image_url,
                    trailer_url,
                    duration_minute,
                    is_featured
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            // Insert all validated showtimes
            $movieStmt->execute([
                $ratingId,
                $createdBy,
                $title,
                $description,
                $imageUrl,
                $trailerUrl !== '' ? $trailerUrl : null,
                $totalDurationMinutes,
                $isFeatured
            ]);

            $savedMovieId = (int) $pdo->lastInsertId();
        }

        $showtimeStmt = $pdo->prepare("
            INSERT INTO showtime (
                movie_id,
                cinema_id,
                scheduled_by,
                show_date,
                start_time,
                adult_price,
                child_price,
                available_seat
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($validatedShowtimes as $showtime) {
            $showtimeStmt->execute([
                $savedMovieId,
                $showtime['cinema_id'],
                $createdBy,
                $showtime['show_date'],
                $showtime['show_time'],
                1500.00,
                1000.00,
                100
            ]);
        }

        $pdo->commit();

        unset($_SESSION['old_input']);
        $_SESSION['success'] = $isEditMode ? 'Movie updated successfully.' : 'Movie created successfully.';
        
        // Redirect to manage movies page
        header('Location: ' . url('/admin/manage_movies.php'));
        exit;

    } catch (Throwable $e) {
        // Undo all changes if an error happens
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['errors'] = ['Something went wrong while saving the movie.'];

        $redirectUrl = '/admin/manage_movie.php';
        if ($isEditMode) {
            $redirectUrl .= '?id=' . $movieId;
        }
        header('Location: ' . url($redirectUrl));
        exit;
    }
}

/**
 * Fetch all ratings from the database.
 * Used to fill the movie rating dropdown.
 */
function fetchRatings($pdo) {
    $stmt = $pdo->query("
        SELECT rating_id, rating_code
        FROM rating
        ORDER BY rating_code ASC
    ");

    return $stmt->fetchAll() ?: [];
}

/**
 * Fetch all cinemas from the database.
 * Used to fill the cinema dropdown.
 */
function fetchCinemas($pdo) {
    $stmt = $pdo->query("
        SELECT cinema_id, cinema_name
        FROM cinema
        ORDER BY cinema_name ASC
    ");

    return $stmt->fetchAll() ?: [];
}

/**
 * Fetch one movie by its id.
 * Returns movie details if found.
 */
function fetchMovieById($pdo, $movieId) {
    $stmt = $pdo->prepare("
        SELECT
            movie_id,
            rating_id,
            created_by,
            title,
            description,
            image_url,
            trailer_url,
            duration_minute,
            is_featured,
            created_at
        FROM movie
        WHERE movie_id = ?
        LIMIT 1
    ");
    $stmt->execute([$movieId]);

    $movie = $stmt->fetch();

    return $movie ?: null;
}

/**
 * Fetch all upcoming showtimes for a movie.
 * Only future or current valid showtimes are returned.
 */
function fetchMovieShowtimes($pdo, $movieId) {
    $stmt = $pdo->prepare("
        SELECT
            s.showtime_id,
            s.cinema_id,
            c.cinema_name,
            s.show_date,
            s.start_time
        FROM showtime s
        INNER JOIN cinema c
            ON c.cinema_id = s.cinema_id
        WHERE s.movie_id = ?
            AND (
                s.show_date > CURDATE()
                OR (s.show_date = CURDATE() AND s.start_time >= CURTIME())
            )
        ORDER BY s.show_date ASC, s.start_time ASC
    ");
    $stmt->execute([$movieId]);

    $rows = $stmt->fetchAll() ?: [];

    $formatted = [];

    // Reformat results to make them easier to use in the form
    foreach ($rows as $row) {
        $formatted[] = [
            'cinemaValue' => (string) $row['cinema_id'],
            'cinemaText'  => $row['cinema_name'],
            'date'        => $row['show_date'],
            'timeValue'   => substr($row['start_time'], 0, 5),
            'timeLabel'   => date('g:i A', strtotime($row['start_time'])),
        ];
    }

    return $formatted;
}

/**
 * Check if a rating exists in the rating table.
 */
function ratingExists($pdo, $ratingId) {
    $stmt = $pdo->prepare("
        SELECT rating_id
        FROM rating
        WHERE rating_id = ?
        LIMIT 1
    ");
    $stmt->execute([$ratingId]);

    return (bool) $stmt->fetch();
}

/**
 * Build the final movie form data.
 * Old input is used first if available,
 * otherwise database values are used.
 */
function buildFormMovieData($oldInput, $movie) {
    $durationMinutes = (int) ($movie['duration_minute'] ?? 0);

    $defaultHours = $durationMinutes > 0 ? floor($durationMinutes / 60) : '';
    $defaultMinutes = $durationMinutes > 0 ? $durationMinutes % 60 : '';

    return [
        'movie_id' => $oldInput['movie_id'] ?? ($movie['movie_id'] ?? ''),
        'title' => $oldInput['title'] ?? ($movie['title'] ?? ''),
        'trailer_url' => $oldInput['trailer_url'] ?? ($movie['trailer_url'] ?? ''),
        'rating_id' => $oldInput['rating_id'] ?? ($movie['rating_id'] ?? ''),
        'duration_hour' => $oldInput['duration_hour'] ?? $defaultHours,
        'duration_minute' => $oldInput['duration_minute'] ?? $defaultMinutes,
        'description' => $oldInput['description'] ?? ($movie['description'] ?? ''),
        'image_url' => $movie['image_url'] ?? null,
        'showtimes' => $oldInput['showtimes'] ?? [],
        'is_featured' => $oldInput['is_featured'] ?? ($movie['is_featured'] ?? 0),
    ];
}

/**
 * Validate all submitted showtimes.
 * Checks cinema, date, time, past dates, and duplicates.
 */
function validateShowtimes($showtimes, $cinemas, &$errors) {
    $validCinemaIds = array_map(
        fn($cinema) => (int) $cinema['cinema_id'],
        $cinemas
    );

    $validated = [];
    $seen = [];

    foreach ($showtimes as $index => $showtime) {
        $cinemaId = isset($showtime['cinema_id']) ? (int) $showtime['cinema_id'] : 0;
        $showDate = trim($showtime['show_date'] ?? '');
        $showTime = trim($showtime['show_time'] ?? '');

        $rowNumber = $index + 1;

        if ($cinemaId <= 0 || !in_array($cinemaId, $validCinemaIds, true)) {
            $errors[] = "Showtime {$rowNumber}: invalid cinema selected.";
            continue;
        }

        if (!isValidDate($showDate)) {
            $errors[] = "Showtime {$rowNumber}: invalid date.";
            continue;
        }

        if ($showDate < date('Y-m-d')) {
            $errors[] = "Showtime {$rowNumber}: date cannot be in the past.";
            continue;
        }

        if (!isValidTime($showTime)) {
            $errors[] = "Showtime {$rowNumber}: invalid time.";
            continue;
        }

        $duplicateKey = $cinemaId . '|' . $showDate . '|' . $showTime;

        if (isset($seen[$duplicateKey])) {
            $errors[] = "Showtime {$rowNumber}: duplicate showtime detected.";
            continue;
        }

        $seen[$duplicateKey] = true;

        $validated[] = [
            'cinema_id' => $cinemaId,
            'show_date' => $showDate,
            'show_time' => $showTime,
        ];
    }

    return $validated;
}

/**
 * Check if a date matches Y-m-d format.
 */
function isValidDate($date) {
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

/**
 * Check if a time matches H:i format.
 */
function isValidTime($time) {
    $parsed = DateTime::createFromFormat('H:i', $time);
    return $parsed && $parsed->format('H:i') === $time;
}

/**
 * Upload a movie poster image.
 * Only JPG, PNG, and WEBP files up to 5MB are allowed.
 */
function uploadMoviePoster($file, &$errors) {
    if ( ! isset($file['error']) || is_array($file['error'])) {
        $errors[] = 'Invalid poster upload.';
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Poster upload failed.';
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $errors[] = 'Poster image must be 5MB or less.';
        return null;
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimeTypes[$mimeType])) {
        $errors[] = 'Poster must be a JPG, PNG, or WEBP image.';
        return null;
    }

    $extension = $allowedMimeTypes[$mimeType];
    $fileName = 'movie_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;

    $uploadDirectory = __DIR__ . '/../uploads/movie_posters/';

    if ( ! is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0775, true);
    }

    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = 'Failed to save uploaded poster.';
        return null;
    }

    return 'uploads/movie_posters/' . $fileName;
}

/**
 * Convert a normal YouTube link into an embed link.
 * This makes it easier to use the trailer inside an iframe.
 */
function convertToEmbedUrl($url) {
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }

    if (strpos($url, 'youtube.com/watch') !== false) {
        $query = parse_url($url, PHP_URL_QUERY);

        if ($query) {
            parse_str($query, $params);

            if (!empty($params['v'])) {
                return 'https://www.youtube.com/embed/' . $params['v'];
            }
        }
    }

    if (strpos($url, 'youtu.be/') !== false) {
        $path = parse_url($url, PHP_URL_PATH);

        if ($path) {
            $videoId = trim($path, '/');

            if ($videoId !== '') {
                return 'https://www.youtube.com/embed/' . $videoId;
            }
        }
    }

    return $url;
}