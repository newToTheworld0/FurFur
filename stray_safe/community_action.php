<?php
session_start();
header('Content-Type: application/json');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['username']) || trim((string)$_SESSION['username']) === '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to like or comment.']);
    exit;
}

$username = trim((string)$_SESSION['username']);

// Resolve user_id from users table
$userStmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$userStmt->bind_param("s", $username);
$userStmt->execute();
$userRes = $userStmt->get_result();
$userRow = $userRes ? $userRes->fetch_assoc() : null;
$userStmt->close();

if (!$userRow) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User account not found.']);
    exit;
}

$userId = (int)$userRow['id'];
$action = $_POST['action'] ?? '';
$postId = (int)($_POST['post_id'] ?? 0);

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid post.']);
    exit;
}

if ($action === 'toggle_like') {
    $checkStmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ? LIMIT 1");
    $checkStmt->bind_param("ii", $postId, $userId);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    $liked = false;
    if ($existing) {
        $delStmt = $conn->prepare("DELETE FROM post_likes WHERE id = ?");
        $delStmt->bind_param("i", $existing['id']);
        $delStmt->execute();
        $delStmt->close();
        $liked = false;
    } else {
        $insStmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $insStmt->bind_param("ii", $postId, $userId);
        $insStmt->execute();
        $insStmt->close();
        $liked = true;
    }

    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM post_likes WHERE post_id = ?");
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $countRow = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'like_count' => (int)($countRow['total'] ?? 0)
    ]);
    exit;
}

if ($action === 'add_comment') {
    $commentText = trim((string)($_POST['comment_text'] ?? ''));
    if ($commentText === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
        exit;
    }

    $insComment = $conn->prepare("INSERT INTO post_comments (post_id, user_id, username, comment_text) VALUES (?, ?, ?, ?)");
    $insComment->bind_param("iiss", $postId, $userId, $username, $commentText);
    $insComment->execute();
    $newCommentId = $insComment->insert_id;
    $insComment->close();

    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM post_comments WHERE post_id = ?");
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $countRow = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();

    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $newCommentId,
            'username' => $username,
            'comment_text' => $commentText,
            'created_at' => date('M j, Y g:i A')
        ],
        'comment_count' => (int)($countRow['total'] ?? 0)
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported action.']);