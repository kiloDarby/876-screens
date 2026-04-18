<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Fetch Available Showtimes (API Endpoint)
 * ------------------------------------------------------------
 * 
 * Fetch available showtimes for a movie (AJAX endpoint)
 *
 * This script returns upcoming showtimes for a given movie ID.
 * It only includes:
 * - future dates
 * - today’s showtimes that have not started yet
 *
 * Request:
 * - GET movie_id
 *
 * Response:
 * - JSON with success status and showtimes array
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');   // return response as JSON

$movieId = (int) ($_GET['movie_id'] ?? 0);

if ($movieId <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$sql = "
    SELECT
        s.showtime_id,
        s.cinema_id,
        c.cinema_name,
        s.show_date,
        s.start_time,
        s.adult_price,
        s.child_price
    FROM showtime s
    JOIN cinema c ON c.cinema_id = s.cinema_id
    WHERE s.movie_id = :movie_id
    AND (
        s.show_date > CURDATE()
        OR (s.show_date = CURDATE() AND s.start_time >= CURTIME())
    )
    ORDER BY s.show_date, s.cinema_id, s.start_time
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['movie_id' => $movieId]);

echo json_encode([
    'success' => true,
    'showtimes' => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);