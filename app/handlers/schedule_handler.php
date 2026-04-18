<?php

/**
 * ------------------------------------------------------------
 * 876 Screens - Schedule Handler
 * ------------------------------------------------------------
 *
 * This file handles the movie schedule system.
 * It is responsible for:
 * - formatting schedule data for display
 * - selecting dates and months
 * - fetching cinemas and showtimes from the database
 * - grouping movies and showtimes
 * - building the calendar view
 * - preparing all data needed for the schedule page
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../functions.php';

/**
 * Convert movie duration from minutes into a readable format.
 * Example: 125 → 2h 05m
 */
function formatScheduleMovieDuration($minutes) {
    $minutes = (int) $minutes;

    if ($minutes <= 0) {
        return 'Duration unavailable';
    }

    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;

    if ($hours <= 0) {
        return $remainingMinutes . 'm';
    }

    return $hours . 'h ' . str_pad((string) $remainingMinutes, 2, '0', STR_PAD_LEFT) . 'm';
}

// Build the heading text for the selected date.
function formatScheduleHeading($date) {
    return 'Schedule for ' . strtoupper($date->format('l, F j'));
}

/**
 * Get the first day of a selected month.
 * If the input is invalid, default to the current month.
 */
function getMonthStart($monthParam) {
    $monthParam = trim($monthParam);

    if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
        $date = new DateTime('first day of this month');
        $date->setTime(0, 0, 0);
        return $date;
    }

    $date = DateTime::createFromFormat('Y-m-d', $monthParam . '-01');

    if ( ! $date) {
        $date = new DateTime('first day of this month');
        $date->setTime(0, 0, 0);
        return $date;
    }

    $date->setTime(0, 0, 0);

    return $date;
}

/**
 * Get the selected date.
 * Defaults to today if no valid date is provided.
 */
function getSelectedDate($dateParam) {
    if ( ! empty($dateParam) ) {
        $selectedDate = DateTime::createFromFormat('Y-m-d', $dateParam);

        if ($selectedDate) {
            $selectedDate->setTime(0, 0, 0);
            return $selectedDate;
        }
    }

    $today = new DateTime('today');
    $today->setTime(0, 0, 0);

    return $today;
}

/**
 * Fetch all cinemas from the database.
 * Used to build the cinema filter dropdown.
 */
