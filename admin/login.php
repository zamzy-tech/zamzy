<?php
if (!ob_get_level()) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$error = '';
$success = '';

// If already authenticated, redirect straight to dashboard
if (isset($_SESSION['zamzy_admin_logged']) && $_SESSION['zamzy_admin_logged'] === true) {
    if (!headers_sent()) {
        header('Location: index.php');
    }
    echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=index.php"><script>window.location.href="index.php";</script></head><body>Redirecting to <a href="index.php">Dashboard</a>...</body></html>';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $authenticated = false;
        $adminData = null;

        if ($pdo) {
            try {
                // Ensure table exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_admin_users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(50) NOT NULL UNIQUE,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(100) NOT NULL,
                    `role` VARCHAR(20) DEFAULT 'admin',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

                // Clean up any legacy admin accounts
                $pdo->exec("DELETE FROM `zamzy_admin_users` WHERE `username` != 'Zamzy0205'");

                // Seed default admin if table is empty
                $chk = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_admin_users` WHERE `username` = 'Zamzy0205'");
                $chk->execute();
                if ($chk->fetchColumn() == 0) {
                    $defaultPass = password_hash('@Zamzy0205', PASSWORD_DEFAULT);
                    $ins = $pdo->prepare("INSERT INTO `zamzy_admin_users` (`username`, `password_hash`, `name`, `email`, `role`) VALUES ('Zamzy0205', :pass, 'ZAMZY Super Admin', 'admin@zamzy.in', 'superadmin')");
                    $ins->execute([':pass' => $defaultPass]);
                }

                $stmt = $pdo->prepare("SELECT * FROM `zamzy_admin_users` WHERE `username` = :username LIMIT 1");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $authenticated = true;
                    $adminData = $user;
                } elseif (strcasecmp($username, 'Zamzy0205') === 0 && $password === '@Zamzy0205') {
                    // Update/ensure password hash matches
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("INSERT INTO `zamzy_admin_users` (`username`, `password_hash`, `name`, `email`, `role`) 
                        VALUES ('Zamzy0205', :p, 'ZAMZY Super Admin', 'admin@zamzy.in', 'superadmin')
                        ON DUPLICATE KEY UPDATE `password_hash` = :p2, `role` = 'superadmin'");
                    $upd->execute([':p' => $newHash, ':p2' => $newHash]);
                    $authenticated = true;
                    $adminData = $user ?: [
                        'id' => 1,
                        'name' => 'ZAMZY Super Admin',
                        'username' => 'Zamzy0205',
                        'role' => 'superadmin',
                        'email' => 'admin@zamzy.in'
                    ];
                }
            } catch (Exception $e) {
                // Fallback check if table query error
                if (strcasecmp($username, 'Zamzy0205') === 0 && $password === '@Zamzy0205') {
                    $authenticated = true;
                    $adminData = ['id' => 1, 'name' => 'ZAMZY Super Admin', 'username' => 'Zamzy0205', 'role' => 'superadmin', 'email' => 'admin@zamzy.in'];
                } else {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } else {
            // Emergency fallback if DB connection fails
            if (strcasecmp($username, 'Zamzy0205') === 0 && $password === '@Zamzy0205') {
                $authenticated = true;
                $adminData = ['id' => 1, 'name' => 'ZAMZY Super Admin', 'username' => 'Zamzy0205', 'role' => 'superadmin', 'email' => 'admin@zamzy.in'];
            } else {
                $error = 'Database connection error. Ensure MySQL is running.';
            }
        }

        if ($authenticated && $adminData) {
            $_SESSION['zamzy_admin_logged'] = true;
            $_SESSION['zamzy_admin_id'] = $adminData['id'] ?? 1;
            $_SESSION['zamzy_admin_name'] = $adminData['name'] ?? 'ZAMZY Super Admin';
            $_SESSION['zamzy_admin_username'] = $adminData['username'] ?? 'Zamzy0205';
            $_SESSION['zamzy_admin_role'] = $adminData['role'] ?? 'superadmin';

            // Log Admin Login Event with IP Location
            logActivity('admin_login', 'Admin Login Successful', ['username' => $username, 'role' => $_SESSION['zamzy_admin_role']], $username, null, $adminData['email'] ?? null, 'success');

            if (!headers_sent()) {
                header('Location: index.php');
            }
            echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=index.php"><script>window.location.href="index.php";</script></head><body>Redirecting to <a href="index.php">Dashboard</a>...</body></html>';
            exit;
        } elseif (empty($error)) {
            $error = 'Invalid username or password credentials.';
            logActivity('admin_login_failed', 'Admin Login Failed', ['attempted_username' => $username, 'reason' => 'Invalid credentials'], $username, null, null, 'failed');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY — Executive Admin Authentication</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --void: #050505;
            --violet: #8b5cf6;
            --cyan: #06b6d4;
            --white: #ffffff;
            --dim: rgba(255, 255, 255, 0.8);
            --faint: rgba(255, 255, 255, 0.45);
            --mono: 'JetBrains Mono', monospace;
            --display: 'Space Grotesk', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--void);
            color: var(--white);
            font-family: var(--display);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .aura {
            position: fixed;
            inset: -25%;
            background: radial-gradient(circle at 50% 44%, rgba(58, 28, 92, 0.6), rgba(24, 12, 46, 0.22) 34%, transparent 64%);
            z-index: 0;
            pointer-events: none;
        }

        .login-card {
            background: #0d0d16;
            border: 1px solid rgba(6, 182, 212, 0.4);
            width: 100%;
            max-width: 440px;
            padding: 3.5rem 2.8rem;
            border-radius: 16px;
            box-shadow: 0 0 60px rgba(6, 182, 212, 0.25), inset 0 0 30px rgba(139, 92, 246, 0.15);
            position: relative;
            z-index: 10;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2.2rem;
        }

        .brand-title {
            font-family: var(--mono);
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--white);
            line-height: 1;
        }

        .brand-title span.dot {
            color: var(--cyan);
            text-shadow: 0 0 12px var(--cyan);
        }

        .brand-subtitle {
            font-family: var(--mono);
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--cyan);
            margin-top: 0.5rem;
            opacity: 0.85;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            font-family: var(--mono);
            font-size: 0.75rem;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            border-radius: 6px;
        }

        .form-group {
            margin-bottom: 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-family: var(--mono);
            font-size: 0.68rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--cyan);
            font-weight: 700;
        }

        .form-input {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--white);
            font-family: var(--mono);
            font-size: 0.85rem;
            padding: 0.9rem 1.1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.35);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--violet), var(--cyan));
            border: 1px solid rgba(6, 182, 212, 0.5);
            color: var(--white);
            font-family: var(--mono);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 1.1rem;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.35);
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            box-shadow: 0 0 35px rgba(6, 182, 212, 0.6);
            transform: translateY(-2px);
        }

        .login-footer {
            margin-top: 2rem;
            text-align: center;
            font-family: var(--mono);
            font-size: 0.62rem;
            color: var(--faint);
            letter-spacing: 0.1em;
            line-height: 1.6;
        }
    </style>
</head>

<body>

    <div class="aura"></div>

    <div class="login-card">
        <div class="brand-header">
            <h1 class="brand-title">ZAMZY<span class="dot">.</span></h1>
            <p class="brand-subtitle">Executive Console</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Admin Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Enter username" required
                    autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Security Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">Enter Control Panel →</button>
        </form>

        <div class="login-footer">
            ZAMZY.IN · Anna Nagar, Chennai<br>
            Protected by End-to-End Enterprise Auth
        </div>
    </div>

</body>

</html>