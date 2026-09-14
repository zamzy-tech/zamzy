<?php
define('BASEPATH', true);
require_once __DIR__ . '/application/config/app-config.php';
$conn = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($conn->connect_error) { die('DB Connect Failed: ' . $conn->connect_error); }

// Update existing invoices, proposals, estimates, expenses to currency ID 3 (INR)
$conn->query("UPDATE tblinvoices SET currency=3");
$conn->query("UPDATE tblproposals SET currency=3");
$conn->query("UPDATE tblestimates SET currency=3");
$conn->query("UPDATE tblexpenses SET currency=3");
$conn->query("UPDATE tblsubscriptions SET currency=3");

echo json_encode([
    'status' => 'success',
    'message' => 'All existing invoices, proposals, estimates, and expenses updated to INR (₹)'
]);
