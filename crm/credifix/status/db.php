<?php
// db.php - SQLite Database Connection & Global Workflow Stages
$dbFile = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable WAL mode
    $pdo->exec("PRAGMA journal_mode = WAL;");

    // Create clients table
    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT,
        email TEXT,
        bank_name TEXT,
        amount REAL DEFAULT 0,
        lead_source TEXT DEFAULT 'Marketing Campaign',
        additional_details TEXT,
        status TEXT NOT NULL DEFAULT 'Documents Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        $pdo->exec("ALTER TABLE clients ADD COLUMN lead_source TEXT DEFAULT 'Marketing Campaign'");
    } catch (Exception $e) {}

    // Create status_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS status_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id INTEGER NOT NULL,
        previous_status TEXT,
        new_status TEXT NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
    )");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Complete Global Workflow Stages
$STAGES = [
    'Documents Pending',
    'Documents Collected',
    'Bank Transferred (Waiting for Login)',
    'Login with Documents',
    'Waiting for Sanction',
    'Sanction',
    'Completed',
    'On Hold',
    'Next Month Lead'
];
