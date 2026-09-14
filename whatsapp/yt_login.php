<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// AJAX actions for Sign Up OTP
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    
    if ($action === 'send_otp') {
        $username = trim($_POST['username'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($phone) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Please fill in all fields.']);
            exit;
        }
        
        // Normalize phone number (digits only)
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) < 10) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid WhatsApp number with country code (e.g. 91xxxxxxxxxx).']);
            exit;
        }
        
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM youtubers WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'error' => 'Username is already taken.']);
                exit;
            }
            
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);
            
            // Save to session
            $_SESSION['signup_otp'] = $otp;
            $_SESSION['signup_data'] = [
                'username' => $username,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];
            $_SESSION['signup_otp_time'] = time();
            
            // Fetch Admin WhatsApp Gateway configuration
            $g_stmt = $pdo->query("SELECT whatsapp_gateway_url, whatsapp_gateway_token FROM settings LIMIT 1");
            $gateway = $g_stmt->fetch();
            
            if (!$gateway || empty($gateway['whatsapp_gateway_url'])) {
                echo json_encode(['success' => false, 'error' => 'Admin WhatsApp gateway is not configured. Please contact support.']);
                exit;
            }
            
            $target_url = $gateway['whatsapp_gateway_url'];
            if (strpos($target_url, '?') !== false) {
                $target_url .= '&session=default';
            } else {
                $target_url .= '?session=default';
            }
            
            $message = "🔑 *THE EXPERT HUB*\n\nYour OTP code for signing up on the YouTube Creator Dashboard is: *{$otp}*\n\nThis code is valid for 10 minutes. Please do not share it with anyone.";
            
            $payload = json_encode([
                'to' => $phone,
                'message' => $message,
                'token' => $gateway['whatsapp_gateway_token'],
                'apikey' => $gateway['whatsapp_gateway_token']
            ]);
            
            $ch = curl_init($target_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $res = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $gateway_res = json_decode($res, true);
            if ($http_code === 200 && isset($gateway_res['success']) && $gateway_res['success']) {
                echo json_encode(['success' => true]);
            } else {
                $err = $gateway_res['error'] ?? 'HTTP ' . $http_code;
                echo json_encode(['success' => false, 'error' => 'Failed to send WhatsApp OTP: ' . $err]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'verify_otp') {
        $otp = trim($_POST['otp'] ?? '');
        
        if (empty($otp)) {
            echo json_encode(['success' => false, 'error' => 'Please enter the OTP code.']);
            exit;
        }
        
        if (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_data'])) {
            echo json_encode(['success' => false, 'error' => 'No active signup session found. Please request a new OTP.']);
            exit;
        }
        
        // Check timeout (10 mins)
        if (time() - $_SESSION['signup_otp_time'] > 600) {
            unset($_SESSION['signup_otp']);
            unset($_SESSION['signup_data']);
            unset($_SESSION['signup_otp_time']);
            echo json_encode(['success' => false, 'error' => 'OTP has expired. Please request a new one.']);
            exit;
        }
        
        if ((int)$otp !== (int)$_SESSION['signup_otp']) {
            echo json_encode(['success' => false, 'error' => 'Invalid OTP code. Please try again.']);
            exit;
        }
        
        try {
            $data = $_SESSION['signup_data'];
            
            // Insert YouTuber
            $ins = $pdo->prepare("INSERT INTO youtubers (username, password, phone, is_active) VALUES (?, ?, ?, 1)");
            $ins->execute([$data['username'], $data['password'], $data['phone']]);
            $new_user_id = $pdo->lastInsertId();
            
            // Log in the user immediately
            $_SESSION['yt_user_id'] = $new_user_id;
            $_SESSION['yt_username'] = $data['username'];
            
            // Clear session data
            unset($_SESSION['signup_otp']);
            unset($_SESSION['signup_data']);
            unset($_SESSION['signup_otp_time']);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to create user: ' . $e->getMessage()]);
        }
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}

// Redirect if already logged in as YouTuber
if (isset($_SESSION['yt_user_id'])) {
    header('Location: yt_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM youtubers WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_active'] == 1) {
                    $_SESSION['yt_user_id'] = $user['id'];
                    $_SESSION['yt_username'] = $user['username'];
                    header('Location: yt_dashboard.php');
                    exit;
                } else {
                    $error = 'Your account has been deactivated.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Creator Authentication &amp; Portal — ZAMZY</title>
<link rel="shortcut icon" href="zamzy_logo.png" type="image/png">
<link rel="icon" href="zamzy_logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg-main: #05060b;
    --bg-surface: #0a0c16;
    --bg-card: rgba(14, 17, 30, 0.85);
    --bg-elev: #141829;
    --border-color: rgba(255, 255, 255, 0.08);
    --border-color-soft: rgba(255, 255, 255, 0.04);
    
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    
    --cyan: #00ffcc;
    --cyan-glow: rgba(0, 255, 204, 0.22);
    --lime: #FF3D3D; /* YouTube Red Accent */
    --lime-deep: #CC2222;
    --lime-glow: rgba(255, 61, 61, 0.25);
    
    --font-heading: 'Space Grotesk', sans-serif;
    --font-body: 'Inter', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
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
    background-image: 
      radial-gradient(circle at 15% 15%, rgba(157, 78, 221, 0.12) 0%, transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(0, 255, 204, 0.10) 0%, transparent 45%),
      linear-gradient(to bottom, #05060b, #070913);
    background-attachment: fixed;
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
    margin-bottom: 24px;
  }
  .logo {
    font-size: 26px;
    font-weight: 700;
    font-family: var(--font-heading);
    letter-spacing: -0.5px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .logo-highlight {
    color: var(--lime);
  }
  h2 {
    font-size: 20px;
    font-weight: 600;
    font-family: var(--font-heading);
    color: var(--text-primary);
    margin-bottom: 8px;
  }
  .subtitle {
    font-size: 13.5px;
    color: var(--text-secondary);
    margin-bottom: 28px;
    line-height: 1.5;
  }
  .form-group {
    text-align: left;
    margin-bottom: 20px;
  }
  label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 6px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  input[type="text"], input[type="password"] {
    width: 100%;
    padding: 14px 16px;
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    color: var(--text-primary);
    font-family: var(--font-body);
    transition: all 0.2s ease;
  }
  input:focus {
    outline: none;
    border-color: var(--lime);
    box-shadow: 0 0 0 3px var(--lime-glow);
  }
  .btn-submit {
    width: 100%;
    padding: 14px;
    background-color: var(--lime);
    color: #FFFFFF;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    font-family: var(--font-body);
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 10px;
  }
  .btn-submit:hover {
    background-color: var(--lime-deep);
  }
  .error-box {
    background-color: rgba(255, 61, 61, 0.1);
    border: 1px solid rgba(255, 61, 61, 0.2);
    color: #FF5A5A;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 20px;
    text-align: left;
  }
  .success-box {
    background-color: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.2);
    color: #25D366;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 20px;
    text-align: left;
  }
  .toggle-link {
    cursor: pointer;
    color: var(--lime);
    text-decoration: underline;
    margin-top: 20px;
    display: inline-block;
    font-size: 13.5px;
    transition: all 0.2s;
  }
  .toggle-link:hover {
    color: #fff;
  }
  .helper-text {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 6px;
    line-height: 1.4;
  }
</style>
</head>
<body>

<div class="login-card">
  <div class="logo-area">
    <div class="logo">
      <img src="zamzy_logo.png" alt="ZAMZY" style="height:44px;width:auto;">
      <span>ZAM<span style="color:var(--cyan);">ZY</span></span>
      <span class="logo-highlight" style="color:#FF3D3D; font-size:14px; margin-left:4px;">YouTube</span>
    </div>
  </div>
  
  <!-- Banner Messages -->
  <div id="msgBox" style="display: none;"></div>

  <?php if (!empty($error)): ?>
    <div class="error-box" id="phpErrorBox"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- LOGIN FORM -->
  <div id="loginFormContainer">
    <h2>Creator Dashboard</h2>
    <p class="subtitle">Log in to manage your automated video & live stream notifications.</p>
    
    <form action="yt_login.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required autocomplete="username">
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
      </div>
      
      <button type="submit" class="btn-submit">Log In</button>
    </form>
    
    <span class="toggle-link" onclick="toggleForm('signup')">Don't have an account? Sign Up</span>
  </div>

  <!-- SIGNUP FORM -->
  <div id="signupFormContainer" style="display: none;">
    <h2>Create Creator Account</h2>
    <p class="subtitle">Join and verify your account via WhatsApp OTP code.</p>
    
    <!-- Step 1: Input details and Send OTP -->
    <form id="signupDetailsForm">
      <div class="form-group">
        <label for="signupUsername">Username</label>
        <input type="text" id="signupUsername" placeholder="Choose a username" required>
      </div>

      <div class="form-group">
        <label for="signupPhone">WhatsApp Number</label>
        <input type="text" id="signupPhone" placeholder="e.g., 918939220422" required>
        <div class="helper-text">Include your country code without "+" or spaces (e.g. 91 for India).</div>
      </div>
      
      <div class="form-group">
        <label for="signupPassword">Password</label>
        <input type="password" id="signupPassword" placeholder="Choose a password" required>
      </div>
      
      <button type="submit" id="btnSendOtp" class="btn-submit">Send WhatsApp OTP</button>
    </form>

    <!-- Step 2: Verification (Initially Hidden) -->
    <form id="signupOtpForm" style="display: none; margin-top: 20px;">
      <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 20px;">
        <label for="signupOtp">Enter Verification OTP</label>
        <input type="text" id="signupOtp" placeholder="Enter 6-digit code" required style="text-align: center; letter-spacing: 6px; font-size: 18px; font-weight: bold;">
        <div class="helper-text">Check your WhatsApp app for the 6-digit security code.</div>
      </div>
      
      <button type="submit" id="btnVerifyOtp" class="btn-submit" style="background-color: #128c7e;">Verify & Complete Signup</button>
    </form>
    
    <span class="toggle-link" onclick="toggleForm('login')">Already have an account? Log In</span>
  </div>
</div>

<script>
const msgBox = document.getElementById('msgBox');
const phpErrorBox = document.getElementById('phpErrorBox');
const loginContainer = document.getElementById('loginFormContainer');
const signupContainer = document.getElementById('signupFormContainer');

const signupDetailsForm = document.getElementById('signupDetailsForm');
const signupOtpForm = document.getElementById('signupOtpForm');
const btnSendOtp = document.getElementById('btnSendOtp');
const btnVerifyOtp = document.getElementById('btnVerifyOtp');

function showMsg(message, type) {
    msgBox.style.display = 'block';
    msgBox.textContent = message;
    msgBox.className = type === 'error' ? 'error-box' : 'success-box';
}

function clearMsg() {
    msgBox.style.display = 'none';
    if (phpErrorBox) phpErrorBox.style.display = 'none';
}

function toggleForm(formType) {
    clearMsg();
    if (formType === 'signup') {
        loginContainer.style.display = 'none';
        signupContainer.style.display = 'block';
        signupDetailsForm.style.display = 'block';
        signupOtpForm.style.display = 'none';
    } else {
        loginContainer.style.display = 'block';
        signupContainer.style.display = 'none';
    }
}

// Send OTP form submit
signupDetailsForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearMsg();
    
    const username = document.getElementById('signupUsername').value.trim();
    const phone = document.getElementById('signupPhone').value.trim();
    const password = document.getElementById('signupPassword').value;
    
    btnSendOtp.disabled = true;
    btnSendOtp.textContent = 'Sending OTP... Please wait...';
    
    const formData = new FormData();
    formData.append('username', username);
    formData.append('phone', phone);
    formData.append('password', password);
    
    try {
        const res = await fetch('yt_login.php?action=send_otp', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showMsg('OTP sent successfully to your WhatsApp number!', 'success');
            signupDetailsForm.style.display = 'none';
            signupOtpForm.style.display = 'block';
        } else {
            showMsg(data.error, 'error');
            btnSendOtp.disabled = false;
            btnSendOtp.textContent = 'Send WhatsApp OTP';
        }
    } catch (e) {
        showMsg('Network error, please try again.', 'error');
        btnSendOtp.disabled = false;
        btnSendOtp.textContent = 'Send WhatsApp OTP';
    }
});

// Verify OTP form submit
signupOtpForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearMsg();
    
    const otp = document.getElementById('signupOtp').value.trim();
    
    btnVerifyOtp.disabled = true;
    btnVerifyOtp.textContent = 'Verifying code...';
    
    const formData = new FormData();
    formData.append('otp', otp);
    
    try {
        const res = await fetch('yt_login.php?action=verify_otp', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showMsg('Signup completed! Redirecting to dashboard...', 'success');
            setTimeout(() => {
                window.location.href = 'yt_dashboard.php';
            }, 1500);
        } else {
            showMsg(data.error, 'error');
            btnVerifyOtp.disabled = false;
            btnVerifyOtp.textContent = 'Verify & Complete Signup';
        }
    } catch (e) {
        showMsg('Network error, please try again.', 'error');
        btnVerifyOtp.disabled = false;
        btnVerifyOtp.textContent = 'Verify & Complete Signup';
    }
});
</script>

</body>
</html>
