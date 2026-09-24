<?php
$ch = curl_init('http://localhost:8080/api/payment_callback.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'reference' => 'SB2409266DA7',
    'status' => 'paid',
    'trx_id' => 'TEST-123',
    'payment_method' => 'va_bca'
]));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: $httpCode\n";
echo "RESPONSE:\n$response\n";

