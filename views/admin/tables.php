<?php
/**
 * Admin Tables — Disabled / Redirected
 * Fitur meja telah dinonaktifkan sesuai kebutuhan operasional grab-and-go.
 */

require_once __DIR__ . '/../../config/session.php';
initSession();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (preg_match('#^(.*?)(/(views|api|assets|admin|controllers|services|config|index\.php|monitor\.php))#', $scriptName, $m)) {
    $baseUrl = rtrim($m[1], '/');
} else {
    $baseUrl = '';
}

header('Location: ' . $baseUrl . '/views/admin/dashboard.php');
exit;
