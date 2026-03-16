<?php
session_start();

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'admin') {
    header('Location: login.php');
    exit();
}

require 'database.php';

$overview = $pdo->query("\n    SELECT\n        COUNT(*) AS total_reports,\n        SUM(LOWER(status) = 'pending') AS pending_reports,\n        SUM(LOWER(status) = 'verified') AS verified_reports,\n        SUM(LOWER(status) = 'inprogress') AS inprogress_reports,\n        SUM(LOWER(status) = 'resolved') AS resolved_reports,\n        SUM(LOWER(status) = 'rejected') AS rejected_reports\n    FROM reports\n")->fetch(PDO::FETCH_ASSOC) ?: [];

$userStats = $pdo->query("\n    SELECT\n        COUNT(*) AS total_users,\n        SUM(role = 'Resident') AS residents,\n        SUM(role = 'Officer') AS officers\n    FROM users\n")->fetch(PDO::FETCH_ASSOC) ?: [];

$categoryRows = $pdo->query("\n    SELECT COALESCE(c.categoryName, 'Uncategorized') AS label, COUNT(*) AS cnt\n    FROM reports r\n    LEFT JOIN categories c ON c.category_id = r.category_id\n    GROUP BY c.category_id, c.categoryName\n    ORDER BY cnt DESC\n    LIMIT 6\n")->fetchAll(PDO::FETCH_ASSOC);

$locationRows = $pdo->query("\n    SELECT COALESCE(NULLIF(l.locationName, ''), 'Unknown location') AS label, COUNT(*) AS cnt\n    FROM reports r\n    LEFT JOIN locations l ON l.report_ID = r.report_ID\n    GROUP BY label\n    ORDER BY cnt DESC\n    LIMIT 6\n")->fetchAll(PDO::FETCH_ASSOC);

$dailyRows = $pdo->query("\n    SELECT DATE(`timestamp`) AS report_date, COUNT(*) AS cnt\n    FROM reports\n    WHERE `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)\n    GROUP BY DATE(`timestamp`)\n    ORDER BY DATE(`timestamp`) ASC\n")->fetchAll(PDO::FETCH_ASSOC);

$dailyMap = [];
foreach ($dailyRows as $row) {
    $dailyMap[$row['report_date']] = (int)$row['cnt'];
}

$last7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i day"));
    $last7Days[] = [
        'label' => date('M d', strtotime($date)),
        'count' => $dailyMap[$date] ?? 0,
    ];
}

$reporterRows = $pdo->query("\n    SELECT\n        u.firstName,\n        u.lastName,\n        COUNT(r.report_ID) AS cnt\n    FROM users u\n    LEFT JOIN reports r ON r.resident_ID = u.user_ID\n    GROUP BY u.user_ID, u.firstName, u.lastName\n    HAVING cnt > 0\n    ORDER BY cnt DESC\n    LIMIT 5\n")->fetchAll(PDO::FETCH_ASSOC);

