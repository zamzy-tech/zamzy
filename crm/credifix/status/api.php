<?php
// api.php - RESTful backend API for Status Tracking System
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper to parse JSON input
$rawInput = @file_get_contents('php://input');
$jsonData = !empty($rawInput) ? json_decode($rawInput, true) : [];
if (!is_array($jsonData)) {
    $jsonData = [];
}

$requestData = array_merge($_REQUEST, $jsonData);

switch ($action) {

    case 'list':
        try {
            $search = trim($requestData['search'] ?? '');
            $statusFilter = trim($requestData['status'] ?? '');

            $query = "SELECT * FROM clients WHERE 1=1";
            $params = [];

            if ($search !== '') {
                $query .= " AND (name LIKE :search OR phone LIKE :search OR email LIKE :search OR bank_name LIKE :search OR lead_source LIKE :search OR additional_details LIKE :search OR id = :exact_id)";
                $params[':search'] = "%{$search}%";
                $params[':exact_id'] = is_numeric($search) ? (int)$search : 0;
            }

            if ($statusFilter !== '' && $statusFilter !== 'All') {
                $query .= " AND status = :status";
                $params[':status'] = $statusFilter;
            }

            $query .= " ORDER BY id DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $clients = $stmt->fetchAll();

            // Calculate status counts
            $countsStmt = $pdo->query("SELECT status, COUNT(*) as count FROM clients GROUP BY status");
            $rawCounts = $countsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $statusCounts = ['Total' => 0];
            foreach ($STAGES as $stage) {
                $statusCounts[$stage] = (int)($rawCounts[$stage] ?? 0);
            }
            $totalStmt = $pdo->query("SELECT COUNT(*) FROM clients");
            $statusCounts['Total'] = (int)$totalStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'clients' => $clients,
                'stages' => $STAGES,
                'counts' => $statusCounts
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $name = trim($requestData['name'] ?? '');
            if (!$name) {
                echo json_encode(['success' => false, 'error' => 'Client Name is required.']);
                exit;
            }

            $phone = trim($requestData['phone'] ?? '');
            $email = trim($requestData['email'] ?? '');
            $bankName = trim($requestData['bank_name'] ?? '');
            $amount = (float)($requestData['amount'] ?? 0);
            $leadSource = trim($requestData['lead_source'] ?? 'Marketing Campaign');
            $additionalDetails = trim($requestData['additional_details'] ?? '');
            $status = trim($requestData['status'] ?? 'Documents Collect');

            if (!in_array($status, $STAGES)) {
                $status = 'Documents Collect';
            }

            $stmt = $pdo->prepare("INSERT INTO clients (name, phone, email, bank_name, amount, lead_source, additional_details, status) VALUES (:name, :phone, :email, :bank_name, :amount, :lead_source, :additional_details, :status)");
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':bank_name' => $bankName,
                ':amount' => $amount,
                ':lead_source' => $leadSource ?: 'Marketing Campaign',
                ':additional_details' => $additionalDetails,
                ':status' => $status
            ]);

            $clientId = $pdo->lastInsertId();

            // Log initial status
            $logStmt = $pdo->prepare("INSERT INTO status_logs (client_id, previous_status, new_status, notes) VALUES (:client_id, NULL, :new_status, :notes)");
            $logStmt->execute([
                ':client_id' => $clientId,
                ':new_status' => $status,
                ':notes' => 'Interested marketing lead onboarded'
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Interested marketing client added successfully!',
                'client_id' => $clientId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'update_status':
        try {
            $clientId = (int)($requestData['client_id'] ?? 0);
            $newStatus = trim($requestData['status'] ?? '');
            $notes = trim($requestData['notes'] ?? '');

            if (!$clientId || !in_array($newStatus, $STAGES)) {
                echo json_encode(['success' => false, 'error' => 'Invalid client ID or status stage.']);
                exit;
            }

            // Fetch current client
            $stmt = $pdo->prepare("SELECT status FROM clients WHERE id = :id");
            $stmt->execute([':id' => $clientId]);
            $client = $stmt->fetch();

            if (!$client) {
                echo json_encode(['success' => false, 'error' => 'Client not found.']);
                exit;
            }

            $previousStatus = $client['status'];

            if ($previousStatus === $newStatus) {
                echo json_encode(['success' => true, 'message' => 'Status is already set to ' . $newStatus]);
                exit;
            }

            // Update client status
            $updateStmt = $pdo->prepare("UPDATE clients SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $updateStmt->execute([
                ':status' => $newStatus,
                ':id' => $clientId
            ]);

            // Add status log
            $logStmt = $pdo->prepare("INSERT INTO status_logs (client_id, previous_status, new_status, notes) VALUES (:client_id, :prev_status, :new_status, :notes)");
            $logStmt->execute([
                ':client_id' => $clientId,
                ':prev_status' => $previousStatus,
                ':new_status' => $newStatus,
                ':notes' => $notes ?: "Advanced status to {$newStatus}"
            ]);

            echo json_encode([
                'success' => true,
                'message' => "Advanced status to '{$newStatus}' successfully!"
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'update_client':
        try {
            $id = (int)($requestData['id'] ?? 0);
            $name = trim($requestData['name'] ?? '');

            if (!$id || !$name) {
                echo json_encode(['success' => false, 'error' => 'Invalid client ID or empty name.']);
                exit;
            }

            $phone = trim($requestData['phone'] ?? '');
            $email = trim($requestData['email'] ?? '');
            $bankName = trim($requestData['bank_name'] ?? '');
            $amount = (float)($requestData['amount'] ?? 0);
            $leadSource = trim($requestData['lead_source'] ?? 'Marketing Campaign');
            $additionalDetails = trim($requestData['additional_details'] ?? '');

            $stmt = $pdo->prepare("UPDATE clients SET name = :name, phone = :phone, email = :email, bank_name = :bank_name, amount = :amount, lead_source = :lead_source, additional_details = :additional_details, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':bank_name' => $bankName,
                ':amount' => $amount,
                ':lead_source' => $leadSource ?: 'Marketing Campaign',
                ':additional_details' => $additionalDetails,
                ':id' => $id
            ]);

            echo json_encode(['success' => true, 'message' => 'Client updated successfully!']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $clientId = (int)($requestData['id'] ?? 0);
            if (!$clientId) {
                echo json_encode(['success' => false, 'error' => 'Invalid client ID.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
            $stmt->execute([':id' => $clientId]);

            echo json_encode(['success' => true, 'message' => 'Client deleted successfully!']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'get_history':
        try {
            $clientId = (int)($requestData['client_id'] ?? 0);
            if (!$clientId) {
                echo json_encode(['success' => false, 'error' => 'Invalid client ID.']);
                exit;
            }

            $clientStmt = $pdo->prepare("SELECT name, status FROM clients WHERE id = :id");
            $clientStmt->execute([':id' => $clientId]);
            $client = $clientStmt->fetch();

            $stmt = $pdo->prepare("SELECT * FROM status_logs WHERE client_id = :id ORDER BY id DESC");
            $stmt->execute([':id' => $clientId]);
            $history = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'client' => $client,
                'history' => $history
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'bulk_import':
        try {
            $rows = $requestData['rows'] ?? [];
            if (!is_array($rows) || empty($rows)) {
                echo json_encode(['success' => false, 'error' => 'No valid data rows provided for import.']);
                exit;
            }

            $insertedCount = 0;
            $pdo->beginTransaction();

            $insertStmt = $pdo->prepare("INSERT INTO clients (name, phone, email, bank_name, amount, lead_source, additional_details, status) VALUES (:name, :phone, :email, :bank_name, :amount, :lead_source, :additional_details, :status)");
            $logStmt = $pdo->prepare("INSERT INTO status_logs (client_id, previous_status, new_status, notes) VALUES (:client_id, NULL, :new_status, :notes)");

            foreach ($rows as $row) {
                $name = trim($row['name'] ?? $row['Name'] ?? '');
                if (!$name) continue;

                $phone = trim($row['phone'] ?? $row['Phone'] ?? $row['Mobile'] ?? '');
                $email = trim($row['email'] ?? $row['Email'] ?? '');
                $bankName = trim($row['bank_name'] ?? $row['Bank'] ?? $row['Bank Name'] ?? '');
                $amount = (float)($row['amount'] ?? $row['Amount'] ?? $row['Loan Amount'] ?? 0);
                $leadSource = trim($row['lead_source'] ?? $row['Source'] ?? 'Marketing Campaign');
                $additionalDetails = trim($row['additional_details'] ?? $row['Details'] ?? $row['Notes'] ?? $row['Remarks'] ?? '');
                $status = trim($row['status'] ?? $row['Status'] ?? 'Documents Collect');

                if (!in_array($status, $STAGES)) {
                    $status = 'Documents Collect';
                }

                $insertStmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':email' => $email,
                    ':bank_name' => $bankName,
                    ':amount' => $amount,
                    ':lead_source' => $leadSource ?: 'Marketing Campaign',
                    ':additional_details' => $additionalDetails,
                    ':status' => $status
                ]);

                $cId = $pdo->lastInsertId();
                $logStmt->execute([
                    ':client_id' => $cId,
                    ':new_status' => $status,
                    ':notes' => 'Imported marketing lead'
                ]);

                $insertedCount++;
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Successfully imported {$insertedCount} marketing leads!",
                'count' => $insertedCount
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid or missing API action']);
        break;
}
