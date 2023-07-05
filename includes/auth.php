<?php

require_once 'encryption.php';

// Vault URL and access token endpoint
$keyVaultURL = decrypt_options(get_option('wp_event_data_collector_azure_vault_url'));
$tenantID = decrypt_options(get_option('wp_event_data_collector_tenant_id'));
$accessTokenEndpoint = 'https://login.microsoftonline.com/' . $tenantID . '/oauth2/token';

// AAD Auth Paramaters
$clientID = decrypt_options(get_option('wp_event_data_collector_azure_client_id'));
$clientSecret = decrypt_options(get_option('wp_event_data_collector_azure_client_secret'));
$resource = 'https://vault.azure.net';

// Key Name
$keyName = decrypt_options(get_option('wp_event_data_collector_azure_key_name'));

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
//Only run Auth if option is Azure Key Vault and all of the fields are filled out.
if ($option == 'akv' && $clientID != NULL && $clientSecret != NULL && $keyName != NULL && $keyVaultURL != NULL && $tenantID != NULL) {
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    curl_close($ch);

    $accessToken = json_decode($response)->access_token;

    // Retrieve key from AKV
    $url = $keyVaultURL . '/keys/' . $keyName . '?api-version=7.2';

    $headers = array(
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $key = json_decode($response);
}
