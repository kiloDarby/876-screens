<?php

require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/auth.php';

header('Content-Type: application/json');

$user = currentUser();

if ( ! $user) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to book a ticket.'
    ]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method.'
        ]);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $showtimeId = isset($data['showtime_id']) ? (int) $data['showtime_id'] : 0;
    $adultQuantity = isset($data['adult_quantity']) ? (int) $data['adult_quantity'] : 0;
    $childQuantity = isset($data['child_quantity']) ? (int) $data['child_quantity'] : 0;

    if ($showtimeId <= 0 || ($adultQuantity + $childQuantity) <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid booking details.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT showtime_id, adult_price, child_price
        FROM showtime
        WHERE showtime_id = ?
        LIMIT 1
    ");
    $stmt->execute([$showtimeId]);
    $showtime = $stmt->fetch();

    if (!$showtime) {
        echo json_encode([
            'success' => false,
            'message' => 'Selected showtime was not found.'
        ]);
        exit;
    }

    $totalAmount =
        ($adultQuantity * (float) $showtime['adult_price']) +
        ($childQuantity * (float) $showtime['child_price']);

    $ticketReference = generateTicketReference();

    $user = currentUser();
    $userId = isset($user['user_id']) ? (int) $user['user_id'] : 0;

    $insertStmt = $pdo->prepare("
        INSERT INTO ticket (
            user_id,
            showtime_id,
            ticket_reference,
            adult_quantity,
            child_quantity,
            total_amount,
            purchase_date
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $insertStmt->execute([
        $userId,
        $showtimeId,
        $ticketReference,
        $adultQuantity,
        $childQuantity,
        $totalAmount
    ]);

    echo json_encode([
        'success' => true,
        'ticket_reference' => $ticketReference,
        'message' => 'Ticket booked successfully.'
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong while saving the ticket.'
    ]);
    exit;
}