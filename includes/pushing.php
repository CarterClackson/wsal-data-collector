<?php

$option = get_option('wp_event_data_collector_identity_dropdown');

if ($option = 'akv') {
    require_once 'auth.php';
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

    if ($option == 'hardcode') {
        $primary_key = decrypt_options(get_option('wp_event_data_collector_primary_key')); //Stored value
    } else {
        $primary_key = $key->value; // AKV
    }

    $api_endpoint = 'https://' . $workspace_ID . '.ods.opinsights.azure.com/api/logs?api-version=2016-04-01&table=' . $table_name;

    //Read existing file data
    $file_data = [];
    if (file_exists($file_path)) {
        $file_data = json_decode(file_get_contents($file_path), true);
    }

    $payload = [];
    //Iterate through each object to build payload
    foreach($file_data as $object) {
        $payload = $file_data;
    }
    $jsonPayload = json_encode($payload);

    //Generate shared key auth signature
    function generateAuthorizationHeader($workspace_ID, $sharedKey, $date, $auth_path, $contentLength) {
        $stringToSign = "POST\n" . $contentLength . "\napplication/json\nx-ms-date:" . $date . "\n/api/logs";
        $stringToSign = mb_convert_encoding($stringToSign, 'UTF-8');
        $sharedKeyBytes = base64_encode($sharedKey);
        $signature = hash_hmac('sha256', $stringToSign, $sharedKeyBytes, true);
        $encodedSignature = base64_encode($signature);
        $authorizationHeader = "SharedKey " . $workspace_ID . ":" . $encodedSignature;
        return $authorizationHeader;
    }

    // Get current UTC time in RFC1123 Formatting
    $date = gmdate('D, d M Y H:i:s T');

    // Build the headers
    $header = [
        'Content-Type: application/json',
        'Log-Type: SampleData',
        'x-ms-date: ' . $date,
        'Authorization: ' . generateAuthorizationHeader($workspace_ID, $primary_key, $date, $auth_path, strlen($jsonPayload)),
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

    $email_sent = get_option('email_sent_flag');

    if ($httpCode == 200) {
        echo 'Data transfer successful.';
        file_put_contents($error_path, 'Data sent successfully!');
        file_put_contents($file_path, '[]'); // Only dump the file if transfer was success.
        update_option('email_sent_flag', true);
    } else {
        file_put_contents($error_path, 'Failed to send data. Status code: ' . $httpCode . '. Message:  ' . $response);
        echo 'Failed to send data. Status code: ' . $httpCode . '. Message:  ' . $response;

        if ($email_sent) { //If email already sent, return.
            return;
        }
        // If email hasn't been sent for this set of errors, send it to admin.
        $admin_email = get_option('admin_email');
        $to = $admin_email;
        $subject = 'LAW Data Transfer Failed';
        $message = 'Failed to send data. Status code: ' . $httpCode . '. Message:  ' . $response . '</br>Please check your config.';
        $header = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);
    }

    curl_close($ch);

    return true;
}

// Additional cron interval
add_filter('cron_schedules', 'add_cron_interval' );
function add_cron_interval( $schedules ) {
    $schedules['ten minutes'] = array(
        'interval' => 600, //Time in seconds
        'display' => 'Every Ten Minutes'
    );
    return $schedules;
}

// Schedule cron to push
add_action('wp_event_data_collector', 'push_file_data_to_api');
if (!wp_next_scheduled('wp_event_data_collector')) {
    wp_schedule_event(time(), 'ten_minutes', 'wp_event_data_collector');
}