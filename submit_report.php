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

const PIO_DEL_PILAR_MIN_LAT = 14.5475105;
const PIO_DEL_PILAR_MAX_LAT = 14.5611702;
const PIO_DEL_PILAR_MIN_LNG = 121.0066859;
const PIO_DEL_PILAR_MAX_LNG = 121.0160332;

const PIO_DEL_PILAR_POLYGON = [
    [14.5501993, 121.0066859],
    [14.550116, 121.0069102],
    [14.5500694, 121.0071442],
    [14.5500364, 121.0071968],
    [14.5499575, 121.0072364],
    [14.5495472, 121.0072601],
    [14.5494163, 121.00733],
    [14.5490087, 121.0075821],
    [14.548918, 121.0076726],
    [14.5488699, 121.0078416],
    [14.5486207, 121.0087686],
    [14.5485641, 121.0088479],
    [14.5483419, 121.0089767],
    [14.5480935, 121.0090864],
    [14.5481558, 121.0093324],
    [14.5481657, 121.0093908],
    [14.5482207, 121.0097138],
    [14.5482336, 121.0097778],
    [14.5482414, 121.0098262],
    [14.5483803, 121.010692],
    [14.5483925, 121.0107683],
    [14.5484046, 121.0108507],
    [14.5484316, 121.011035],
    [14.5485356, 121.0117304],
    [14.5486011, 121.0119729],
    [14.5486551, 121.012135],
    [14.5487262, 121.0123009],
    [14.5487507, 121.0123571],
    [14.5475105, 121.0130279],
    [14.5475509, 121.0131088],
    [14.5476008, 121.013209],
    [14.5476207, 121.0132489],
    [14.5476392, 121.0132859],
    [14.5476565, 121.0133206],
    [14.5477018, 121.0134113],
    [14.5477093, 121.0134311],
    [14.547714, 121.0134463],
    [14.5477239, 121.0135038],
    [14.5477299, 121.013535],
    [14.5477373, 121.0135647],
    [14.5477485, 121.0135923],
    [14.5477877, 121.0136729],
    [14.5478006, 121.0136993],
    [14.5479932, 121.0140775],
    [14.548103, 121.0142973],
    [14.5481783, 121.0144571],
    [14.548214, 121.0145304],
    [14.5482376, 121.0145771],
    [14.5483545, 121.0148088],
    [14.5483969, 121.014946],
    [14.5485374, 121.0157049],
    [14.5485736, 121.0159088],
    [14.5485866, 121.015982],
    [14.5485927, 121.0160162],
    [14.5485957, 121.0160332],
    [14.5487805, 121.0159339],
    [14.551247, 121.0154158],
    [14.5517113, 121.0153186],
    [14.5551083, 121.0146242],
    [14.5552951, 121.0145975],
    [14.5584958, 121.0139928],
    [14.5586098, 121.0139823],
    [14.558826, 121.013978],
    [14.559025, 121.0140101],
    [14.5598927, 121.0143296],
    [14.5600843, 121.0143916],
    [14.560991, 121.0147408],
    [14.5611702, 121.0148057],
    [14.561094, 121.0145215],
    [14.5610414, 121.0143049],
    [14.5609491, 121.0140119],
    [14.5608136, 121.0136279],
    [14.5607352, 121.0134393],
    [14.560664, 121.0132711],
    [14.5605808, 121.0130764],
    [14.5604763, 121.0128677],
    [14.5601185, 121.0121423],
    [14.5599405, 121.0117855],
    [14.5597035, 121.0113399],
    [14.5593932, 121.0107243],
    [14.5589296, 121.0098129],
    [14.5581056, 121.0082205],
    [14.557769, 121.0074923],
    [14.5554075, 121.0087778],
    [14.5549751, 121.0090165],
    [14.5527963, 121.0101666],
    [14.5526816, 121.0099815],
    [14.5523908, 121.0092371],
    [14.5522319, 121.0083941],
    [14.5522022, 121.0082367],
    [14.5522394, 121.0079922],
    [14.5521991, 121.0075923],
    [14.5521712, 121.0069537],
    [14.5521969, 121.0069084],
    [14.5519095, 121.0069861],
    [14.5517147, 121.0070375],
    [14.5514318, 121.0071122],
    [14.5511097, 121.0071973],
    [14.550776, 121.0072897],
    [14.5504088, 121.0073898],
    [14.5503696, 121.0074002],
    [14.5502766, 121.0070232],
    [14.5501993, 121.0066859],
];

function isInsidePolygon(float $latitude, float $longitude, array $polygon): bool {
    $inside = false;
    $count = count($polygon);

    for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
        $yi = (float)$polygon[$i][0];
        $xi = (float)$polygon[$i][1];
        $yj = (float)$polygon[$j][0];
        $xj = (float)$polygon[$j][1];

        $intersects = (($yi > $latitude) !== ($yj > $latitude))
            && ($longitude < (($xj - $xi) * ($latitude - $yi)) / ($yj - $yi) + $xi);

        if ($intersects) {
            $inside = !$inside;
        }
    }

    return $inside;
}

function isWithinPioDelPilar($latitude, $longitude): bool {
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        return false;
    }

    $latitude = (float)$latitude;
    $longitude = (float)$longitude;

    if ($latitude < PIO_DEL_PILAR_MIN_LAT || $latitude > PIO_DEL_PILAR_MAX_LAT || $longitude < PIO_DEL_PILAR_MIN_LNG || $longitude > PIO_DEL_PILAR_MAX_LNG) {
        return false;
    }

    return isInsidePolygon($latitude, $longitude, PIO_DEL_PILAR_POLYGON);
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

if (!isWithinPioDelPilar($latitude, $longitude)) {
    echo json_encode(['status' => 'error', 'message' => 'Please pin a location within Brgy. Pio Del Pilar.']);
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