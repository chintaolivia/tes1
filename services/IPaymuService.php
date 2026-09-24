<?php
require_once __DIR__ . '/../config/ipaymu.php';

class IPaymuService {
    private static function request($method, $endpoint, $body = []) {
        $config = getIPaymuConfig();
        $url = $config['sandbox'] ? 'https://sandbox.ipaymu.com/api/v2' : 'https://my.ipaymu.com/api/v2';
        $url .= $endpoint;
        
        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        
        $signature = generateIPaymuSignature($method, $bodyJson);
        
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'va: ' . $config['va'],
            'signature: ' . $signature,
            'timestamp: ' . date('YmdHis')
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
        } else if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            error_log("IPaymu cURL Error: " . $err);
            throw new Exception("Koneksi ke payment gateway gagal: " . $err);
        }
        
        return json_decode($response, true);
    }
    
    public static function createQRISPayment($orderCode, $amount, $buyerName, $buyerEmail, $buyerPhone) {
        $body = [
            'product' => ['Order ' . $orderCode],
            'qty' => ['1'],
            'price' => [(string)$amount],
            'returnUrl' => getIPaymuReturnUrl(),
            'notifyUrl' => getIPaymuCallbackUrl(),
            'cancelUrl' => getIPaymuReturnUrl(),
            'referenceId' => $orderCode,
            'buyerName' => $buyerName,
            'buyerEmail' => $buyerEmail,
            'buyerPhone' => $buyerPhone,
            'paymentMethod' => 'qris'
        ];
        
        $res = self::request('POST', '/payment/direct', $body);
        
        if (isset($res['Status']) && $res['Status'] == 200) {
            return $res['Data'];
        }
        
        throw new Exception("Gagal membuat pembayaran QRIS: " . ($res['Message'] ?? 'Unknown error'));
    }
    
    public static function createVAPayment($orderCode, $amount, $bank, $buyerName, $buyerEmail, $buyerPhone) {
        $body = [
            'name' => $buyerName,
            'phone' => $buyerPhone,
            'email' => $buyerEmail,
            'amount' => (string)$amount,
            'notifyUrl' => getIPaymuCallbackUrl(),
            'referenceId' => $orderCode,
            'paymentMethod' => 'va',
            'paymentChannel' => strtolower($bank)
        ];
        
        $res = self::request('POST', '/payment/direct', $body);
        
        if (isset($res['Status']) && $res['Status'] == 200) {
            return $res['Data'];
        }
        
        throw new Exception("Gagal membuat pembayaran Virtual Account: " . ($res['Message'] ?? 'Unknown error'));
    }
    
    public static function checkPaymentStatus($transactionId) {
        $body = [
            'transactionId' => $transactionId
        ];
        
        $res = self::request('POST', '/transaction', $body);
        
        if (isset($res['Status']) && $res['Status'] == 200) {
            return $res['Data'];
        }
        
        throw new Exception("Gagal mengecek status pembayaran: " . ($res['Message'] ?? 'Unknown error'));
    }
    
    public static function verifyCallback($data) {
        if (!isset($data['trx_id']) || !isset($data['status']) || !isset($data['signature'])) {
            return false;
        }
        try {
            $statusData = self::checkPaymentStatus($data['trx_id']);
            if ($statusData && ($statusData['Status'] == 1 || $statusData['Status'] == 6)) { // 1 or 6 = success/paid
                return true;
            }
        } catch (Exception $e) {
            error_log("IPaymu verifyCallback Error: " . $e->getMessage());
        }
        return false;
    }
}
