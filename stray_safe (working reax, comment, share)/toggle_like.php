<?php
session_start();
header('Content-Type: application/json');

require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to like posts.'
    ]);
    exit;
}

$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$user_id = (int) $_SESSION['user_id'];

if (!$post_id || $post_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid post ID.'
    ]);
    exit;
}

//Check if this user already liked the post
$stmt = $conn->prepare(
    "SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    //Unlike: remove existing like
    $stmt->close();

    $stmt = $conn->prepare(
        "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $liked = false;
} else {
    // Like: add like
    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)"
    );
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $liked = true;
}

$stmt->close();

//Get updated like count
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM post_likes WHERE post_id = ?"
);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'likes_count' => (int) $count
]);

$stmt->close();
$conn->close();
?>