$maxCategory = !empty($categoryRows) ? max(array_map(fn($r) => (int)$r['cnt'], $categoryRows)) : 1;
$maxLocation = !empty($locationRows) ? max(array_map(fn($r) => (int)$r['cnt'], $locationRows)) : 1;
$maxDaily = !empty($last7Days) ? max(array_map(fn($r) => (int)$r['count'], $last7Days)) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pin and Throw - Analytics</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #eef6f0;
    --card: #ffffff;
    --text: #1c2b22;
    --muted: #6b8f76;
    --accent: #1a7a3e;
    --accent-soft: #dff0e5;
    --border: #d8e7dc;
    --warn: #b07d00;
    --danger: #c0392b;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; color: var(--text); background: var(--bg); }
  .layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
  .sidebar { background: linear-gradient(180deg, #145c2f, #1a7a3e); color: #fff; padding: 20px 16px; }
  .brand { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 4px; }
  .sub { font-size: 11px; color: rgba(255,255,255,.7); margin-bottom: 18px; font-family: 'DM Mono', monospace; }
  .nav-item {
    display: block;
    color: rgba(255,255,255,.8);
    text-decoration: none;
    padding: 9px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    font-size: 13px;
  }
  .nav-item:hover, .nav-item.active { background: rgba(255,255,255,.16); color: #fff; }
  .main { padding: 22px; }
  .top { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; }
  .title { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; }
  .subtitle { color: var(--muted); font-size: 13px; }
  .cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
  .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 14px; }
  .k-label { font-size: 11px; color: var(--muted); margin-bottom: 6px; font-family: 'DM Mono', monospace; }
  .k-value { font-size: 28px; font-weight: 700; font-family: 'Syne', sans-serif; }
  .k-meta { font-size: 12px; color: var(--muted); margin-top: 6px; }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .panel { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 14px; }
  .panel h3 { font-size: 13px; margin-bottom: 10px; font-family: 'DM Mono', monospace; color: var(--muted); }
  .bar { margin-bottom: 10px; }
  .bar-head { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }
  .track { height: 8px; border-radius: 999px; background: #edf4ef; overflow: hidden; border: 1px solid var(--border); }
  .fill { height: 100%; background: linear-gradient(90deg, #1a7a3e, #2bb061); }
  .daily-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 8px; align-items: end; min-height: 130px; }
  .day-col { text-align: center; }
  .day-bar { width: 100%; background: linear-gradient(180deg, #4fca7e, #1a7a3e); border-radius: 8px 8px 4px 4px; min-height: 2px; }
  .day-count { font-size: 11px; color: var(--muted); margin-top: 4px; }
  .day-label { font-size: 10px; color: var(--muted); font-family: 'DM Mono', monospace; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border-bottom: 1px solid var(--border); padding: 8px 2px; font-size: 12.5px; text-align: left; }
  th { font-size: 11px; color: var(--muted); font-family: 'DM Mono', monospace; font-weight: 500; }
  @media (max-width: 980px) {
    .layout { grid-template-columns: 1fr; }
    .cards { grid-template-columns: 1fr 1fr; }
    .grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">Pin and Throw</div>
    <div class="sub">Officer Command Center</div>
    <a class="nav-item" href="admin_dashboard.php">Dashboard</a>
    <a class="nav-item active" href="analytics.php">Analytics</a>
    <a class="nav-item" href="user_management.php">User Management</a>
  </aside>

  <main class="main">
    <div class="top">
      <div>
        <div class="title">Analytics</div>
        <div class="subtitle">Live reporting and user activity overview</div>
      </div>
    </div>

    <section class="cards">
      <div class="card">
        <div class="k-label">Total Reports</div>
        <div class="k-value"><?= (int)($overview['total_reports'] ?? 0) ?></div>
        <div class="k-meta">All-time submissions</div>
      </div>
      <div class="card">
        <div class="k-label">Open Reports</div>
        <div class="k-value"><?= (int)($overview['pending_reports'] ?? 0) + (int)($overview['verified_reports'] ?? 0) + (int)($overview['inprogress_reports'] ?? 0) ?></div>
        <div class="k-meta">Pending + Verified + InProgress</div>
      </div>
      <div class="card">
        <div class="k-label">Resolved</div>
        <div class="k-value"><?= (int)($overview['resolved_reports'] ?? 0) ?></div>
        <div class="k-meta">Closed successfully</div>
      </div>
      <div class="card">
        <div class="k-label">Users</div>
        <div class="k-value"><?= (int)($userStats['total_users'] ?? 0) ?></div>
        <div class="k-meta">Residents: <?= (int)($userStats['residents'] ?? 0) ?>, Officers: <?= (int)($userStats['officers'] ?? 0) ?></div>
      </div>
    </section>

    <section class="grid">
      <div class="panel">
        <h3>Reports by Category</h3>
        <?php if (empty($categoryRows)): ?>
          <div class="subtitle">No report data yet.</div>
        <?php else: ?>
          <?php foreach ($categoryRows as $row):
            $pct = $maxCategory > 0 ? round(((int)$row['cnt'] / $maxCategory) * 100) : 0;
          ?>
            <div class="bar">
              <div class="bar-head"><span><?= htmlspecialchars($row['label']) ?></span><span><?= (int)$row['cnt'] ?></span></div>
              <div class="track"><div class="fill" style="width: <?= $pct ?>%"></div></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="panel">
        <h3>Reports by Location</h3>
        <?php if (empty($locationRows)): ?>
          <div class="subtitle">No location data yet.</div>
        <?php else: ?>
          <?php foreach ($locationRows as $row):
            $pct = $maxLocation > 0 ? round(((int)$row['cnt'] / $maxLocation) * 100) : 0;
          ?>
            <div class="bar">
              <div class="bar-head"><span><?= htmlspecialchars($row['label']) ?></span><span><?= (int)$row['cnt'] ?></span></div>
              <div class="track"><div class="fill" style="width: <?= $pct ?>%"></div></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="panel">
        <h3>Reports (Last 7 Days)</h3>
        <div class="daily-grid">
          <?php foreach ($last7Days as $day):
            $heightPct = $maxDaily > 0 ? max(6, round(($day['count'] / $maxDaily) * 100)) : 6;
          ?>
            <div class="day-col">
              <div class="day-bar" style="height: <?= $heightPct ?>px"></div>
              <div class="day-count"><?= (int)$day['count'] ?></div>
              <div class="day-label"><?= htmlspecialchars($day['label']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="panel">
        <h3>Top Reporters</h3>
        <table>
          <thead>
            <tr><th>Name</th><th>Reports</th></tr>
          </thead>
          <tbody>
            <?php if (empty($reporterRows)): ?>
              <tr><td colspan="2">No reporter activity yet.</td></tr>
            <?php else: ?>
              <?php foreach ($reporterRows as $row): ?>
                <tr>
                  <td><?= htmlspecialchars(trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''))) ?></td>
                  <td><?= (int)$row['cnt'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>
</body>
</html>
