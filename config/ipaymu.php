<?php
/**
 * iPaymu Payment Gateway Configuration
 * Little Salt Bread Blok M — POS System
 * 
 * API credentials and helper for iPaymu QRIS/VA payments.
 * Documentation: https://documenter.getpostman.com/view/7508947/SzzfXtrE
 */

// ─── iPaymu Credentials ─────────────────────────────────────────
// Set to sandbox for testing, production for live
define('IPAYMU_ENV', 'sandbox');

// Sandbox credentials (replace with your own from https://sandbox.ipaymu.com)
define('IPAYMU_VA_SANDBOX', '0000005150560383');   // Virtual Account sandbox
define('IPAYMU_KEY_SANDBOX', 'SANDBOXE764B16C-8CA0-41A5-AFD2-C1A9ED6F8D7F'); // API Key sandbox

// Production credentials (replace when going live)
define('IPAYMU_VA_PRODUCTION', '');
define('IPAYMU_KEY_PRODUCTION', '');

// ─── Derived Config ──────────────────────────────────────────────
function getIPaymuConfig(): array {
    $isSandbox = IPAYMU_ENV === 'sandbox';
    
    return [
        'va'       => $isSandbox ? IPAYMU_VA_SANDBOX : IPAYMU_VA_PRODUCTION,
        'apiKey'   => $isSandbox ? IPAYMU_KEY_SANDBOX : IPAYMU_KEY_PRODUCTION,
        'baseUrl'  => $isSandbox 
            ? 'https://sandbox.ipaymu.com/api/v2' 
            : 'https://my.ipaymu.com/api/v2',
        'env'      => IPAYMU_ENV,
    ];
}

/**
 * Generate iPaymu API signature
 * 
 * @param array  $body    Request body (will be JSON-encoded)
 * @param string $method  HTTP method (POST)
 * @return array Headers array with signature
 */
function generateIPaymuSignature(array $body, string $method = 'POST'): array {
    $config = getIPaymuConfig();
    
    $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
    $requestBody = strtolower(hash('sha256', $jsonBody));
    $stringToSign = strtoupper($method) . ':' . $config['va'] . ':' . $requestBody . ':' . $config['apiKey'];
    $signature = hash_hmac('sha256', $stringToSign, $config['apiKey']);
    
    return [
        'Content-Type: application/json',
        'va: ' . $config['va'],
        'signature: ' . $signature,
        'timestamp: ' . date('YmdHis'),
    ];
}

// ─── Callback URL helpers ────────────────────────────────────────
function getIPaymuCallbackUrl(): string {
    $base = getBaseUrl();
    return $base . '/api/payment_callback.php';
}

function getIPaymuReturnUrl(string $orderCode): string {
    $base = getBaseUrl();
    return $base . '/views/customer/invoice.php?order_code=' . urlencode($orderCode);
}

