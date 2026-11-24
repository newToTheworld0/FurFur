<?php
session_start();
ini_set('display_errors', 0);
error_reporting(0);
ob_start();

header('Content-Type: application/json');

function respond_json(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload);
    exit;
}

try {
    require_once 'db.php';

    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        respond_json(["success" => false, "message" => "Database connection failed."], 500);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond_json(["success" => false, "message" => "Invalid request method."], 405);
    }

    if (!isset($_SESSION['username']) || trim((string)$_SESSION['username']) === '') {
        respond_json(["success" => false, "message" => "Please log in to publish a post."], 401);
    }

    $username = trim((string)$_SESSION['username']);
    $description = trim((string)($_POST['description'] ?? ''));
    $animal_type = trim((string)($_POST['animal_type'] ?? 'Unknown'));
    $location_text = trim((string)($_POST['location_text'] ?? ''));
    $maps_link = trim((string)($_POST['maps_link'] ?? ''));
    $condition = trim((string)($_POST['condition'] ?? 'Appears okay'));
    $status = trim((string)($_POST['status'] ?? 'Stray'));

    if ($animal_type === '') {
        $animal_type = 'Unknown';
    }
    if ($condition === '') {
        $condition = 'Appears okay';
    }
    if ($status === '') {
        $status = 'Stray';
    }

    if ($description === '' || $location_text === '') {
        respond_json(["success" => false, "message" => "Description and location are required."], 400);
    }

    $uploaded_paths = [];

    if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
        $abs_upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        if (!is_dir($abs_upload_dir) && !mkdir($abs_upload_dir, 0777, true) && !is_dir($abs_upload_dir)) {
            respond_json(["success" => false, "message" => "Failed to create upload directory."], 500);
        }

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;

        $saved_count = 0;
        foreach ($_FILES['photos']['name'] as $key => $orig_name) {
            if ($saved_count >= 10) {
                break;
            }

            $error_code = $_FILES['photos']['error'][$key] ?? UPLOAD_ERR_NO_FILE;
            if ($error_code !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp_name = $_FILES['photos']['tmp_name'][$key] ?? '';
            if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
                continue;
            }

            $ext = strtolower(pathinfo((string)$orig_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_exts, true)) {
                continue;
            }

            if ($finfo !== null) {
                $detected_mime = $finfo->file($tmp_name);
                if (!in_array($detected_mime, $allowed_mimes, true)) {
                    continue;
                }
            }

            $filename = uniqid('sighting_', true) . '.' . $ext;
            $destination = $abs_upload_dir . $filename;

            if (move_uploaded_file($tmp_name, $destination)) {
                $uploaded_paths[] = 'uploads/' . $filename;
                $saved_count++;
            }
        }
    }

    $images_json = json_encode($uploaded_paths);
    if ($images_json === false) {
        $images_json = '[]';
    }

    $stmt = $conn->prepare(
        "INSERT INTO posts (username, description, animal_type, location_text, maps_link, animal_condition, status, images)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        respond_json(["success" => false, "message" => "Database prepare error: " . $conn->error], 500);
    }

    $stmt->bind_param(
        "ssssssss",
        $username,
        $description,
        $animal_type,
        $location_text,
        $maps_link,
        $condition,
        $status,
        $images_json
    );

    if (!$stmt->execute()) {
        $error_msg = $stmt->error;
        $stmt->close();
        respond_json(["success" => false, "message" => "Database error: " . $error_msg], 500);
    }

    $new_post_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    respond_json([
        "success" => true,
        "message" => "Sighting published!",
        "post_id" => $new_post_id,
        "images" => $uploaded_paths
    ]);

} catch (Throwable $e) {
    respond_json(["success" => false, "message" => "Server error: " . $e->getMessage()], 500);
}