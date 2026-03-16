<?php
session_start();
require 'database.php';
header('Content-Type: application/json');

function normalizeReportReference(string $reference): int {
    $reference = strtoupper(trim($reference));
    if ($reference === '') {
        return 0;
    }

    if (preg_match('/^RPT-(\d+)$/', $reference, $matches)) {
        return (int)$matches[1];
    }

    return is_numeric($reference) ? (int)$reference : 0;
}

$resident_ID = $_SESSION['user_id'] ?? $_SESSION['user_ID'] ?? $_GET['resident_ID'] ?? null;
$resident_ID = is_numeric($resident_ID) ? (int)$resident_ID : 0;
$resident_email = trim($_GET['resident_email'] ?? '');
$report_ID = normalizeReportReference($_GET['report_reference'] ?? $_GET['report_id'] ?? '');

if ($resident_ID <= 0 && $resident_email === '' && $report_ID <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing report lookup details.']);
    exit;
}

try {
    $query = "
        SELECT
            r.report_ID, r.description, r.imageUrl, r.status, r.timestamp,
            c.categoryName,
            l.latitude, l.longitude, l.locationName,
            u.firstName, u.lastName, u.email
        FROM reports r
        LEFT JOIN categories c ON r.category_id = c.category_id
        LEFT JOIN locations l ON r.report_ID = l.report_ID
        LEFT JOIN users u ON u.user_ID = r.resident_ID
        WHERE 1 = 1
    ";

    $params = [];
    if ($resident_ID > 0) {
        $query .= " AND r.resident_ID = ?";
        $params[] = $resident_ID;
    } elseif ($resident_email !== '') {
        $query .= " AND u.email = ?";
        $params[] = $resident_email;
    }

    if ($report_ID > 0) {
        $query .= " AND r.report_ID = ?";
        $params[] = $report_ID;
    }

    $query .= " ORDER BY r.timestamp DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $reports]);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>