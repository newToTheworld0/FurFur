<?php
session_start();
ini_set('display_errors', 0);
error_reporting(0);
ob_start();

header('Content-Type: application/json');

try {
    require 'db.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
        exit;
    }

    $username = $_SESSION['username'] ?? 'Guest';
    $description = trim($_POST['description'] ?? '');
    $animal_type = $_POST['animal_type'] ?? 'Unknown';
    $location_text = trim($_POST['location_text'] ?? '');
    $maps_link = trim($_POST['maps_link'] ?? '');
    $condition = trim($_POST['condition'] ?? 'Appears okay');
    $status = $_POST['status'] ?? 'Stray';

    if (empty($description) || empty($location_text)) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Description and location are required."]);
        exit;
    }

    $uploaded_paths = [];
    if (!empty($_FILES['photos']['name'][0])) {
        $abs_upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if (!is_dir($abs_upload_dir)) {
            mkdir($abs_upload_dir, 0777, true);
        }

        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($key >= 10) break;
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                $filename = uniqid('sighting_') . '.' . $ext;
                
                if (move_uploaded_file($tmp_name, $abs_upload_dir . $filename)) {
                    $uploaded_paths[] = 'uploads/' . $filename;
                }
            }
        }
    }

    $images_json = json_encode($uploaded_paths);

    $stmt = $conn->prepare("INSERT INTO posts (username, description, animal_type, location_text, maps_link, animal_condition, status, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $username, $description, $animal_type, $location_text, $maps_link, $condition, $status, $images_json);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(["success" => true, "message" => "Sighting published!"]);
    } else {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
?>