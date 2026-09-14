<!DOCTYPE html>
<html>
<body>
<pre>
<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=shacartc_eschool_saas", "shacartc_school", "School@786");
    
    echo "=== SCHOOLS ===\n";
    $stmt = $pdo->query("SELECT id, name, code, status, database_name FROM schools");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($schools);
    
    echo "\n=== USERS ===\n";
    $stmt2 = $pdo->query("SELECT id, first_name, last_name, email, school_id FROM users ORDER BY id DESC LIMIT 5");
    $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
</pre>
</body>
</html>
