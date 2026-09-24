<?php
$_SERVER['REQUEST_URI'] = '/views/admin/dashboard.php';
$_SERVER['SCRIPT_NAME'] = '/views/admin/dashboard.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../config/session.php';
loginUser([
    'id' => 1,
    'name' => 'Admin Test',
    'email' => 'admin@saltbread.id',
    'role' => 'admin',
    'tier' => 'vip'
]);

ob_start();
try {
    include __DIR__ . '/../views/admin/dashboard.php';
    $output = ob_get_clean();
    echo "SUCCESS: Dashboard rendered without fatal errors! Output length: " . strlen($output) . " bytes\n";
    if (strpos($output, 'Fatal error') !== false || strpos($output, 'PDOException') !== false) {
        echo "FOUND ERROR IN OUTPUT:\n" . substr($output, 0, 500) . "\n";
    } else {
        echo "No Fatal Errors or PDOExceptions detected!\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "FATAL EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
