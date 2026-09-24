<?php
require_once __DIR__ . '/../config/resend.php';

class ResendService {
    public static function sendEmail($to, $subject, $htmlContent) {
        $data = [
            'from' => RESEND_FROM_NAME . ' <' . RESEND_FROM_EMAIL . '>',
            'to' => is_array($to) ? $to : [$to],
            'subject' => $subject,
            'html' => $htmlContent
        ];
        
        $ch = curl_init(RESEND_BASE_URL . '/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            error_log("Resend cURL Error: " . $err);
            throw new Exception("Gagal mengirim email: " . $err);
        }
        
        $res = json_decode($response, true);
        if (isset($res['statusCode']) && $res['statusCode'] >= 400) {
            throw new Exception("Gagal mengirim email: " . ($res['message'] ?? 'Unknown error'));
        }
        
        return $res;
    }
    
    public static function sendVerificationEmail($email, $name, $token) {
        $baseUrl = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $verifyUrl = $baseUrl . '/api/auth/verify_email.php?token=' . urlencode($token);
        $content = verificationEmailContent($name, $verifyUrl);
        return self::sendEmail($email, 'Verifikasi Email Anda - Little Salt Bread', $content);
    }
    
    public static function sendPasswordResetEmail($email, $name, $token) {
        $baseUrl = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $resetUrl = $baseUrl . '/views/auth/reset_password.php?token=' . urlencode($token);
        $content = resetPasswordEmailContent($name, $resetUrl);
        return self::sendEmail($email, 'Reset Password - Little Salt Bread', $content);
    }
    
    public static function sendOrderConfirmation($email, $name, $orderData) {
        $baseUrl = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $queueNumber = $orderData['queue_number'] ?? 'SB-000';
        $orderCode = $orderData['order_code'] ?? '';
        $total = isset($orderData['total_amount']) ? formatRupiah((int)$orderData['total_amount']) : 'Rp 0';
        $trackingUrl = $baseUrl . '/views/customer/invoice.php?order_code=' . urlencode($orderCode);
        $content = orderConfirmationEmailContent($name, $queueNumber, $orderCode, $total, $trackingUrl);
        return self::sendEmail($email, 'Konfirmasi Pesanan [' . $queueNumber . '] - Little Salt Bread', $content);
    }
}
