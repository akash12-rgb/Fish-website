<?php
// ============================================================
//  config/payment.php  –  ICICI Bank Orange Pay settings
// ============================================================

define('ORANGEPAY_MERCHANT_ID',  getenv('OP_MERCHANT_ID')  ?: 'YOUR_MERCHANT_ID');
define('ORANGEPAY_MERCHANT_KEY', getenv('OP_MERCHANT_KEY') ?: 'YOUR_MERCHANT_SECRET_KEY');
define('ORANGEPAY_CURRENCY',     'INR');   // change to IDR / USD as needed

// Live vs sandbox
define('ORANGEPAY_SANDBOX', true);

define('ORANGEPAY_BASE_URL',
    ORANGEPAY_SANDBOX
        ? 'https://sandbox.orangepay.icicibank.com/payment/initiate'
        : 'https://orangepay.icicibank.com/payment/initiate'
);

define('ORANGEPAY_RETURN_URL',  APP_URL . '/payments/payment_success.php');
define('ORANGEPAY_CANCEL_URL',  APP_URL . '/payments/payment_failure.php');
