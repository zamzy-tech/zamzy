<!DOCTYPE html>
<html>
<body>
<pre>
<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=shacartc_eschool_saas", "shacartc_school", "School@786");
    $stmt = $pdo->query("SELECT name, data FROM system_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($settings as $s) {
        echo htmlspecialchars($s['name']) . ": " . htmlspecialchars(substr($s['data'], 0, 150)) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
</pre>
</body>
</html>
