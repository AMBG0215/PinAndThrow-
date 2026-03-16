<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pin and Throw — Login</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
  height: 100%;
  background: #1e8a3c;
  font-family: 'DM Sans', sans-serif;
  -webkit-font-smoothing: antialiased;
  color: #fff;
}

nav {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  background: rgba(0,0,0,0.18);
  border-bottom: 1px solid rgba(255,255,255,0.10);
}

.nav-logo-img {
  height: 30px;
  width: auto;
  mix-blend-mode: screen;
}

.page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 52px);
  padding: 40px 20px;
}

.card {
  width: 100%;
  max-width: 400px;
  text-align: center;
}

.card h1 {
  font-family: 'Syne', sans-serif;
  font-size: 42px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 30px;
}

.field-group {
  margin-bottom: 14px;
  text-align: left;
}

.field-group label {
  display: block;
  font-size: 13.5px;
  font-weight: 500;
  color: #fff;
  margin-bottom: 7px;
}

.field-group input {
  width: 100%;
  background: rgba(210,230,215,0.80);
  border: none;
  border-radius: 30px;
  color: #1a2e22;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  padding: 13px 20px;
  outline: none;
  transition: background .2s, box-shadow .2s;
}

.field-group input:focus {
  background: rgba(235,248,240,0.95);
  box-shadow: 0 0 0 2px rgba(255,255,255,0.50);
}

.forgot {
  text-align: right;
  margin-top: 8px;
  margin-bottom: 20px;
}

.forgot a {
  font-size: 13px;
  color: rgba(255,255,255,0.85);
  text-decoration: none;
}

.forgot a:hover { text-decoration: underline; }

.btn-row {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 30px;
}

.btn-login {
  flex: 1;
  background: linear-gradient(135deg, #3dd668 0%, #1eb350 100%);
  border: none;
  border-radius: 30px;
  color: #fff;
  font-family: 'Syne', sans-serif;
  font-size: 17px;
  font-weight: 700;
  padding: 13px;
  cursor: pointer;
  transition: opacity .18s, transform .12s;
  box-shadow: 0 4px 16px rgba(0,0,0,0.20), inset 0 1px 0 rgba(255,255,255,0.25);
}

.btn-login:hover  { opacity: .88; transform: translateY(-1px); }
.btn-login:active { transform: translateY(0); }

.btn-home {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255,255,255,0.12);
  border: 1.5px solid rgba(255,255,255,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  text-decoration: none;
  flex-shrink: 0;
  transition: background .15s;
}

.btn-home:hover { background: rgba(255,255,255,0.22); }

.register-hint {
  font-size: 12.5px;
  color: rgba(255,255,255,0.65);
  margin-bottom: 5px;
}

.register-link {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  display: block;
  transition: opacity .15s;
}

.register-link:hover { opacity: .75; }

.error-msg {
  background: rgba(180,40,20,0.25);
  border: 1px solid rgba(255,100,80,0.40);
  border-radius: 10px;
  color: #ffd0cc;
  font-size: 12.5px;
  padding: 10px 16px;
  margin-bottom: 16px;
  display: none;
  text-align: left;
}

.error-msg.show { display: block; }
</style>
</head>
<body>

<nav>
  <a href="index.html">
    <img src="resources/logo (2).png" alt="Pin and Throw" class="nav-logo-img">
  </a>
</nav>

<div class="page">
  <div class="card">
    <h1>Login</h1>

    <div class="error-msg" id="errorMsg"></div>

    <div class="field-group">
      <label for="inputUser">Username or Email</label>
      <input type="text" id="inputUser" autocomplete="username">
    </div>

    <div class="field-group">
      <label for="inputPass">Password</label>
      <input type="password" id="inputPass" autocomplete="current-password">
    </div>

    <div class="forgot">
      <a href="#">Forgot Password</a>
    </div>

    <div class="btn-row">
      <button class="btn-login" onclick="doLogin()">Login</button>
      <a href="index.html" class="btn-home" title="Back to Home">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H15v-5h-6v5H4a1 1 0 01-1-1V9.5z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round" fill="none"/>
        </svg>
      </a>
    </div>

    <div class="register-hint">No Account?</div>
    <a href="register.php" class="register-link">Create an Account</a>
  </div>
</div>

<script>
var DEMO_USERS = [
  { username: 'resident',  email: 'resident@pinandthrow.com', password: 'password123', name: 'Juan dela Cruz',        avatar: 'https://i.pravatar.cc/80?img=11' },
  { username: 'feone',     email: 'feone@pinandthrow.com',    password: 'feone123',    name: 'Feone Marie Remoquillo', avatar: 'https://i.pravatar.cc/80?img=47' },
  { username: 'maria',     email: 'maria@pinandthrow.com',    password: 'maria123',    name: 'Maria Santos',          avatar: 'https://i.pravatar.cc/80?img=23' }
];

function getAllUsers() {
  var registered = JSON.parse(localStorage.getItem('pat_registered_users') || '[]');
  return DEMO_USERS.concat(registered);
}

function showError(msg) {
  var el = document.getElementById('errorMsg');
  el.textContent = msg;
  el.classList.add('show');
}

function doLogin() {
  var user = document.getElementById('inputUser').value.trim();
  var pass = document.getElementById('inputPass').value;
  document.getElementById('errorMsg').classList.remove('show');

  if (!user || !pass) {
    showError('Please fill in both fields.');
    return;
  }

  var allUsers = getAllUsers();
  var found = null;
  for (var i = 0; i < allUsers.length; i++) {
    var u = allUsers[i];
    if ((u.username === user || u.email === user) && u.password === pass) { found = u; break; }
  }

  if (!found) {
    showError('Invalid username/email or password.');
    return;
  }

  localStorage.setItem('pat_session', JSON.stringify({ name: found.name, username: found.username, avatar: found.avatar || null }));
  window.location.href = 'index.html';
}

document.getElementById('inputPass').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') doLogin();
});

if (localStorage.getItem('pat_session')) {
  window.location.href = 'index.html';
}
</script>
</body>
</html>
