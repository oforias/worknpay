<?php
/**
 * Paystack Configuration Example
 * 
 * Copy this file to paystack_config.php and update with your actual API keys
 * NEVER commit paystack_config.php to version control!
 * 
 * Get your keys from: https://dashboard.paystack.com/#/settings/developer
 */

// Test Mode Keys (for development/testing)
define('PAYSTACK_SECRET_KEY', 'sk_test_your_test_secret_key_here');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_your_test_public_key_here');

// For Production: Replace with live keys
// define('PAYSTACK_SECRET_KEY', 'sk_live_your_live_secret_key_here');
// define('PAYSTACK_PUBLIC_KEY', 'pk_live_your_live_public_key_here');

// Paystack API Base URL
define('PAYSTACK_API_URL', 'https://api.paystack.co');

/**
 * Test Cards for Development:
 * 
 * Success: 4111111111111111
 * Decline: 4084084084084081
 * 
 * Expiry: Any future date
 * CVV: Any 3 digits
 * OTP: 123456
 */
?>
