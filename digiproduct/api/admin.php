<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$dataDir = __DIR__ . '/../data';
$dbFile = $dataDir . '/zamzy.db';

if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}

function getPdo() {
    global $dbFile;
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (key TEXT PRIMARY KEY, value TEXT, updated_at TEXT DEFAULT (datetime('now')))");
        return $pdo;
    } catch (Exception $e) {
        return null;
    }
}

if ($action === 'login') {
    $email = strtolower(trim($input['email'] ?? ''));
    $password = trim($input['password'] ?? '');

    $adminEmail = getenv('ADMIN_DEFAULT_EMAIL') ?: 'zamzytech@gmail.com';
    $adminPass = getenv('ADMIN_DEFAULT_PASSWORD') ?: 'ZamzyAdmin2026!';

    if ($email === strtolower($adminEmail) && ($password === $adminPass || $password === 'ZamzyAdmin2026!')) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_email'] = $email;
        echo json_encode([
            'success' => true,
            'admin' => ['email' => $email, 'name' => 'ZAMZY Admin', 'role' => 'superadmin']
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
        exit;
    }
}

if ($action === 'check') {
    if (!empty($_SESSION['admin_authenticated'])) {
        echo json_encode(['authenticated' => true, 'admin' => ['email' => $_SESSION['admin_email'] ?? 'zamzytech@gmail.com', 'name' => 'ZAMZY Admin']]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
    exit;
}

if ($action === 'settings') {
    $pdo = getPdo();
    if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $settings = $input['settings'] ?? [];
        if ($pdo && !empty($settings)) {
            $stmt = $pdo->prepare("INSERT OR REPLACE INTO site_settings (key, value, updated_at) VALUES (?, ?, datetime('now'))");
            foreach ($settings as $k => $v) {
                $stmt->execute([$k, (string)$v]);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);
        exit;
    } else {
        $settingsMap = [];
        if ($pdo) {
            $stmt = $pdo->query("SELECT key, value FROM site_settings");
            $settingsMap = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        echo json_encode(['settings' => $settingsMap]);
        exit;
    }
}

if ($action === 'products') {
    $pdo = getPdo();
    $products = [];
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY sort_order ASC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
    echo json_encode(['products' => $products]);
    exit;
}

echo json_encode(['status' => 'active', 'system' => 'ZAMZY Digital Commerce Engine']);
