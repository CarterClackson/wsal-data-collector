<?php
//Log data to file on server for ingestion
function log_event_data_to_file($event_data) {
    //File path where event data should be stored temporarily
    $file_path = WP_CONTENT_DIR . '/event_data.json';

    // Read existing file before adding new
    $file_data = [];
    if (file_exists($file_path)) {
        $file_data = json_decode(file_get_contents($file_path), true);
    }

    // Append new data to file
    $file_data[] = $event_data;
    file_put_contents($file_path, json_encode($file_data));

    return $event_data;
}

add_filter('wsal_event_data_before_log', 'log_event_data_to_file');