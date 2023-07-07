<?php 
/* 
Plugin Name: WSAL Data Collector API Integration
Description: This plugin consumes logged events out of the WP Activity Log plugin and pushes them to Microsoft's Data Collector API to use in Log Analytics Workspace/Azure Sentinel
Author: Carter Clackson
AuthorURI: https://carterclackson.ca
Version: 1.0.0
*/

require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/logging.php';
require_once plugin_dir_path(__FILE__) . 'includes/pushing.php';
require_once plugin_dir_path(__FILE__) . 'includes/encryption.php';
require_once plugin_dir_path(__FILE__) . 'tests/test-push.php';
require_once plugin_dir_path(__FILE__) . 'tests/test-notification.php';

register_activation_hook(__FILE__, 'wp_event_data_collector_activate');
register_deactivation_hook(__FILE__, 'wp_event_data_collector_deactivate');
register_uninstall_hook(__FILE__, 'wp_event_data_collector_uninstall');

function wp_event_data_collector_enqueue_scripts() {
    wp_enqueue_script( 'admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'wp_event_data_collector_enqueue_scripts' );

function wp_event_data_collector_enqueue_styles() {
    wp_enqueue_style( 'admin-style', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
}
add_action( 'admin_enqueue_scripts', 'wp_event_data_collector_enqueue_styles' );

function wp_event_data_collector_activate() {
    add_option('encryption_key_exists', false);
    $keys = get_option('encryption_key_exists');
    are_key_and_iv_present($keys);
    if ((!is_plugin_active('wp-security-audit-log/wp-security-audit-log.php')) && (!is_plugin_active('wp-security-audit-log-premium/wp-security-audit-log.php')) ) {
        // The required plugin is not active, so prevent activation
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate the current plugin
        wp_die('Error: The required plugin "WP Activity Log" is not active. This plugin cannot be activated. Please install and activate "WP Activity Log" before activating this one.');
    } 
    add_option('email_sent_flag', false); // Initialize email sent value and store in option.
    add_option('wp_event_collector_last_success', 'No successful transmission');
}

function wp_event_data_collector_deactivate() {
    wp_clear_scheduled_hook('wp_event_data_collector');
    remove_keys_and_iv();
    delete_option('encryption_key_exists');
    delete_option('email_sent_flag');
    delete_option('wp_event_collector_last_success');
}

function wp_event_data_collector_uninstall() {
    delete_option('wp_event_data_collector_identity_dropdown');
    delete_option( 'wp_event_data_collector_workspace_id');
    delete_option( 'wp_event_data_collector_primary_key');
    delete_option('wp_event_data_collector_table_name');
    delete_option( 'wp_event_data_collector_azure_client_id');
    delete_option('wp_event_data_collector_azure_client_secret');
    delete_option('wp_event_data_collector_azure_tenant_id');
    delete_option( 'wp_event_data_collector_azure_vault_url');
    delete_option('wp_event_data_collector_azure_key_name');
}


add_filter('cron_schedules', 'add_cron_interval' );
function add_cron_interval( $schedules ) {
    $schedules['ten_minutes'] = array(
        'interval' => 600,
        'display' => 'Every Ten Minutes'
    );
    return $schedules;
}

add_action('wp_event_data_collector', 'push_file_data_to_api');
if (!wp_next_scheduled('wp_event_data_collector')) {
    wp_schedule_event((time() + 600), 'ten_minutes', 'wp_event_data_collector');
}

// Filters to encrypt each option before pushing to the DB.
add_filter('pre_update_option_wp_event_data_collector_primary_key', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_workspace_id', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_table_name', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_email', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_azure_client_id', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_azure_client_secret', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_azure_tenant_id', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_azure_vault_url', 'encrypt_options', 10, 3);
add_filter('pre_update_option_wp_event_data_collector_azure_key_name', 'encrypt_options', 10, 3);

// Filters for decrypting options before use
add_filter('pre_get_option_wp_event_data_collector_primary_key', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_workspace_id', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_table_name', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_email', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_azure_client_id', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_azure_client_secret', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_azure_tenant_id', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_azure_vault_url', 'decrypt_options');
add_filter('pre_get_option_wp_event_data_collector_azure_key_name', 'decrypt_options');
