<?php
function scanDirRecursive($dir) {
    $results = [];
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            $results = array_merge($results, scanDirRecursive($path));
        } else if (substr($path, -4) === '.php') {
            $results[] = $path;
        }
    }
    return $results;
}

$files = scanDirRecursive(__DIR__ . '/views');
$files[] = __DIR__ . '/index.php';
$files[] = __DIR__ . '/monitor.php';

foreach ($files as $f) {
    $lines = file($f);
    $rel = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $f);
    
    // Check for duplicate consecutive lines or non-prefixed href
    for ($i = 0; $i < count($lines); $i++) {
        $trim = trim($lines[$i]);
        if (empty($trim)) continue;
        
        // Non-prefixed href to /views or /monitor or /index
        if (preg_match('/href="(\/(views|monitor|index|assets|api)[^"]*)"/', $lines[$i], $m)) {
            echo "Non-baseUrl link in $rel on line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
        }
        
        // Exact duplicate consecutive lines
        if ($i > 0 && trim($lines[$i]) === trim($lines[$i - 1]) && strlen($trim) > 10) {
            echo "Duplicate line in $rel on line " . ($i + 1) . ": " . $trim . "\n";
        }
    }
}

