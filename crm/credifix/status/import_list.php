<?php
// import_list.php - Clear old client data and import updated client list with statuses
require_once __DIR__ . '/db.php';

$newClients = [
    ["name" => "Prabhakar Reddy sir", "details" => "", "status" => "Waiting for Sanction"],
    ["name" => "Bala Krishna anna", "details" => "", "status" => "Waiting for Sanction"],
    ["name" => "Asad Khan anna", "details" => "", "status" => "Waiting for Sanction"],
    ["name" => "Narender Reddy anna", "details" => "", "status" => "Login with Documents"],
    ["name" => "Guruzeevan Reddy sir", "details" => "", "status" => "Waiting for Sanction"],
    ["name" => "Srinivas sir", "details" => "Warangal (Upender sir)", "status" => "Login with Documents"],
    ["name" => "Mohan sir", "details" => "(Nageshwar Rao)", "status" => "Documents Collected"],
    ["name" => "Venu Gopal Rao", "details" => "Mang (NRI)", "status" => "Documents Pending"],
    ["name" => "Muka Hanumanth Rao", "details" => "(NRI) Jubilee Hills; (Nageshwar Rao) HDFC", "status" => "Bank Transferred (Waiting for Login)"],
    ["name" => "Pullaiah Singh", "details" => "(Nageshwar Rao) 15 Cr", "status" => "On Hold"],
    ["name" => "Nagesh sir", "details" => "(Kareem anna) [name unclear]", "status" => "On Hold"],
    ["name" => "Rajesh Kumar Nayak", "details" => "(Narsing Rao sir)", "status" => "Bank Transferred (Waiting for Login)"],
    ["name" => "G S Infra", "details" => "(Narsing Rao sir)", "status" => "Documents Pending"],
    ["name" => "Mahendranath sir", "details" => "(Nageshwar Rao)", "status" => "Documents Pending"],
    ["name" => "Koteswara Rao", "details" => "(Nageshwar Rao)", "status" => "Bank Transferred (Waiting for Login)"],
    ["name" => "Likitha", "details" => "(Likitha) TD", "status" => "Documents Pending"],
    ["name" => "Agama sir", "details" => "", "status" => "Login with Documents"],
    ["name" => "Srinivas Rao", "details" => "(Mahidipatnam)", "status" => "Bank Transferred (Waiting for Login)"],
    ["name" => "Raja Shekar Reddy anna", "details" => "LMR", "status" => "On Hold"],
    ["name" => "Rajanna", "details" => "(Kanakadurga)", "status" => "Completed"],
    ["name" => "Agastya Reddy anna", "details" => "", "status" => "Completed"],
    ["name" => "Rama Kanth sir", "details" => "[name unclear in original]", "status" => "Completed"],
    ["name" => "Bikshapathi baava", "details" => "[name unclear in original]", "status" => "Bank Transferred (Waiting for Login)"],
    ["name" => "Vidya Sagar sir", "details" => "", "status" => "On Hold"],
    ["name" => "Mahesh anna", "details" => "(DSNR)", "status" => "On Hold"],
    ["name" => "Tarakeshwar Rao", "details" => "", "status" => "Documents Pending"],
    ["name" => "Harshavardhan", "details" => "", "status" => "Waiting for Sanction"],
    ["name" => "Venkatesh anna", "details" => "", "status" => "Documents Pending"],
    ["name" => "Sridhar", "details" => "(Cult) [unclear in original]", "status" => "Documents Pending"],
    ["name" => "Suresh", "details" => "(Kandukur)", "status" => "Documents Pending"],
    ["name" => "Sanjeev Reddy anna", "details" => "", "status" => "Documents Pending"],
    ["name" => "Vamshi Reddy anna", "details" => "", "status" => "Bank Transferred (Waiting for Login)"],
    ["name" => "Venkat Ramana Reddy", "details" => "(PL)", "status" => "Next Month Lead"],
    ["name" => "Sunil", "details" => "(Mahesh anna)", "status" => "Documents Pending"],
    ["name" => "Krishna", "details" => "", "status" => "Login with Documents"],
    ["name" => "Teju", "details" => "Hold (Need to talk Tirupesh anna)", "status" => "On Hold"]
];

try {
    $pdo->beginTransaction();

    // Wipe old records completely
    $pdo->exec("DELETE FROM status_logs");
    $pdo->exec("DELETE FROM clients");
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ('clients', 'status_logs')");

    $stmt = $pdo->prepare("INSERT INTO clients (name, additional_details, status, lead_source) VALUES (:name, :details, :status, 'Marketing Campaign')");
    $logStmt = $pdo->prepare("INSERT INTO status_logs (client_id, previous_status, new_status, notes) VALUES (:client_id, NULL, :new_status, 'Imported with assigned status')");

    $count = 0;
    foreach ($newClients as $item) {
        $status = in_array($item['status'], $STAGES) ? $item['status'] : 'Documents Pending';

        $stmt->execute([
            ':name' => $item['name'],
            ':details' => $item['details'],
            ':status' => $status
        ]);
        $clientId = $pdo->lastInsertId();

        $logStmt->execute([
            ':client_id' => $clientId,
            ':new_status' => $status
        ]);
        $count++;
    }

    $pdo->commit();
    echo "Successfully removed all old data and imported {$count} new client records with updated statuses.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error importing clients: " . $e->getMessage() . "\n";
}