function getCinemaOptions($pdo) {
    $stmt = $pdo->query("
        SELECT cinema_id, cinema_name
        FROM cinema
        ORDER BY cinema_name ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all dates within the month that have showtimes.
 * Used to highlight active days in the calendar.
 */
function getDatesWithShowtimes($pdo, $monthStart) {
    $monthStartString = $monthStart->format('Y-m-01');
    $monthEnd = (clone $monthStart)->modify('last day of this month');
    $monthEndString = $monthEnd->format('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT DISTINCT s.show_date
        FROM showtime s
        INNER JOIN movie m ON m.movie_id = s.movie_id
        WHERE s.show_date >= ?
          AND s.show_date <= ?
        ORDER BY s.show_date ASC
    ");

    $stmt->execute([$monthStartString, $monthEndString]);

    $dates = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dates[$row['show_date']] = true;
    }

    return $dates;
}

/**
 * Get all movies and showtimes for a selected date.
 * Optionally filter by cinema.
 */
function getScheduleByDate($pdo, $selectedDate, $cinemaId = 0) {
    $params = [$selectedDate];
    $cinemaSql = '';

    if ($cinemaId > 0) {
        $cinemaSql = ' AND c.cinema_id = ? ';
        $params[] = $cinemaId;
    }

    $stmt = $pdo->prepare("
        SELECT
            m.movie_id,
            m.title,
            m.description,
            m.image_url,
            m.trailer_url,
            m.duration_minute,
            r.rating_code AS rating_code,
            c.cinema_id,
            c.cinema_name,
            s.showtime_id,
            s.show_date,
            s.start_time
        FROM showtime s
        INNER JOIN movie m ON m.movie_id = s.movie_id
        INNER JOIN cinema c ON c.cinema_id = s.cinema_id
        LEFT JOIN rating r ON r.rating_id = m.rating_id
        WHERE s.show_date = ?
          $cinemaSql
        ORDER BY m.title ASC, s.start_time ASC, c.cinema_name ASC
    ");

    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groupedSchedule = [];

    foreach ($rows as $row) {
        $movieId = (int) $row['movie_id'];
        $displayTime = date('g:i A', strtotime($row['start_time']));
        $timeKey = strtolower($displayTime);

        if (!isset($groupedSchedule[$movieId])) {
            $groupedSchedule[$movieId] = [
                'movie_id' => $movieId,
                'title' => $row['title'],
                'description' => $row['description'],
                'image_url' => $row['image_url'],
                'trailer_url' => $row['trailer_url'],
                'duration' => formatScheduleMovieDuration((int) $row['duration_minute']),
                'rating_code' => $row['rating_code'] ?: 'NR',
                'cinema_names' => [],
                'showtimes' => [],
                'seen_times' => [],
            ];
        }

        // Add cinema name if not already included
        if ( ! in_array($row['cinema_name'], $groupedSchedule[$movieId]['cinema_names'], true) ) {
            $groupedSchedule[$movieId]['cinema_names'][] = $row['cinema_name'];
        }

        // Prevent duplicate showtimes
        if (!isset($groupedSchedule[$movieId]['seen_times'][$timeKey])) {
            $groupedSchedule[$movieId]['showtimes'][] = [
                'showtime_id' => (int) $row['showtime_id'],
                'show_date' => $row['show_date'],
                'start_time' => $row['start_time'],
                'display_time' => $displayTime,
                'cinema_name' => $row['cinema_name'],
                'cinema_id' => (int) $row['cinema_id'],
            ];

            $groupedSchedule[$movieId]['seen_times'][$timeKey] = true;
        }
    }

    // Final formatting
    foreach ($groupedSchedule as &$movie) {
        $movie['cinema_label'] = implode(', ', $movie['cinema_names']);
        unset($movie['seen_times']);
    }
    unset($movie);

    return array_values($groupedSchedule);
}

/**
 * Build the calendar grid for the schedule page.
 * Includes previous and next month days for full layout.
 */
function buildCalendarDays($monthStart, $datesWithShowtimes, $selectedDate) {
    $calendarDays = [];
    $today = new DateTime('today');
    $today->setTime(0, 0, 0);

    $gridStart = clone $monthStart;
    $daysToSubtract = (int) $gridStart->format('w');

    if ($daysToSubtract > 0) {
        $gridStart->modify("-{$daysToSubtract} days");
    }

    $monthEnd = (clone $monthStart)->modify('last day of this month');
    $gridEnd = clone $monthEnd;
    $daysToAdd = 6 - (int) $gridEnd->format('w');

    if ($daysToAdd > 0) {
        $gridEnd->modify("+{$daysToAdd} days");
    }

    $currentDay = clone $gridStart;

    // Build each calendar day
    while ($currentDay <= $gridEnd) {
        $dateString = $currentDay->format('Y-m-d');
        $isCurrentMonth = $currentDay->format('Y-m') === $monthStart->format('Y-m');
        $isPastDay = $currentDay < $today;

        $calendarDays[] = [
            'date' => $dateString,
            'day_number' => $currentDay->format('j'),
            'is_current_month' => $isCurrentMonth,
            'has_showtime' => isset($datesWithShowtimes[$dateString]),
            'is_active' => $dateString === $selectedDate,
            'is_past_day' => $isPastDay,
        ];

        $currentDay->modify('+1 day');
    }

    return $calendarDays;
}

/**
 * Main page logic
 * - get selected date and month
 * - fetch schedule data
 * - prepare all values for the view
 */
$selectedDateObject = getSelectedDate($_GET['date'] ?? null);
$selectedDate = $selectedDateObject->format('Y-m-d');

$monthParam = $_GET['month'] ?? $selectedDateObject->format('Y-m');
$monthStart = getMonthStart($monthParam);

$todayMonthStart = new DateTime('first day of this month');
$todayMonthStart->setTime(0, 0, 0);

$datesWithShowtimes = getDatesWithShowtimes($pdo, $monthStart);

$cinemaId = isset($_GET['cinema']) ? (int) $_GET['cinema'] : 0;

$cinemaOptions = getCinemaOptions($pdo);
$scheduleItems = getScheduleByDate($pdo, $selectedDate, $cinemaId);
$calendarDays = buildCalendarDays($monthStart, $datesWithShowtimes, $selectedDate);

$isCurrentMonth = $monthStart->format('Y-m') === $todayMonthStart->format('Y-m');
$previousMonth = $isCurrentMonth ? null : (clone $monthStart)->modify('-1 month')->format('Y-m');
$nextMonth = (clone $monthStart)->modify('+1 month')->format('Y-m');

/**
 * Final data sent to the view
 */
$pageData = [
    'month_label' => strtoupper($monthStart->format('F Y')),
    'selected_date' => $selectedDate,
    'selected_heading' => formatScheduleHeading($selectedDateObject),
    'selected_month' => $monthStart->format('Y-m'),
    'previous_month' => $previousMonth,
    'next_month' => $nextMonth,
    'is_current_month' => $isCurrentMonth,
    'calendar_days' => $calendarDays,
    'cinema_options' => $cinemaOptions,
    'selected_cinema_id' => $cinemaId,
    'schedule_items' => $scheduleItems,
];