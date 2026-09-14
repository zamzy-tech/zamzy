<?php
// seed.php - Populate sample data
require_once __DIR__ . '/db.php';

$count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
if ($count == 0) {
    $samples = [
        [
            'name' => 'Rahul Sharma',
            'phone' => '+91 98765 43210',
            'email' => 'rahul.sharma@example.com',
            'bank_name' => 'HDFC Bank',
            'amount' => 500000,
            'additional_details' => 'Personal Loan request. All documents submitted.',
            'status' => 'Documents Collect'
        ],
        [
            'name' => 'Priya Patel',
            'phone' => '+91 98123 45678',
            'email' => 'priya.p@example.com',
            'bank_name' => 'ICICI Bank',
            'amount' => 1200000,
            'additional_details' => 'Home Loan file under Credfix verification.',
            'status' => 'Verification by Credfix'
        ],
        [
            'name' => 'Amit Kumar',
            'phone' => '+91 97654 32109',
            'email' => 'amit.k@example.com',
            'bank_name' => 'State Bank of India',
            'amount' => 750000,
            'additional_details' => 'Bank transfer stage completed.',
            'status' => 'Login with Documents'
        ],
        [
            'name' => 'Sneha Verma',
            'phone' => '+91 99887 76655',
            'email' => 'sneha.v@example.com',
            'bank_name' => 'Axis Bank',
            'amount' => 1500000,
            'additional_details' => 'Final sanction letter generated.',
            'status' => 'Sanction'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO clients (name, phone, email, bank_name, amount, additional_details, status) VALUES (:name, :phone, :email, :bank_name, :amount, :additional_details, :status)");
    $logStmt = $pdo->prepare("INSERT INTO status_logs (client_id, previous_status, new_status, notes) VALUES (:client_id, NULL, :new_status, :notes)");

    foreach ($samples as $sample) {
        $stmt->execute($sample);
        $cId = $pdo->lastInsertId();
        $logStmt->execute([
            ':client_id' => $cId,
            ':new_status' => $sample['status'],
            ':notes' => 'Sample record created'
        ]);
    }
    echo "Seed data created successfully!\n";
} else {
    echo "Database already contains {$count} records.\n";
}
