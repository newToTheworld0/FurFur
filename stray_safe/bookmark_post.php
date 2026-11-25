<?php

session_start();
header('Content-Type: application/json');

require 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in first.'
    ]);
    exit;
}

$username = $_SESSION['username'];

$userStmt = $conn->prepare(
    "SELECT id FROM users WHERE username=?"
);

$userStmt->bind_param("s", $username);
$userStmt->execute();

$userId = $userStmt
    ->get_result()
    ->fetch_assoc()['id'];

$postId = (int)($_POST['post_id'] ?? 0);

$check = $conn->prepare(
    "SELECT id
     FROM post_bookmarks
     WHERE user_id=? AND post_id=?"
);

$check->bind_param("ii", $userId, $postId);
$check->execute();

$result = $check->get_result();

if ($result->num_rows > 0) {

    $delete = $conn->prepare(
        "DELETE
         FROM post_bookmarks
         WHERE user_id=? AND post_id=?"
    );

    $delete->bind_param(
        "ii",
        $userId,
        $postId
    );

    $delete->execute();

    echo json_encode([
        'success' => true,
        'saved' => false
    ]);

} else {

    $insert = $conn->prepare(
        "INSERT INTO post_bookmarks
         (user_id, post_id)
         VALUES (?, ?)"
    );

    $insert->bind_param(
        "ii",
        $userId,
        $postId
    );

    $insert->execute();

    echo json_encode([
        'success' => true,
        'saved' => true
    ]);
}