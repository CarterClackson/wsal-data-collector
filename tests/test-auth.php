<?php

require_once WP_CONTENT_DIR . '/plugins/wsal-data-collector/includes/encryption.php';


add_action('wp_ajax_test_vault_connection', 'test_vault_connection');
add_action('wp_ajax_nopriv_test_vault_connection', 'test_vault_connection');

add_action('wp_ajax_test_vault_connection_push', 'test_vault_connection_push');
add_action('wp_ajax_nopriv_test_vault_connection_push', 'test_vault_connection_push');

function test_vault_connection() {

    $auth_path = WP_CONTENT_DIR . '/auth_log.txt';

    // Vault URL and access token endpoint
    $keyVaultURL = decrypt_options(get_option('wp_event_data_collector_azure_vault_url'));
    $tenantID = decrypt_options(get_option('wp_event_data_collector_azure_tenant_id'));
    $accessTokenEndpoint = 'https://login.microsoftonline.com/' . $tenantID . '/oauth2/token';
    $variable_option = get_option('wp_event_data_collector_azure_variable_dropdown');

    // AAD Auth Paramaters
    $clientID = decrypt_options(get_option('wp_event_data_collector_azure_client_id'));
    $clientSecret = decrypt_options(get_option('wp_event_data_collector_azure_client_secret'));
    $resource = 'https://vault.azure.net';

    // Key Name
    $keyName = decrypt_options(get_option('wp_event_data_collector_azure_variable_name'));

    $variable_switch = '';
    if ($variable_option == 'key') {
        $variable_switch = '/keys/';
    } else {
        $variable_switch = '/secrets/';
    }

    //Request access token from AAD
    $data = array(
        'grant_type' => 'client_credentials',
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'resource' => $resource
    );
    $options = array(
        CURLOPT_URL => $accessTokenEndpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data)
    );

    $option = get_option('wp_event_data_collector_identity_dropdown');

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
        $success_message = 'Test Key Vault Connection was successful.';
        $response = array (
            'status' => 'success',
            'message' => $success_message
        );
    } else {
        $error_message = 'Failed to authenticate with Key Vault. Status code: ' . $httpCode . '. Message: ' . $response;
        $response = array(
            'status' => 'error',
            'message' => $error_message
        );
    }
    curl_close($ch);
    wp_send_json($response);
    die();
}

function test_vault_connection_push() {
        
    $auth_path = WP_CONTENT_DIR . '/auth_log.txt';

    // Vault URL and access token endpoint
    $keyVaultURL = decrypt_options(get_option('wp_event_data_collector_azure_vault_url'));
    $tenantID = decrypt_options(get_option('wp_event_data_collector_azure_tenant_id'));
    $accessTokenEndpoint = 'https://login.microsoftonline.com/' . $tenantID . '/oauth2/token';
    $variable_option = get_option('wp_event_data_collector_azure_variable_dropdown');

    // AAD Auth Paramaters
    $clientID = decrypt_options(get_option('wp_event_data_collector_azure_client_id'));
    $clientSecret = decrypt_options(get_option('wp_event_data_collector_azure_client_secret'));
    $resource = 'https://vault.azure.net';

    // Key Name
    $keyName = decrypt_options(get_option('wp_event_data_collector_azure_variable_name'));

    $variable_switch = '';
    if ($variable_option == 'key') {
        $variable_switch = '/keys/';
    } else {
        $variable_switch = '/secrets/';
    }

    //Request access token from AAD
    $data = array(
        'grant_type' => 'client_credentials',
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'resource' => $resource
    );
    $options = array(
        CURLOPT_URL => $accessTokenEndpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data)
    );

    $option = get_option('wp_event_data_collector_identity_dropdown');

    //Get auth code
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);

    $decoded = json_decode($response);
    $decoded = $decoded->access_token;
    $accessToken = $decoded;
    
    curl_close($ch);

    // Retrieve key from AKV
    $url = $keyVaultURL . $variable_switch . $keyName . '?api-version=7.2';

    $headers = array(
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
        $success_message = 'Test Get from Key Vault was successful.';
        $response = array (
            'status' => 'success',
            'message' => $success_message
        );
    } else {
        if ($httpCode == 0) {
            $error_message = 'Failed to send data with Code 0. Unable to connect to API. Please check your config above.';
            $response = array(
                'status' => 'error',
                'message' => $error_message
            );
        } else {
            $error_message = 'Failed to get from Key Vault. Status code: ' . $httpCode . '. Message: ' . $response;
            $response = array(
                'status' => 'error',
                'message' => $error_message
            );
        }
    }
    curl_close($ch);
    wp_send_json($response);
    die();
}

