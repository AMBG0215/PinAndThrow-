<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pin and Throw — Create Account</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
  min-height: 100%;
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
  position: sticky;
  top: 0;
  z-index: 10;
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
  padding: 40px 20px 60px;
}

.card {
  width: 100%;
  max-width: 400px;
  text-align: center;
}

.card h1 {
  font-family: 'Syne', sans-serif;
  font-size: 38px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 6px;
}

.card .subtitle {
  font-size: 12.5px;
  color: rgba(255,255,255,0.58);
  margin-bottom: 32px;
}

.field-group {
  margin-bottom: 16px;
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

.field-group input::placeholder { color: rgba(30,60,40,0.38); }

.password-wrap { position: relative; }
.password-wrap input { padding-right: 48px; }

.toggle-pass {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: rgba(30,80,50,0.50);
  display: flex;
  align-items: center;
  padding: 0;
  transition: color .15s;
}

.toggle-pass:hover { color: rgba(30,80,50,0.85); }

.strength-bar {
  height: 4px;
  border-radius: 4px;
  background: rgba(255,255,255,0.15);
  margin-top: 8px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  border-radius: 4px;
  width: 0%;
  transition: width .3s, background .3s;
}

.strength-label {
  font-size: 10.5px;
  margin-top: 4px;
  padding-left: 4px;
  color: rgba(255,255,255,0.48);
  min-height: 16px;
}

.field-error {
  font-size: 11.5px;
  color: #fca5a5;
  margin-top: 6px;
  padding-left: 6px;
  display: none;
}

.field-error.show { display: block; }

.alert-error {
  background: rgba(180,40,20,0.25);
  border: 1px solid rgba(255,100,80,0.40);
  border-radius: 10px;
  color: #ffd0cc;
  font-size: 12.5px;
  padding: 11px 16px;
  margin-bottom: 18px;
  display: none;
  text-align: left;
  line-height: 1.5;
}

.alert-error.show { display: block; }

.btn-create {
  width: 100%;
  background: linear-gradient(135deg, #3dd668 0%, #1a9048 100%);
  border: none;
  border-radius: 30px;
  color: #fff;
  font-family: 'Syne', sans-serif;
  font-size: 17px;
  font-weight: 700;
  padding: 14px;
  cursor: pointer;
  transition: opacity .18s, transform .12s;
  box-shadow: 0 4px 18px rgba(0,0,0,0.22), inset 0 1px 0 rgba(255,255,255,0.25);
  margin-top: 8px;
  margin-bottom: 26px;
  letter-spacing: .3px;
}

.btn-create:hover  { opacity: .88; transform: translateY(-1px); }
.btn-create:active { transform: translateY(0); }

.login-hint {
  font-size: 12.5px;
  color: rgba(255,255,255,0.60);
  margin-bottom: 5px;
}

.login-link {
  display: block;
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  transition: opacity .15s;
}

.login-link:hover { opacity: .75; }

.success-screen {
  display: none;
  text-align: center;
  padding: 30px 0;
}

.check-circle {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #3dd668, #1a9048);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.20);
}

.success-screen h2 {
  font-family: 'Syne', sans-serif;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 10px;
}

.success-screen p {
  font-size: 13.5px;
  color: rgba(255,255,255,0.68);
  margin-bottom: 30px;
  line-height: 1.65;
}

.btn-go-login {
  display: inline-block;
  background: linear-gradient(135deg, #3dd668, #1a9048);
  border: none;
  border-radius: 30px;
  color: #fff;
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
  padding: 13px 44px;
  cursor: pointer;
  text-decoration: none;
  box-shadow: 0 4px 16px rgba(0,0,0,0.20);
  transition: opacity .18s, transform .12s;
}

.btn-go-login:hover { opacity: .88; transform: translateY(-1px); }
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

    <div id="formView">
      <h1>Create Account</h1>
      <p class="subtitle">Join the community and help keep Barangay Pio Del Pilar clean.</p>

      <div class="alert-error" id="alertError"></div>

      <div class="field-group">
        <label for="inputName">Full Name</label>
        <input type="text" id="inputName" placeholder="e.g. Juan dela Cruz" autocomplete="name">
        <div class="field-error" id="errName">Please enter your full name.</div>
      </div>

      <div class="field-group">
        <label for="inputEmail">Email Address</label>
        <input type="email" id="inputEmail" placeholder="you@example.com" autocomplete="email">
        <div class="field-error" id="errEmail">Please enter a valid email address.</div>
      </div>

      <div class="field-group">
        <label for="inputPass">Password</label>
        <div class="password-wrap">
          <input type="password" id="inputPass" placeholder="Create a password" autocomplete="new-password" oninput="checkStrength()">
          <button type="button" class="toggle-pass" onclick="toggleVis('inputPass', this)" tabindex="-1">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8" fill="none"/>
              <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" fill="none"/>
            </svg>
          </button>
        </div>
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <div class="strength-label" id="strengthLabel"></div>
        <div class="field-error" id="errPass">Password must be at least 8 characters.</div>
      </div>

      <div class="field-group">
        <label for="inputConfirm">Confirm Password</label>
        <div class="password-wrap">
          <input type="password" id="inputConfirm" placeholder="Repeat your password" autocomplete="new-password">
          <button type="button" class="toggle-pass" onclick="toggleVis('inputConfirm', this)" tabindex="-1">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8" fill="none"/>
              <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" fill="none"/>
            </svg>
          </button>
        </div>
        <div class="field-error" id="errConfirm">Passwords do not match.</div>
      </div>

      <button class="btn-create" onclick="doRegister()">Create Account</button>

      <div class="login-hint">Already have an account?</div>
      <a href="login.php" class="login-link">Login</a>
    </div>

    <div class="success-screen" id="successScreen">
      <div class="check-circle">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
          <path d="M5 13l4 4L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h2>Account Created!</h2>
      <p id="successMsg">Your account has been created. You can now login and start reporting waste in your community.</p>
      <a href="login.php" class="btn-go-login">Go to Login</a>
    </div>

  </div>
</div>

<script>
function toggleVis(id, btn) {
  var inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.style.opacity = inp.type === 'text' ? '1' : '';
}

function checkStrength() {
  var val  = document.getElementById('inputPass').value;
  var fill = document.getElementById('strengthFill');
  var lbl  = document.getElementById('strengthLabel');
  if (!val) { fill.style.width = '0'; lbl.textContent = ''; return; }
  var score = 0;
  if (val.length >= 8)           score++;
  if (val.length >= 12)          score++;
  if (/[A-Z]/.test(val))        score++;
  if (/[0-9]/.test(val))        score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  var levels = [
    { pct: '20%',  color: '#ef4444', label: 'Very weak'   },
    { pct: '40%',  color: '#f97316', label: 'Weak'        },
    { pct: '60%',  color: '#eab308', label: 'Fair'        },
    { pct: '80%',  color: '#84cc16', label: 'Strong'      },
    { pct: '100%', color: '#22c55e', label: 'Very strong' }
  ];
  var lvl = levels[Math.min(score, 4)];
  fill.style.width      = lvl.pct;
  fill.style.background = lvl.color;
  lbl.textContent       = lvl.label;
  lbl.style.color       = lvl.color;
}

function showFieldError(id, show) {
  document.getElementById(id).classList.toggle('show', show);
}

function getExistingUsers() {
  return JSON.parse(localStorage.getItem('pat_registered_users') || '[]');
}

function doRegister() {
  var alert  = document.getElementById('alertError');
  alert.classList.remove('show');
  ['errName','errEmail','errPass','errConfirm'].forEach(function(id) {
    document.getElementById(id).classList.remove('show');
  });

  var name    = document.getElementById('inputName').value.trim();
  var email   = document.getElementById('inputEmail').value.trim();
  var pass    = document.getElementById('inputPass').value;
  var confirm = document.getElementById('inputConfirm').value;

  var valid = true;

  if (!name) {
    showFieldError('errName', true); valid = false;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showFieldError('errEmail', true); valid = false;
  }

  if (pass.length < 8) {
    showFieldError('errPass', true); valid = false;
  }

  if (confirm !== pass || !confirm) {
    showFieldError('errConfirm', true); valid = false;
  }

  if (!valid) return;

  var existing = getExistingUsers();
  for (var i = 0; i < existing.length; i++) {
    if (existing[i].email.toLowerCase() === email.toLowerCase()) {
      alert.textContent = 'An account with that email already exists. Try logging in.';
      alert.classList.add('show');
      return;
    }
  }

  var username = email.split('@')[0].replace(/[^A-Za-z0-9_]/g, '_');

  var newUser = {
    username:  username,
    email:     email,
    password:  pass,
    name:      name,
    avatar:    null,
    createdAt: new Date().toISOString()
  };

  existing.push(newUser);
  localStorage.setItem('pat_registered_users', JSON.stringify(existing));

  document.getElementById('formView').style.display      = 'none';
  document.getElementById('successScreen').style.display = 'block';
  document.getElementById('successMsg').textContent      = 'Welcome, ' + name.split(' ')[0] + '! Your account is ready. You can now log in and start reporting waste in your community.';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && document.getElementById('formView').style.display !== 'none') doRegister();
});

if (localStorage.getItem('pat_session')) {
  window.location.href = 'index.html';
}
</script>
</body>
</html>