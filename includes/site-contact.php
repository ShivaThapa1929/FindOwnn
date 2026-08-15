<?php
/**
 * Findownn — public site contact / WhatsApp (CRM)
 */
$site_whatsapp_display = '+91 95583 46768';
$site_whatsapp_digits  = '919558346768';
$site_phone_tel        = '+919558346768';
$site_contact_email    = 'findownn@gmail.com';
$site_whatsapp_message = 'Hi Findownn! I need help with playground booking.';
$site_whatsapp_url     = 'https://wa.me/' . $site_whatsapp_digits . '?text=' . rawurlencode($site_whatsapp_message);

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
