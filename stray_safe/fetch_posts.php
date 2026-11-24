<?php
header('Content-Type: application/json');
require 'db.php';

$status_filter = $_GET['status'] ?? 'All';

if ($status_filter === 'All') {
    $stmt = $conn->prepare("SELECT * FROM posts ORDER BY id DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE status = ? ORDER BY id DESC");
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $row['images'] = json_decode($row['images']) ?? [];
    $row['formatted_date'] = date("F j, Y", strtotime($row['created_at']));
    $row['likes_count'] = 0;
    $row['comments_count'] = 0;
    $posts[] = $row;
}

echo json_encode($posts);
$stmt->close();
$conn->close();
?>