<?php

require_once ABSPATH . WPINC . '/pluggable.php';

add_action('wp_ajax_test_notification', 'test_notification');
add_action('wp_ajax_nopriv_test_notification', 'test_notification');

// Call the test_push function if it is an AJAX request
/*if (defined('DOING_AJAX') && DOING_AJAX) {
    test_notification();
} */

function test_notification() {
    $admin_email = get_option('admin_email');
    $custom_email = get_option('wp_data_collector_email');
    if ($custom_email) {
        $to = $custom_email;
    } else {
        $to = $admin_email;
    }
    $subject = 'Notification Email Test';
    $message = 'Your email has been configured correctly.';
    $header = array('Content-Type: text/html; charset=UTF-8');

    $email_sent = wp_mail($to, $subject, $message, $header);

    if ($email_sent) {
        $response = array (
            'status' => 'success',
            'message' => 'Sending server was successful. Please check your email.'
        );
    } else {
        $response = array (
            'status' => 'error',
            'message' => 'Sending server was not successful. Check your configuration and ensure your server is capable of sending mail.'
        );
    }
    wp_send_json($response);
}