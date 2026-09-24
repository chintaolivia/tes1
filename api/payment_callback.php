<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../services/ResendService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

try {
    $raw_body = file_get_contents('php://input');
    $input = json_decode($raw_body, true) ?? $_POST;
    
    // Log the callback data
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_data = date('Y-m-d H:i:s') . " - Payment Callback: " . print_r($input, true) . "\n";
    file_put_contents($log_dir . '/payment.log', $log_data, FILE_APPEND);
    
    $trx_id = $input['trx_id'] ?? '';
    $status = $input['status'] ?? '';
    $reference = $input['reference'] ?? ($input['order_code'] ?? '');
    
    if (empty($reference)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reference / Order Code tidak ditemukan']);
        exit;
    }
    
    $db = getDb();
    
    // Find order
    $stmtFind = $db->prepare("SELECT id, order_code, queue_number, customer_name, customer_email, customer_phone, total_amount, order_type, payment_status, order_status FROM orders WHERE order_code = ? OR queue_number = ? LIMIT 1");
    $stmtFind->execute([$reference, $reference]);
    $order = $stmtFind->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Pesanan dengan referensi ' . $reference . ' tidak ditemukan']);
        exit;
    }
    
    if (strtolower($status) === 'berhasil' || strtolower($status) === 'success' || strtolower($status) === 'paid') {
        $db = getDb();
        $payment_method_input = trim($input['payment_method'] ?? '');
        if (!empty($payment_method_input)) {
            $stmtUpdate = $db->prepare("
                UPDATE orders 
                SET payment_status = 'paid', 
                    order_status = 'confirmed', 
                    payment_method = ?,
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$payment_method_input, $order['id']]);
            $order['payment_method'] = $payment_method_input;
        } else {
            $stmtUpdate = $db->prepare("
                UPDATE orders 
                SET payment_status = 'paid', 
                    order_status = 'confirmed', 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$order['id']]);
        }
        $order['payment_status'] = 'paid';
        $order['order_status'] = 'confirmed';
        
        // Send order confirmation email via Resend
        $emailSent = false;
        $emailError = null;
        if (!empty($order['customer_email']) && filter_var($order['customer_email'], FILTER_VALIDATE_EMAIL)) {
            try {
                ResendService::sendOrderConfirmation(
                    $order['customer_email'],
                    $order['customer_name'] ?? 'Pelanggan',
                    $order
                );
                $emailSent = true;
            } catch (Throwable $eMail) {
                $emailError = $eMail->getMessage();
                error_log("Failed to send order confirmation email: " . $emailError);
            }
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Pembayaran berhasil diverifikasi. Nomor antrean resmi telah diaktifkan.',
            'data' => [
                'order_id' => (int) $order['id'],
                'order_code' => $order['order_code'],
                'queue_number' => $order['queue_number'],
                'customer_name' => $order['customer_name'],
                'customer_email' => $order['customer_email'],
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'email_sent' => $emailSent,
                'email_error' => $emailError
            ]
        ]);
        exit;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Status bukan success (' . $status . ')', 'data' => ['order_code' => $order['order_code']]]);
    exit;
} catch (Exception $e) {
    $log_dir = __DIR__ . '/../logs';
    if (is_dir($log_dir)) {
        $log_data = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
        file_put_contents($log_dir . '/payment.log', $log_data, FILE_APPEND);
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    exit;
}
