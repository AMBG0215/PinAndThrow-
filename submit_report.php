<?php
session_start();
require 'database.php';
header('Content-Type: application/json');

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
$imageUrl = $_POST['imageUrl'] ?? '';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$locationName = trim($_POST['locationName'] ?? '');

if (!$category_id || $description === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing required report details.']);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($resident_ID <= 0) {
        if ($resident_email === '' && $resident_username !== '') {
            $resident_email = strtolower($resident_username) . '@pinandthrow.local';
        }

        if ($resident_email !== '') {
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
    }

    if ($resident_ID <= 0) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
        exit;
    }

    $stmtReport = $pdo->prepare("INSERT INTO reports (resident_ID, category_id, description, imageUrl, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmtReport->execute([$resident_ID, $category_id, $description, $imageUrl]);

    $report_ID = (int)$pdo->lastInsertId();

    $stmtLocation = $pdo->prepare("INSERT INTO locations (report_ID, latitude, longitude, locationName) VALUES (?, ?, ?, ?)");
    $stmtLocation->execute([$report_ID, $latitude, $longitude, $locationName]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Report submitted successfully.', 'report_ID' => $report_ID]);

} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit report: ' . $e->getMessage()]);
}
?>