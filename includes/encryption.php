<?php
// Check if Key and Initialization Vector exist
function are_key_and_iv_present() {
    $keyFilePath = WP_CONTENT_DIR . '/encryption-key.txt';
    $ivFilePath = WP_CONTENT_DIR . '/encryption-iv.txt';

    if (file_exists($keyFilePath) && file_exists($ivFilePath)) {
        return true;
    }

    return false;
}

// Generate new keys if they don't exist
function generate_and_store_keys() {
    $keyFilePath = WP_CONTENT_DIR . '/encryption-key.txt';
    $ivFilePath = WP_CONTENT_DIR . '/encryption-iv.txt';

    // Generate new keys
    $key = bin2hex(openssl_random_pseudo_bytes(32));
    $iv = bin2hex(openssl_random_pseudo_bytes(16));

    // Save contents to files.
    file_put_contents($keyFilePath, $key);
    file_put_contents($ivFilePath, $iv);
}

// Check if key and IV exist, generate and store if not
if (!are_key_and_iv_present()) {
    generate_and_store_keys();
}