<?php
require_once WP_CONTENT_DIR . '/plugins/WSAL-Data-Collector/includes/pushing.php';


$option = get_option('wp_event_data_collector_identity_dropdown');

if ($option == 'akv') {
    require_once WP_CONTENT_DIR . '/plugins/WSAL-Data-Collector/includes/auth.php';
}

//Push data to MS Data Collector API
function test_push() {
    $auth_path = WP_CONTENT_DIR . '/auth_log.txt';
    //File path where data lives
    $test_data = '{
        "PluginFile": "\/var\/www\/html\/wp-content\/plugins\/WSAL-Data-Collector\/wp-event-data-collector.php",
        "PluginData": {
            "Name": "Test Data",
            "PluginURI": "",
            "Version": "1.0.0",
            "Author": "Carter Clackson",
            "Network": "False"
        },
        "Timestamp": "1688588355.058100",
        "ClientIP": "192.168.0.1",
        "UserAgent": "Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/114.0.0.0 Safari\/537.36",
        "CurrentUserID": 1,
        "CurrentUserRoles": [
            "administrator"
        ],
        "Severity": 400,
        "Object": "plugin",
        "EventType": "activated",
        "date": "2023-07-05T20:19:15Z"
    }';

    // Settings
    $workspace_ID = decrypt_options(get_option('wp_event_data_collector_workspace_id'));
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $table_name = decrypt_options(get_option('wp_event_data_collector_table_name'));

    $primary_key = decrypt_options(get_option('wp_event_data_collector_primary_key')); //Stored value

    $api_endpoint = 'https://' . $workspace_ID . '.ods.opinsights.azure.com/api/logs?api-version=2016-04-01';

    $jsonPayload = $test_data;

    //Generate shared key auth signature
    function generateAuthorizationHeaderTester($workspace_ID, $sharedKey, $date, $contentLength) {
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
        'Log-Type: ' . $table_name,
        'x-ms-date: ' . $date,
        'time-generated-field: date',
        'Authorization: ' . generateAuthorizationHeaderTester($workspace_ID, $primary_key, $date, strlen($jsonPayload)),
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
        $success_message = 'Test push was successful.';
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
            $error_message = 'Failed to send data. Status code: ' . $httpCode . '. Message: ' . $response;
            $response = array(
                'status' => 'error',
                'message' => $error_message
            );
        }
    }
    curl_close($ch);
    wp_send_json($response);
}

add_action('wp_ajax_test_push', 'test_push');
add_action('wp_ajax_nopriv_test_push', 'test_push');

// Call the test_push function if it is an AJAX request
/*if (defined('DOING_AJAX') && DOING_AJAX) {
    test_push();
}*/
