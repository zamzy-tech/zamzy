<?php
header('Content-Type: text/plain');
$filePath = '/home/shacartc/school.tehub.in/app/Services/UserService.php';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    $reflection = new ReflectionClass('App\Services\UserService');
    $method = $reflection->getMethod('updateStudentUser');
    
    echo "=== Remote UserService::updateStudentUser Method ===\n\n";
    echo "Parameters:\n";
    foreach ($method->getParameters() as $param) {
        echo " - " . $param->getName() . " (Type: " . ($param->getType() ? $param->getType()->getName() : 'none') . ", Default: " . ($param->isDefaultValueAvailable() ? var_export($param->getDefaultValue(), true) : 'required') . ")\n";
    }
    
    // Print the method body
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();
    $lines = explode("\n", $content);
    echo "\nMethod Body:\n";
    for ($i = $startLine - 1; $i < $endLine; $i++) {
        echo ($i + 1) . ": " . $lines[$i] . "\n";
    }
} else {
    echo "Remote UserService.php not found.\n";
}
@unlink(__FILE__);
?>
