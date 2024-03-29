<?php

require_once ABSPATH . WPINC . '/pluggable.php';
require_once 'auth.php';

$option = get_option('wp_event_data_collector_identity_dropdown');


//Generate shared key auth signature
function generateAuthorizationHeader($workspace_ID, $sharedKey, $date, $contentLength) {
        $stringToSign = "POST\n" . $contentLength . "\napplication/json\nx-ms-date:" . $date . "\n/api/logs";
        $stringToSign = mb_convert_encoding($stringToSign, 'UTF-8');
        $sharedKeyBytes = base64_decode($sharedKey);
        $signature = hash_hmac('sha256', $stringToSign, $sharedKeyBytes, true);
        $encodedSignature = base64_encode($signature);
        $authorizationHeader = "SharedKey " . $workspace_ID . ":" . $encodedSignature;
        return $authorizationHeader;
}

//Push data to MS Data Collector API
function push_file_data_to_api() {

    //File path where data lives
    $file_path = WP_CONTENT_DIR . '/event_data.json';
    $error_path = WP_CONTENT_DIR . '/error_log.txt';
    $auth_path = WP_CONTENT_DIR . '/auth_log.txt';

    // Settings
    $workspace_ID = decrypt_options(get_option('wp_event_data_collector_workspace_id'));
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $table_name = decrypt_options(get_option('wp_event_data_collector_table_name'));
    $custom_email = trim(decrypt_options(get_option('wp_event_data_collector_email')));
    $primary_key = decrypt_options(get_option('wp_event_data_collector_primary_key'));

    $api_endpoint = 'https://' . $workspace_ID . '.ods.opinsights.azure.com/OperationalData.svc/PostJsonDataItems';

    //Read existing file data
    $file_data = [];
    if (file_exists($file_path)) {
        $file_data = json_decode(file_get_contents($file_path), true);
    }

    $payload = [];
    foreach ($file_data as $object) {
        $payload[] = [
            "DataType" => "WP-Log",
            "IPName" => "WSAL-Data-Collector",
            "Data" => $object
        ];
    }
    $jsonPayload = json_encode($payload);

    // Get current UTC time in RFC1123 Formatting
    $date = gmdate('D, d M Y H:i:s T');

    // Build the headers
    $header = [
        'Content-Type: application/json',
        //'Log-Type: ' . $table_name,
        'x-ms-date: ' . $date,
        //'time-generated-field: date',
        'Authorization: ' . generateAuthorizationHeader($workspace_ID, $primary_key, $date, strlen($jsonPayload)),
    ];

    //Init cURL
    $ch = curl_init();

    //Set Options
    curl_setopt($ch, CURLOPT_URL, $api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode == 200) {
        echo 'Data transfer successful.';
        file_put_contents($file_path, '[]'); // Only dump the file if transfer was success.
        update_option('email_sent_flag', false);
        update_option('wp_event_collector_last_success', current_time('mysql') . ' UTC' . wp_timezone_string());
    } else {
        file_put_contents($error_path, 'Failed to send data. Status code: ' . $httpCode . '. Message:  ' . $response);
        echo 'Failed to send data. Status code: ' . $httpCode . '. Message:  ' . $response;
        $email_sent = get_option('email_sent_flag');
        if ($email_sent == 'setflag') { //If email already sent, return.
            return;
        }
        // If email hasn't been sent for this set of errors, send it to admin.
        // If $custom_email is set, use that instead of admin.
        $admin_email = get_option('admin_email');
        if ($custom_email) {
            $to = $custom_email;
        } else {
            $to = $admin_email;
        }
        $subject = 'LAW Data Transfer Failed';
        $message = 'Failed to send data. Status code: ' . $httpCode . '. Message:  ' . $response . '</br>Please check your config.';
        $header = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $header);
        update_option('email_sent_flag', 'setflag');
    }

    curl_close($ch);

    return true;
}
