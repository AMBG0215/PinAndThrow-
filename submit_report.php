<?php
session_start();
require 'database.php';
header('Content-Type: application/json');

function resolveExistingImagePath(string $imageUrl): string {
    $imageUrl = trim(str_replace('\\', '/', $imageUrl));
    if ($imageUrl === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $imageUrl) || str_starts_with($imageUrl, 'data:')) {
        return $imageUrl;
    }

    $imageUrl = ltrim($imageUrl, '/');
    if (preg_match('#^uploads/reports/#i', $imageUrl)) {
        return $imageUrl;
    }

    $basename = basename($imageUrl);
    $candidate = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $basename;
    if (is_file($candidate)) {
        return 'uploads/reports/' . $basename;
    }

    return '';
}

function storeUploadedReportImage(array $file): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    $tmpPath = $file['tmp_name'] ?? '';
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new RuntimeException('Invalid uploaded image.');
    }

    $mimeType = mime_content_type($tmpPath) ?: '';
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedTypes[$mimeType])) {
        throw new RuntimeException('Only JPG, PNG, GIF, and WEBP images are allowed.');
    }

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reports';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Unable to create the uploads directory.');
    }

    $filename = sprintf('report_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $allowedTypes[$mimeType]);
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpPath, $destination)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    return 'uploads/reports/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

function splitResidentName(string $fullName): array {
    $fullName = trim($fullName);
    if ($fullName === '') {
        return ['Resident', 'User'];
    }
    $parts = preg_split('/\s+/', $fullName);
    $first = $parts[0] ?? 'Resident';
    $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Resident';
    return [$first, $last];
}

$resident_ID = $_SESSION['user_id'] ?? $_SESSION['user_ID'] ?? $_POST['resident_ID'] ?? null;
$resident_ID = is_numeric($resident_ID) ? (int)$resident_ID : 0;

$resident_name = trim($_POST['resident_name'] ?? '');
$resident_username = trim($_POST['resident_username'] ?? '');
$resident_email = trim($_POST['resident_email'] ?? '');

$category_id = $_POST['category_id'] ?? null;
$description = trim($_POST['description'] ?? '');
$imageUrl = resolveExistingImagePath($_POST['imageUrl'] ?? '');
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$locationName = trim($_POST['locationName'] ?? '');

if (!$category_id || $description === '' || $resident_name === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing required report details.']);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($resident_ID <= 0 && $resident_email !== '') {
        $findResident = $pdo->prepare("SELECT user_ID FROM users WHERE email = ? LIMIT 1");
        $findResident->execute([$resident_email]);
        $existing = $findResident->fetch(PDO::FETCH_ASSOC);

        if ($existing && isset($existing['user_ID'])) {
            $resident_ID = (int)$existing['user_ID'];
        } else {
            [$firstName, $lastName] = splitResidentName($resident_name);
            $createResident = $pdo->prepare("INSERT INTO users (firstName, lastName, email, role, password) VALUES (?, ?, ?, 'Resident', NULL)");
            $createResident->execute([$firstName, $lastName, $resident_email]);
            $resident_ID = (int)$pdo->lastInsertId();
        }
    }

    if (isset($_FILES['report_image']) && ($_FILES['report_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $imageUrl = storeUploadedReportImage($_FILES['report_image']);
    }

    $reportResidentId = $resident_ID > 0 ? $resident_ID : null;

    $stmtReport = $pdo->prepare("INSERT INTO reports (resident_ID, category_id, description, imageUrl, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmtReport->execute([$reportResidentId, $category_id, $description, $imageUrl]);

    $report_ID = (int)$pdo->lastInsertId();

    $stmtLocation = $pdo->prepare("INSERT INTO locations (report_ID, latitude, longitude, locationName) VALUES (?, ?, ?, ?)");
    $stmtLocation->execute([$report_ID, $latitude, $longitude, $locationName]);

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Report submitted successfully.',
        'report_ID' => $report_ID,
        'tracking_enabled' => $reportResidentId !== null,
    ]);

} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit report: ' . $e->getMessage()]);
}
?>