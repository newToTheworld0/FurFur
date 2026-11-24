<?php
session_start();

header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to manage comments.'
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// ADDED: Handle adding a new comment.
if ($action === 'add') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');

    if (!$post_id || $post_id <= 0 || $comment === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid post ID or empty comment.'
        ]);
        exit;
    }

    // ADDED: Verify that the target post exists.
    $checkPost = $conn->prepare("SELECT id FROM posts WHERE id = ?");
    $checkPost->bind_param("i", $post_id);
    $checkPost->execute();
    $postResult = $checkPost->get_result();

    if ($postResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found.'
        ]);
        $checkPost->close();
        exit;
    }

    $checkPost->close();

    // ADDED: Save the new comment.
    $stmt = $conn->prepare(
        "INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $post_id, $user_id, $comment);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully.',
            'comment_id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add comment.'
        ]);
    }

    $stmt->close();

// ADDED: Handle deleting an existing comment.
} elseif ($action === 'delete') {
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

    if (!$comment_id || $comment_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid comment ID.'
        ]);
        exit;
    }

    // ADDED: Only delete comments owned by the logged-in user.
    $stmt = $conn->prepare(
        "DELETE FROM post_comments WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Comment not found or you do not have permission to delete it.'
        ]);
    }

    $stmt->close();

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid comment action.'
    ]);
}

$conn->close();
?>