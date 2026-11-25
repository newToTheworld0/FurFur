<?php
session_start();

header('Content-Type: application/json');
require 'db.php';

$post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);

$user_id = isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : 0;

if (!$post_id || $post_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid post ID.'
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        pc.id,
        pc.comment,
        pc.created_at,
        u.username,
        (pc.user_id = ?) AS is_owner
    FROM post_comments pc
    INNER JOIN users u ON pc.user_id = u.id
    WHERE pc.post_id = ?
    ORDER BY pc.created_at ASC
");

$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];

while ($row = $result->fetch_assoc()) {
    $row['formatted_date'] = date(
        "F j, Y g:i A",
        strtotime($row['created_at'])
    );

    $row['is_owner'] = (bool) $row['is_owner'];

    $comments[] = $row;
}

echo json_encode([
    'success' => true,
    'comments' => $comments,
    'comments_count' => count($comments)
]);

$stmt->close();
$conn->close();
?>