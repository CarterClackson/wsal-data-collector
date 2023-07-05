<?php
// Check if Key and Initialization Vector exist
$keys = get_option('encryption_key_iv_exists');

function are_key_and_iv_present($keys) {
    if ($keys === false) {
        generate_and_store_keys();
        return false;
    }
    return true;
}

// Generate new keys if they don't exist
function generate_and_store_keys() {
    $wp_config_path = ABSPATH . 'wp-config.php';

    // Generate new keys
    $key = bin2hex(openssl_random_pseudo_bytes(32));
    $iv = bin2hex(openssl_random_pseudo_bytes(16));

    $key_code = "define( 'ENCRYPTION_KEY', '" . $key . "' );";
    $iv_code = "define( 'ENCRYPTION_IV', '" . $iv . "' );";

    // Read the existing wp-config.php file content
    $config_content = file_get_contents($wp_config_path);

    // Check if the key and IV definitions exist in the config content
    $key_exists = strpos($config_content, "define( 'ENCRYPTION_KEY'") !== false;
    $iv_exists = strpos($config_content, "define( 'ENCRYPTION_IV'") !== false;

    // If both key and IV definitions exist, do not insert the new code
    if ($key_exists && $iv_exists) {
        return;
    }

    // Find the position of the insertion point
    $insertion_point = "/* That's all, stop editing! Happy publishing. */";
    $insertion_position = strpos($config_content, $insertion_point);

    // If the insertion point exists, insert the new code before it
    if ($insertion_position !== false) {
        $new_config_content = substr_replace($config_content, $key_code . "\n" . $iv_code . "\n", $insertion_position, 0);

        // Write the updated content to the wp-config.php file
        file_put_contents($wp_config_path, $new_config_content);
    }

    update_option('encryption_key_iv_exists', true);
}

// Remove key and IV definitions from wp-config.php
function remove_keys_and_iv() {
    $wp_config_path = ABSPATH . 'wp-config.php';

    // Read the existing wp-config.php file content
    $config_content = file_get_contents($wp_config_path);

    // Define the key and IV definitions with surrounding spaces
    $key_definition = "define( 'ENCRYPTION_KEY', '";
    $iv_definition = "define( 'ENCRYPTION_IV', '";

    // Find the positions of the key and IV definitions
    $key_start = strpos($config_content, $key_definition);
    $iv_start = strpos($config_content, $iv_definition);

    // Check if both key and IV definitions exist in the file
    if ($key_start !== false && $iv_start !== false) {
        // Find the end positions of the key and IV definitions
        $key_end = strpos($config_content, "'", $key_start + strlen($key_definition));
        $iv_end = strpos($config_content, "'", $iv_start + strlen($iv_definition));

        // Find the line breaks before and after the key and IV definitions
        $key_line_start = strrpos($config_content, "\n", -strlen($config_content) + $key_start);
        $key_line_end = strpos($config_content, "\n", $key_end);
        $iv_line_start = strrpos($config_content, "\n", -strlen($config_content) + $iv_start);
        $iv_line_end = strpos($config_content, "\n", $iv_end);

        // Determine the start and end positions of the lines containing key and IV definitions
        $line_start = ($key_line_start < $iv_line_start) ? $key_line_start : $iv_line_start;
        $line_end = ($key_line_end > $iv_line_end) ? $key_line_end : $iv_line_end;

        // Remove the lines containing key and IV definitions from the content
        $config_content = substr_replace($config_content, '', $line_start, $line_end - $line_start + 1);

        // Write the updated content back to the wp-config.php file
        file_put_contents($wp_config_path, $config_content);
    }
}

// Encryption
function encrypt_data($data, $key, $iv) {
    $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encryptedData);
}

// Decryption
function decrypt_data($encryptedData, $key, $iv) {
    $decodedData = base64_decode($encryptedData);
    return openssl_decrypt($decodedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

// Encrypt option on save
function encrypt_options($new_value, $old_value, $option_name) {
    $keyFilePath = ABSPATH . 'wp-config.php';
    $config_content = file_get_contents($keyFilePath);

    // Get the encryption key from wp-config.php
    $key_start = strpos($config_content, "define( 'ENCRYPTION_KEY', '") + strlen("define( 'ENCRYPTION_KEY', '");
    $key_end = strpos($config_content, "'", $key_start);
    $key = substr($config_content, $key_start, $key_end - $key_start);
    $key = hex2bin($key);

    // Get the encryption IV from wp-config.php
    $iv_start = strpos($config_content, "define( 'ENCRYPTION_IV', '") + strlen("define( 'ENCRYPTION_IV', '");
    $iv_end = strpos($config_content, "'", $iv_start);
    $iv = substr($config_content, $iv_start, $iv_end - $iv_start);
    $iv = hex2bin($iv);

    // Encrypt the option value
    $encrypted_value = encrypt_data($new_value, $key, $iv);
    return $encrypted_value;
}

// Decrypt and use
function decrypt_options($option) {
    $keyFilePath = ABSPATH . 'wp-config.php';
    $config_content = file_get_contents($keyFilePath);

    // Get the encryption key from wp-config.php
    $key_start = strpos($config_content, "define( 'ENCRYPTION_KEY', '") + strlen("define( 'ENCRYPTION_KEY', '");
    $key_end = strpos($config_content, "'", $key_start);
    $key = substr($config_content, $key_start, $key_end - $key_start);
    $key = hex2bin($key);

    // Get the encryption IV from wp-config.php
    $iv_start = strpos($config_content, "define( 'ENCRYPTION_IV', '") + strlen("define( 'ENCRYPTION_IV', '");
    $iv_end = strpos($config_content, "'", $iv_start);
    $iv = substr($config_content, $iv_start, $iv_end - $iv_start);
    $iv = hex2bin($iv);

    // Decrypt the option value
    $option = decrypt_data($option, $key, $iv);
    return $option;
}