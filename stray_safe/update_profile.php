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
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$currentUsername = trim((string)$_SESSION['username']);

$stmt = $conn->prepare("SELECT id, avatar FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$userRow) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User account not found.']);
    exit;
}

$userId = (int)$userRow['id'];
$avatarPath = !empty($userRow['avatar']) ? $userRow['avatar'] : 'paw.png';

$bio = trim((string)($_POST['bio'] ?? ''));
$aboutMe = trim((string)($_POST['about_me'] ?? ''));
$contact = trim((string)($_POST['contact'] ?? ''));
$location = trim((string)($_POST['location'] ?? ''));

if ($contact === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Contact information cannot be empty.']);
    exit;
}

if (!empty($_FILES['avatar']['name'])) {
    $err = $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($err !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Image upload error code: ' . $err]);
        exit;
    }

    $tmpName = $_FILES['avatar']['tmp_name'];
    if (!is_uploaded_file($tmpName)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid uploaded file.']);
        exit;
    }

    $ext = strtolower(pathinfo((string)$_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowedExts, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.']);
        exit;
    }

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not create uploads folder.']);
        exit;
    }

    $newFilename = uniqid('avatar_', true) . '.' . $ext;
    if (!move_uploaded_file($tmpName, $uploadDir . $newFilename)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded profile picture.']);
        exit;
    }

    $avatarPath = 'uploads/' . $newFilename;
}

$updateStmt = $conn->prepare("
    UPDATE users
    SET bio = ?, about_me = ?, contact = ?, location = ?, avatar = ?
    WHERE id = ?
");
$updateStmt->bind_param("sssssi", $bio, $aboutMe, $contact, $location, $avatarPath, $userId);

if ($updateStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated!',
        'data' => [
            'bio' => $bio !== '' ? $bio : 'Welcome to my StraySafe profile.',
            'about_me' => $aboutMe !== '' ? $aboutMe : 'Community animal lover and supporter.',
            'contact' => $contact,
            'location' => $location !== '' ? $location : 'Pampanga, Philippines',
            'avatar' => $avatarPath
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error while saving profile.']);
}

$updateStmt->close();
$conn->close();