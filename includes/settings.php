<?php
//Register and initialize plugin settings
function wp_event_data_collector_register_settings() {
    add_settings_section(
        'wp_event_data_collector_general',
        'Azure Data Collector API Settings',
        'wp_event_data_collector_general_section_callback',
        'wp_event_data_collector_settings'
    );
    add_settings_field(
        'wp_event_data_collector_identity_dropdown',
        'Do you want to enter in your primary key or connect to Azure Key Vault?',
        'wp_event_data_collector_identity_dropdown_field_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_general'
    );
    add_settings_field(
        'wp_event_data_collector_workspace_id',
        'Workspace ID',
        'wp_event_data_collector_workspace_id_field_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_general'
    );
    add_settings_field(
        'wp_event_data_collector_primary_key',
        'Primary Key',
        'wp_event_data_collector_primary_key_field_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_general'
    );
    add_settings_field(
        'wp_event_data_collector_table_name',
        'Table Name',
        'wp_event_data_collector_table_name_field_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_general'
    );
    add_settings_section(
        'wp_event_data_collector_identity',
        'Azure Key Vault Settings',
        'wp_event_data_collector_identity_section_callback',
        'wp_event_data_collector_settings',
        'identity-settings'
    );
    add_settings_field(
        'wp_event_data_collector_azure_client_id',
        'Azure Client ID',
        'wp_event_data_collector_azure_client_id_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_identity'
    );
    add_settings_field(
        'wp_event_data_collector_azure_client_secret',
        'Azure Client Secret',
        'wp_event_data_collector_azure_client_secret_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_identity'
    );
    add_settings_field(
        'wp_event_data_collector_azure_tenant_id',
        'Azure Tenant ID',
        'wp_event_data_collector_azure_tenant_id_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_identity'
    );
    add_settings_field(
        'wp_event_data_collector_azure_vault_url',
        'Azure Vault URL',
        'wp_event_data_collector_azure_vault_url_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_identity'
    );
    add_settings_field(
        'wp_event_data_collector_azure_key_name',
        'Azure Key Name',
        'wp_event_data_collector_azure_key_name_callback',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_identity'
    );
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_identity_dropdown');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_workspace_id');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_primary_key');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_table_name');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_azure_client_id');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_azure_client_secret');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_azure_tenant_id');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_azure_vault_url');
    register_setting('wp_event_data_collector_options', 'wp_event_data_collector_azure_key_name');
}
add_action('admin_init', 'wp_event_data_collector_register_settings');

//Callback for general settings section
function wp_event_data_collector_general_section_callback() {
    echo '<p>If you wish to hardcode your values for Workspace ID and Primary Key please do so here. </br> If you would like to use a key stored in Azure Key Vault, select "Azure Key Vault" in the dropdown and fill out the fields.</p></br><span style="color: red; font-size: 18px;">Please be aware that changing the value of the dropdown will wipe prior configurations.</span>';
}
//Callback for identity settings section
function wp_event_data_collector_identity_section_callback($args) {
    $class = isset($args['class']) ? $args['class'] : '';
    echo '<p class="' . esc_attr($class) . '">Configure your Azure Identity settings here</p>';
}
//Call back for Workspace ID Field
function wp_event_data_collector_workspace_id_field_callback() {
    $workspace_id = decrypt_options(get_option('wp_event_data_collector_workspace_id'));
    echo '<input type="text" name="wp_event_data_collector_workspace_id" value="' . esc_attr($workspace_id) . '" />';
}
//Call back for Primary Key Field
function wp_event_data_collector_primary_key_field_callback() {
    $primary_key = decrypt_options(get_option('wp_event_data_collector_primary_key'));
    echo '<input type="text" name="wp_event_data_collector_primary_key" value="' . esc_attr($primary_key) . '" />';
}
//Call back for Table Name Field
function wp_event_data_collector_table_name_field_callback() {
    $table_name = decrypt_options(get_option('wp_event_data_collector_table_name'));
    echo '<input type="text" name="wp_event_data_collector_table_name" value="' . esc_attr($table_name) . '" />';
}
//Call back for Select Field
function wp_event_data_collector_identity_dropdown_field_callback() {
    $option = get_option('wp_event_data_collector_identity_dropdown');
  ?>
  <select name="wp_event_data_collector_identity_dropdown">
    <option value="hardcode" <?php selected($option, 'hardcode'); ?>>Enter it myself</option>
    <option value="akv" <?php selected($option, 'akv'); ?>>Azure Key Vault</option>
  </select>
  <?php
}
// Call back for Client ID Field
function wp_event_data_collector_azure_client_id_callback() {
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $azure_client_id = decrypt_options(get_option('wp_event_data_collector_azure_client_id'));
    if ($option === 'akv') {
        echo '<input type="text" name="wp_event_data_collector_azure_client_id" value="' . esc_attr($azure_client_id) . '" class="wp-event-collector-identity-fields" />';
    } else {
        echo '<input type="text" name="wp_event_data_collector_azure_client_id" value="' . esc_attr($azure_client_id) . '" class="wp-event-collector-identity-fields" />';
    }
}
// Call back for Client Secret Field
function wp_event_data_collector_azure_client_secret_callback() {
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $azure_client_secret = decrypt_options(get_option('wp_event_data_collector_azure_client_secret'));
    if ($option === 'akv') {
        echo '<input type="text" name="wp_event_data_collector_azure_client_secret" value="' . esc_attr($azure_client_secret) . '" class="wp-event-collector-identity-fields" />';
    } else {
        echo '<input type="text" name="wp_event_data_collector_azure_client_secret" value="' . esc_attr($azure_client_secret) . '" class="wp-event-collector-identity-fields" />';
    }
}
// Call back for Tenant ID Field
function wp_event_data_collector_azure_tenant_id_callback() {
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $azure_tenant_id = decrypt_options(get_option('wp_event_data_collector_azure_tenant_id'));
    if ($option === 'akv') {
        echo '<input type="text" name="wp_event_data_collector_azure_tenant_id" value="' . esc_attr($azure_tenant_id) . '" class="wp-event-collector-identity-fields" />';
    } else {
        echo '<input type="text" name="wp_event_data_collector_azure_tenant_id" value="' . esc_attr($azure_tenant_id) . '" class="wp-event-collector-identity-fields" />';
    }
}
// Call back for Vault URL Field
function wp_event_data_collector_azure_vault_url_callback() {
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $azure_vault_url = decrypt_options(get_option('wp_event_data_collector_azure_vault_url'));
    if ($option === 'akv') {
        echo '<input type="text" name="wp_event_data_collector_azure_vault_url" value="' . esc_attr($azure_vault_url) . '" class="wp-event-collector-identity-fields" />';
    } else {
        echo '<input type="text" name="wp_event_data_collector_azure_vault_url" value="' . esc_attr($azure_vault_url) . '" class="wp-event-collector-identity-fields" />';
    }
}
// Call back for Tenant ID Field
function wp_event_data_collector_azure_key_name_callback() {
    $option = get_option('wp_event_data_collector_identity_dropdown');
    $azure_key_name = decrypt_options(get_option('wp_event_data_collector_azure_key_name'));
    if ($option === 'akv') {
        echo '<input type="text" name="wp_event_data_collector_azure_key_name" value="' . esc_attr($azure_key_name) . '" class="wp-event-collector-identity-fields" />';
    } else {
        echo '<input type="text" name="wp_event_data_collector_azure_key_name" value="' . esc_attr($azure_key_name) . '" class="wp-event-collector-identity-fields" />';
    }
}