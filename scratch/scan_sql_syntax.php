<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDb();

function checkPhpFiles($dir) {
    global $pdo;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() !== 'php') continue;
        if (strpos($file->getPathname(), 'vendor') !== false) continue;
        
        $code = file_get_contents($file->getPathname());
        
        // Find prepare calls
        preg_match_all('/(?:->prepare|\$pdo->query)\s*\(\s*(["\'])([\s\S]*?)\1\s*\)/', $code, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $sql = $m[2];
            // Normalize sql
            $testSql = preg_replace('/\?/', "'test'", $sql);
            $testSql = preg_replace('/:[a-zA-Z0-9_]+/', "'test'", $testSql);
            $testSql = trim($testSql);
            if (empty($testSql)) continue;
            
            // Check for obviously malformed SQL (multiple WHEREs not in subquery, etc)
            if (substr_count(strtoupper($sql), 'WHERE ') > 1 && strpos(strtoupper($sql), 'SELECT') === strrpos(strtoupper($sql), 'SELECT')) {
                echo "[WARNING Multiple WHERE] in " . $file->getPathname() . "\nSQL: $sql\n\n";
            }
            if (substr_count(strtoupper($sql), 'SELECT ') > 1 && strpos(strtoupper($sql), 'UNION') === false && strpos(strtoupper($sql), '(') === false) {
                echo "[WARNING Multiple SELECT] in " . $file->getPathname() . "\nSQL: $sql\n\n";
            }
            
            try {
                $pdo->prepare($sql);
            } catch (Throwable $e) {
                echo "[PDO ERROR] in " . $file->getPathname() . ": " . $e->getMessage() . "\nSQL: $sql\n\n";
            }
        }
    }
}

checkPhpFiles(__DIR__ . '/../');
echo "SQL scan completed.\n";

