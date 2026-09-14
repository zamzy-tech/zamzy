<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Redirect if already logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'admin';
    $password = $_POST['password'] ?? '';
    $username = trim($_POST['username'] ?? '');

    try {
        if ($role === 'admin') {
            // Admin Authentication
            $stmt = $pdo->query("SELECT admin_password FROM settings LIMIT 1");
            $stored_hash = $stmt->fetchColumn();

            if ($stored_hash && password_verify($password, $stored_hash)) {
                $_SESSION['authenticated'] = true;
                $_SESSION['user_type'] = 'admin';
                $_SESSION['display_name'] = 'Administrator';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid administrative password.';
            }
        } else {
            // Staff Authentication
            if (empty($username) || empty($password)) {
                $error = 'Please enter both Username and Password.';
            } else {
                $stmt = $pdo->prepare("SELECT * FROM staff_users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $staff = $stmt->fetch();

                if ($staff) {
                    if ($staff['status'] !== 'active') {
                        $error = 'This staff account has been deactivated.';
                    } elseif (password_verify($password, $staff['password'])) {
                        $_SESSION['authenticated'] = true;
                        $_SESSION['user_type'] = 'staff';
                        $_SESSION['user_id'] = $staff['id'];
                        $_SESSION['username'] = $staff['username'];
                        $_SESSION['display_name'] = $staff['name'];
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Invalid username or password.';
                    }
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – The Expert Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap');

  :root {
    --bg-main: #0A0A0C;
    --bg-surface: #121215;
    --bg-card: #18181C;
    --bg-elev: #232328;
    --border-color: rgba(244, 244, 240, 0.08);
    --border-color-soft: rgba(244, 244, 240, 0.04);
    
    --text-primary: #F4F4F0;
    --text-secondary: #C9C9C2;
    --text-muted: #80807A;
    
    --lime: #D4FF3D;
    --lime-deep: #9CCB1F;
    --lime-glow: rgba(212, 255, 61, 0.20);
    
    --font-heading: 'Outfit', 'Inter Tight', sans-serif;
    --font-body: 'Inter Tight', 'Inter', sans-serif;
    --font-mono: 'Geist Mono', monospace;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: var(--font-body);
    background-color: var(--bg-main);
    color: var(--text-primary);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 16px;
  }
  .login-card {
    background-color: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 40px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.7);
    text-align: center;
    animation: fadeIn 0.45s ease-out;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .logo-area {
    margin-bottom: 28px;
  }
  .logo {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: var(--lime);
    font-family: var(--font-heading);
    display: inline-block;
  }
  .tagline {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
  }
  .welcome-text {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 30px;
    line-height: 1.6;
  }
  .form-group {
    text-align: left;
    margin-bottom: 22px;
  }
  label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }
  .password-container {
    position: relative;
  }
  input[type="password"], input[type="text"] {
    width: 100%;
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: 12px;
    padding: 14px 18px;
    font-size: 15px;
    color: var(--text-primary);
    outline: none;
    transition: all 0.3s;
    font-family: var(--font-body);
  }
  input:focus {
    border-color: var(--lime);
    background: var(--bg-elev);
    box-shadow: 0 0 0 3px var(--lime-glow);
  }
  .toggle-password {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 13px;
    user-select: none;
    outline: none;
    font-weight: 600;
  }
  .toggle-password:hover {
    color: var(--text-primary);
  }
  .btn-submit {
    width: 100%;
    background: var(--lime);
    color: var(--bg-main);
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: 0.5px;
    transition: all 0.25s ease;
    box-shadow: 0 4px 14px rgba(212, 255, 61, 0.2);
    margin-top: 10px;
    font-family: inherit;
  }
  .btn-submit:hover {
    transform: translateY(-1.5px);
    background: var(--lime-deep);
    box-shadow: 0 6px 24px rgba(212, 255, 61, 0.25);
  }
  .btn-submit:active {
    transform: translateY(0);
  }
  .alert {
    background: rgba(248, 113, 113, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.25);
    color: #f87171;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    margin-bottom: 20px;
    text-align: left;
    line-height: 1.4;
  }
  .info-badge {
    background: var(--bg-card);
    border: 1px dashed var(--border-color);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    color: var(--lime);
    margin-top: 24px;
    display: inline-block;
    line-height: 1.5;
  }
  .role-tabs {
    display: flex;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    margin-bottom: 24px;
    padding: 4px;
    gap: 4px;
  }
  .role-tab {
    flex: 1;
    padding: 10px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-muted);
    background: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
  }
  .role-tab.active {
    color: var(--bg-main);
    background: var(--lime);
  }
</style>
</head>
<body>

<div class="login-card">
  <div class="logo-area">
    <div class="logo">⚡ The Expert Hub</div>
    <div class="tagline">Invoice Administration</div>
  </div>

  <div class="role-tabs">
    <button type="button" class="role-tab active" id="tabAdmin" onclick="setRole('admin')">🔑 Administrator</button>
    <button type="button" class="role-tab" id="tabStaff" onclick="setRole('staff')">👥 Staff Member</button>
  </div>

  <p class="welcome-text" id="welcomeText">Sign in to manage and issue billing invoices securely.</p>

  <?php if (!empty($error)): ?>
    <div class="alert">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <input type="hidden" name="role" id="loginRole" value="admin">

    <div class="form-group" id="usernameGroup" style="display: none; text-align: left; margin-bottom: 18px;">
      <label for="username">Staff Username</label>
      <input type="text" name="username" id="username" placeholder="Enter staff username" autocomplete="username">
    </div>

    <div class="form-group" style="text-align: left;">
      <label for="password" id="passwordLabel">Security Password</label>
      <div class="password-container">
        <input type="password" name="password" id="password" placeholder="Enter administrative password" required autocomplete="current-password">
        <button type="button" class="toggle-password" onclick="togglePass()">Show</button>
      </div>
    </div>

    <button type="submit" class="btn-submit">Authenticate</button>
  </form>

  <div class="info-badge" id="infoBadge">
    💡 Default password is <strong style="color: #fff; font-family: monospace">admin123</strong>.<br>
    Please change it inside Settings upon successful login.
  </div>
</div>

<script>
function setRole(role) {
  document.getElementById('loginRole').value = role;
  
  const tabAdmin = document.getElementById('tabAdmin');
  const tabStaff = document.getElementById('tabStaff');
  const usernameGroup = document.getElementById('usernameGroup');
  const usernameInput = document.getElementById('username');
  const passwordLabel = document.getElementById('passwordLabel');
  const passwordInput = document.getElementById('password');
  const infoBadge = document.getElementById('infoBadge');
  const welcomeText = document.getElementById('welcomeText');
  
  if (role === 'admin') {
    tabAdmin.classList.add('active');
    tabStaff.classList.remove('active');
    usernameGroup.style.display = 'none';
    usernameInput.required = false;
    infoBadge.style.display = 'inline-block';
    welcomeText.textContent = 'Sign in to manage and issue billing invoices securely.';
    
    passwordLabel.textContent = 'Security Password';
    passwordInput.placeholder = 'Enter administrative password';
  } else {
    tabAdmin.classList.remove('active');
    tabStaff.classList.add('active');
    usernameGroup.style.display = 'block';
    usernameInput.required = true;
    infoBadge.style.display = 'none';
    welcomeText.textContent = 'Sign in using your staff credentials assigned by the administrator.';
    
    passwordLabel.textContent = 'Account Password';
    passwordInput.placeholder = 'Enter staff password';
  }
}

function togglePass() {
  const input = document.getElementById('password');
  const btn = document.querySelector('.toggle-password');
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = 'Hide';
  } else {
    input.type = 'password';
    btn.textContent = 'Show';
  }
}
</script>

</body>
</html>
