<?php
session_start();

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'admin') {
    header('Location: login.php');
    exit();
}

require 'database.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? '';
    $allowedRoles = ['Resident', 'Officer'];

    if ($userId > 0 && in_array($role, $allowedRoles, true)) {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE user_ID = ?');
        $stmt->execute([$role, $userId]);
        header('Location: user_management.php?msg=role_updated');
        exit();
    }

    header('Location: user_management.php?msg=role_failed');
    exit();
}

$search = trim($_GET['q'] ?? '');
$whereSql = '';
$params = [];
if ($search !== '') {
    $whereSql = 'WHERE u.firstName LIKE :q OR u.lastName LIKE :q OR u.email LIKE :q';
    $params[':q'] = '%' . $search . '%';
}

$userStats = $pdo->query("\n    SELECT\n        COUNT(*) AS total_users,\n        SUM(role = 'Resident') AS residents,\n        SUM(role = 'Officer') AS officers\n    FROM users\n")->fetch(PDO::FETCH_ASSOC) ?: [];

$sql = "\n    SELECT\n        u.user_ID,\n        u.firstName,\n        u.lastName,\n        u.email,\n        u.role,\n        COUNT(r.report_ID) AS report_count,\n        MAX(r.timestamp) AS last_report\n    FROM users u\n    LEFT JOIN reports r ON r.resident_ID = u.user_ID\n    $whereSql\n    GROUP BY u.user_ID, u.firstName, u.lastName, u.email, u.role\n    ORDER BY u.user_ID DESC\n";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
$notice = '';
$noticeClass = 'ok';
if ($msg === 'role_updated') {
    $notice = 'User role updated successfully.';
} elseif ($msg === 'role_failed') {
    $notice = 'Failed to update role. Please try again.';
    $noticeClass = 'err';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pin and Throw - User Management</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #eef6f0;
    --card: #fff;
    --text: #1c2b22;
    --muted: #6b8f76;
    --accent: #1a7a3e;
    --accent-soft: #e4f4ea;
    --border: #d7e7dc;
    --danger: #c0392b;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }
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
  .top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .title { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; }
  .subtitle { color: var(--muted); font-size: 13px; }
  .stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
  .stat { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 14px; }
  .stat .label { font-size: 11px; color: var(--muted); font-family: 'DM Mono', monospace; margin-bottom: 6px; }
  .stat .value { font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 700; }
  .panel { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 14px; }
  .toolbar { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
  .search-form { display: flex; gap: 8px; width: 100%; max-width: 500px; }
  .search-form input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 9px 11px;
    outline: none;
    font-size: 13px;
  }
  .search-form button {
    border: 1px solid var(--accent);
    background: var(--accent);
    color: #fff;
    border-radius: 8px;
    padding: 9px 12px;
    cursor: pointer;
    font-size: 13px;
  }
  .notice { padding: 10px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; }
  .notice.ok { background: var(--accent-soft); border: 1px solid #b7dec5; color: #155b30; }
  .notice.err { background: #fdebe7; border: 1px solid #f0c1b8; color: var(--danger); }
  table { width: 100%; border-collapse: collapse; }
  th, td { border-bottom: 1px solid var(--border); text-align: left; padding: 9px 4px; font-size: 12.5px; }
  th { font-size: 11px; color: var(--muted); font-family: 'DM Mono', monospace; font-weight: 500; }
  .role-form { display: flex; align-items: center; gap: 8px; }
  .role-form select {
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 7px 8px;
    background: #f8fcf9;
    font-size: 12px;
  }
  .role-form button {
    border: 1px solid var(--accent);
    background: #fff;
    color: var(--accent);
    border-radius: 7px;
    padding: 7px 9px;
    cursor: pointer;
    font-size: 12px;
  }
  .empty { color: var(--muted); font-size: 13px; padding: 18px 0; }
  @media (max-width: 980px) {
    .layout { grid-template-columns: 1fr; }
    .stats { grid-template-columns: 1fr; }
    .toolbar { flex-direction: column; }
  }
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">Pin and Throw</div>
    <div class="sub">Officer Command Center</div>
    <a class="nav-item" href="admin_dashboard.php">Dashboard</a>
    <a class="nav-item" href="analytics.php">Analytics</a>
    <a class="nav-item active" href="user_management.php">User Management</a>
  </aside>

  <main class="main">
    <div class="top">
      <div>
        <div class="title">User Management</div>
        <div class="subtitle">Manage residents and officers with live database updates</div>
      </div>
    </div>

    <section class="stats">
      <div class="stat">
        <div class="label">Total Users</div>
        <div class="value"><?= (int)($userStats['total_users'] ?? 0) ?></div>
      </div>
      <div class="stat">
        <div class="label">Residents</div>
        <div class="value"><?= (int)($userStats['residents'] ?? 0) ?></div>
      </div>
      <div class="stat">
        <div class="label">Officers</div>
        <div class="value"><?= (int)($userStats['officers'] ?? 0) ?></div>
      </div>
    </section>

    <section class="panel">
      <?php if ($notice !== ''): ?>
        <div class="notice <?= $noticeClass ?>"><?= htmlspecialchars($notice) ?></div>
      <?php endif; ?>

      <div class="toolbar">
        <form class="search-form" method="GET" action="user_management.php">
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email">
          <button type="submit">Search</button>
        </form>
      </div>

      <?php if (empty($users)): ?>
        <div class="empty">No users found for this filter.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Reports</th>
              <th>Last Report</th>
              <th>Role</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><?= (int)$u['user_ID'] ?></td>
                <td><?= htmlspecialchars(trim(($u['firstName'] ?? '') . ' ' . ($u['lastName'] ?? ''))) ?></td>
                <td><?= htmlspecialchars((string)$u['email']) ?></td>
                <td><?= (int)$u['report_count'] ?></td>
                <td><?= $u['last_report'] ? htmlspecialchars(date('M d, Y h:i A', strtotime($u['last_report']))) : '-' ?></td>
                <td>
                  <form class="role-form" method="POST" action="user_management.php">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" value="<?= (int)$u['user_ID'] ?>">
                    <select name="role">
                      <option value="Resident" <?= ($u['role'] === 'Resident') ? 'selected' : '' ?>>Resident</option>
                      <option value="Officer" <?= ($u['role'] === 'Officer') ? 'selected' : '' ?>>Officer</option>
                    </select>
                    <button type="submit">Save</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</div>
</body>
</html>
