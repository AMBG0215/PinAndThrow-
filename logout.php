<?php
session_start();

// Clear all session data for server-side authenticated users.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logging out...</title>
</head>
<body>
<script>
  // Clear client-side auth used by resident demo flow.
  localStorage.removeItem('pat_session');
  window.location.replace('login.php');
</script>
<noscript>
  <meta http-equiv="refresh" content="0;url=login.php">
  <p>You have been logged out. <a href="login.php">Go to login</a>.</p>
</noscript>
</body>
</html>