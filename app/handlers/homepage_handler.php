<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Homepage Handler
 * ------------------------------------------------------------
 *
 * This file contains functions used to retrieve and format
 * movie data for the homepage of the 876 Screens system.
 *
 * The homepage has three main sections:
 * 1. Featured Movies (hero carousel)
 * 2. Now Showing (movies currently available for booking)
 * 3. Coming Soon (movies not yet scheduled)
 *
 * Each function is responsible for fetching specific data
 * from the database and returning it in a usable format.
 */

require_once __DIR__ . '/../../config/db.php';

/**
 * Fetch featured movies for homepage hero carousel.
 * Featured movies must:
 * - be marked as featured
 * - have at least one upcoming showtime
 */
function fetchFeaturedMovies($pdo, $limit = 5) {
    $sql = "
        SELECT
            m.movie_id,
            m.title,
            m.description,
            m.image_url,
            m.trailer_url,
            m.duration_minute,
            r.rating_code,
            c.cinema_name,
            s.showtime_id,
            s.show_date,
            s.start_time,
            s.adult_price,
            s.child_price
        FROM movie m
        INNER JOIN rating r
            ON r.rating_id = m.rating_id
        INNER JOIN (
            SELECT
                s1.movie_id,
                MIN(TIMESTAMP(s1.show_date, s1.start_time)) AS next_show_datetime
            FROM showtime s1
            WHERE TIMESTAMP(s1.show_date, s1.start_time) >= NOW()
            GROUP BY s1.movie_id
        ) next_slot
            ON next_slot.movie_id = m.movie_id
        INNER JOIN showtime s
            ON s.movie_id = next_slot.movie_id
           AND TIMESTAMP(s.show_date, s.start_time) = next_slot.next_show_datetime
        INNER JOIN cinema c
            ON c.cinema_id = s.cinema_id
        WHERE m.is_featured = 1
        ORDER BY TIMESTAMP(s.show_date, s.start_time) ASC
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch now showing movies for the card grid.
 * These are movies with at least one upcoming showtime.
 * We grab the next available showtime for each movie.
 */
function fetchNowShowingMovies($pdo, $limit = 12) {
    $sql = "
        SELECT
            m.movie_id,
            m.title,
            m.description,
            m.image_url,
            m.trailer_url,
            m.duration_minute,
            r.rating_code,
            c.cinema_name,
            s.showtime_id,
            s.show_date,
            s.start_time,
            s.adult_price,
            s.child_price
        FROM movie m
        INNER JOIN rating r
            ON r.rating_id = m.rating_id
        INNER JOIN (
            SELECT
                s1.movie_id,
                MIN(TIMESTAMP(s1.show_date, s1.start_time)) AS next_show_datetime
            FROM showtime s1
            WHERE TIMESTAMP(s1.show_date, s1.start_time) >= NOW()
            GROUP BY s1.movie_id
        ) next_slot
            ON next_slot.movie_id = m.movie_id
        INNER JOIN showtime s
            ON s.movie_id = next_slot.movie_id
           AND TIMESTAMP(s.show_date, s.start_time) = next_slot.next_show_datetime
        INNER JOIN cinema c
            ON c.cinema_id = s.cinema_id
        ORDER BY TIMESTAMP(s.show_date, s.start_time) ASC
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch coming soon movies for second carousel.
 * Coming soon = movies with no showtimes yet.
 */
function fetchComingSoonMovies($pdo, $limit = 10) {
    $sql = "
        SELECT
            m.movie_id,
            m.title,
            m.description,
            m.image_url,
            m.trailer_url,
            m.duration_minute,
            r.rating_code,
            m.created_at
        FROM movie m
        INNER JOIN rating r
            ON r.rating_id = m.rating_id
        LEFT JOIN showtime s
            ON s.movie_id = m.movie_id
        WHERE s.showtime_id IS NULL
        ORDER BY m.created_at DESC, m.movie_id DESC
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Format helpers for homepage display.
 */
function formatHomepageMovieDuration(?int $minutes): string
{
    if (empty($minutes)) {
        return '';
    }

    return (int) $minutes . ' mins';
}

/**
 * Format show date into readable format.
 *
 * Example:
 * 2026-04-17 -> Friday, April 17
 */
function formatShowDate($showDate) {
    if ( empty($showDate) ) {
        return '';
    }

    return date('l, F j', strtotime($showDate));
}

/**
 * Format show time into 12-hour format.
 *
 * Example:
 * 18:30:00 -> 6:30 PM
 */
function formatShowTime($startTime) {
    if ( empty($startTime) ) {
        return '';
    }
    return date('g:i A', strtotime($startTime));
}

/**
 * Build small meta text shown under movie title.
 *
 * Combines:
 * - duration
 * - rating
 *
 * Example:
 * "120 mins • PG-13"
 */
function formatMovieMeta($movie) {
    $parts = [];

    if ( ! empty($movie['duration_minute']) ) {
        $parts[] = formatHomepageMovieDuration((int) $movie['duration_minute']);
    }

    if ( ! empty($movie['rating_code']) ) {
        $parts[] = $movie['rating_code'];
    }

    return implode(' • ', $parts);
}

/**
 * Main helper function for homepage.
 * This returns all required homepage data at once.
 *
 */
function getHomePageData($pdo) {
    return [
        'featuredMovies'   => fetchFeaturedMovies($pdo, 5),
        'nowShowingMovies' => fetchNowShowingMovies($pdo, 12),
        'comingSoonMovies' => fetchComingSoonMovies($pdo, 8),
    ];
}