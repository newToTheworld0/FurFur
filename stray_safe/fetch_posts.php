<?php
session_start();

header('Content-Type: application/json');
require 'db.php';

$status_filter = $_GET['status'] ?? 'All';

$user_id = isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : 0;

$sql = "
    SELECT
        p.*,
        (
            SELECT COUNT(*)
            FROM post_likes pl
            WHERE pl.post_id = p.id
        ) AS likes_count,
        EXISTS (
            SELECT 1
            FROM post_likes pl2
            WHERE pl2.post_id = p.id
              AND pl2.user_id = ?
        ) AS liked_by_user,
        (
            SELECT COUNT(*)
            FROM post_comments pc
            WHERE pc.post_id = p.id
        ) AS comments_count
    FROM posts p
";

if ($status_filter === 'All') {
    $sql .= " ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $sql .= " WHERE p.status = ? ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();

$posts = [];

while ($row = $result->fetch_assoc()) {
    $row['images'] = json_decode($row['images']) ?? [];
    $row['formatted_date'] = date(
        "F j, Y",
        strtotime($row['created_at'])
    );

    $row['likes_count'] = (int) $row['likes_count'];
    $row['liked_by_user'] = (bool) $row['liked_by_user'];

    $row['comments_count'] = (int) $row['comments_count'];;

    $posts[] = $row;
}

echo json_encode($posts);

$stmt->close();
$conn->close();
?>