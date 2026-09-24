<?php
/**
 * Resend Email Service Configuration
 * Little Salt Bread Blok M — POS System
 * 
 * API credentials and helpers for transactional emails via Resend.
 * Documentation: https://resend.com/docs/api-reference
 */

// ─── Resend Credentials ─────────────────────────────────────────
// Get your API key from https://resend.com/api-keys
//if (!defined('RESEND_API_KEY')) define('RESEND_API_KEY', 'TEMPEL API 1 DISINI');            
if (!defined('RESEND_BASE_URL')) define('RESEND_BASE_URL', 'https://api.resend.com');

// ─── Sender Configuration ────────────────────────────────────────
if (!defined('RESEND_FROM_NAME')) define('RESEND_FROM_NAME', 'Little Salt Bread');
if (!defined('RESEND_FROM_EMAIL')) define('RESEND_FROM_EMAIL', 'noreply@shisuka.online');

// ─── Email Templates ────────────────────────────────────────────

/**
 * Generate HTML email wrapper with branding
 */
function emailTemplate(string $title, string $bodyContent): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#fffbeb;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <div style="max-width:600px;margin:0 auto;background-color:#ffffff;">
        <!-- Header -->
        <div style="background-color:#d97706;padding:24px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:24px;letter-spacing:2px;">
                little <span style="font-weight:800;">SALT BREAD</span>
            </h1>
            <p style="margin:4px 0 0;color:#fef3c7;font-size:13px;">Blok M • Salt Bread Renyah Gurih</p>
        </div>
        
        <!-- Body -->
        <div style="padding:32px 24px;">
            {$bodyContent}
        </div>
        
        <!-- Footer -->
        <div style="background-color:#f9fafb;padding:20px 24px;text-align:center;border-top:1px solid #e5e7eb;">
            <p style="margin:0;color:#9ca3af;font-size:12px;">
                &copy; 2026 Little Salt Bread Blok M. All rights reserved.<br>
                Blok M, Jakarta Selatan • Senin - Minggu 10:00 - 20:00
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Build verification email content
 */
function verificationEmailContent(string $name, string $verifyUrl): string {
    return emailTemplate('Verifikasi Email Anda', <<<HTML
        <h2 style="color:#1f2937;margin:0 0 16px;">Halo, {$name}!</h2>
        <p style="color:#4b5563;line-height:1.6;">
            Terima kasih telah mendaftar di <strong>Little Salt Bread</strong>. 
            Silakan klik tombol di bawah untuk memverifikasi alamat email Anda:
        </p>
        <div style="text-align:center;margin:32px 0;">
            <a href="{$verifyUrl}" 
               style="display:inline-block;background-color:#d97706;color:#ffffff;padding:14px 32px;
                      text-decoration:none;border-radius:8px;font-weight:600;font-size:16px;">
                Verifikasi Email Saya
            </a>
        </div>
        <p style="color:#9ca3af;font-size:13px;">
            Jika Anda tidak merasa mendaftar, abaikan email ini.<br>
            Link verifikasi berlaku selama 24 jam.
        </p>
HTML);
}

/**
 * Build password reset email content
 */
function resetPasswordEmailContent(string $name, string $resetUrl): string {
    return emailTemplate('Reset Password', <<<HTML
        <h2 style="color:#1f2937;margin:0 0 16px;">Reset Password</h2>
        <p style="color:#4b5563;line-height:1.6;">
            Halo <strong>{$name}</strong>, kami menerima permintaan untuk mereset password akun Anda.
            Klik tombol di bawah untuk membuat password baru:
        </p>
        <div style="text-align:center;margin:32px 0;">
            <a href="{$resetUrl}" 
               style="display:inline-block;background-color:#d97706;color:#ffffff;padding:14px 32px;
                      text-decoration:none;border-radius:8px;font-weight:600;font-size:16px;">
                Reset Password
            </a>
        </div>
        <p style="color:#9ca3af;font-size:13px;">
            Jika Anda tidak meminta reset password, abaikan email ini.<br>
            Link reset berlaku selama 1 jam.
        </p>
HTML);
}

/**
 * Build order confirmation email content
 */
function orderConfirmationEmailContent(string $name, string $queueNumber, string $orderCode, string $total, string $trackingUrl): string {
    return emailTemplate('Pesanan Diterima!', <<<HTML
        <h2 style="color:#1f2937;margin:0 0 16px;">Pesanan Diterima!</h2>
        <p style="color:#4b5563;line-height:1.6;">
            Halo <strong>{$name}</strong>, pesanan Anda sudah kami terima.
        </p>
        <div style="background-color:#fef3c7;border-radius:12px;padding:20px;margin:24px 0;text-align:center;">
            <p style="margin:0 0 4px;color:#92400e;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Nomor Antrean</p>
            <p style="margin:0;color:#d97706;font-size:36px;font-weight:800;">{$queueNumber}</p>
            <p style="margin:8px 0 0;color:#92400e;font-size:13px;">Kode: {$orderCode}</p>
        </div>
        <p style="color:#4b5563;">
            <strong>Total:</strong> {$total}
        </p>
        <div style="text-align:center;margin:24px 0;">
            <a href="{$trackingUrl}" 
               style="display:inline-block;background-color:#d97706;color:#ffffff;padding:14px 32px;
                      text-decoration:none;border-radius:8px;font-weight:600;font-size:16px;">
                Lacak Pesanan
            </a>
        </div>
HTML);
}

