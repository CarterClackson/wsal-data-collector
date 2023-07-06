<?php
//Admin menu setup
function wp_event_data_collector_register_admin_menu() {
    add_options_page( 
        'Event Data Collector Settings',
        'Event Collector Settings',
        'manage_options',
        'wp_event_data_collector_settings',
        'wp_event_data_collector_render_settings_page'
    );
}
add_action('admin_menu', 'wp_event_data_collector_register_admin_menu');

//Plugin settings page render
function wp_event_data_collector_render_settings_page() {
    ?>
    <div class="wrap">
    <h1>Event Collector Settings</h1>
    <p>If you wish to hardcode your values for Workspace ID and Primary Key please do so here. </br> If you would like to use a key stored in Azure Key Vault, select "Azure Key Vault" in the dropdown and fill out the fields.<br> <span style="color: red">You must scroll down and click save before running any of the tests.</span></p>
    <form method="post" action="options.php">
        <?php
        settings_fields('wp_event_data_collector_options');
        ?>
        <div id="general-settings">
            <?php
                do_settings_sections('wp_event_data_collector_settings_general');
                
            ?>
            <span>Please ensure you have your Workspace ID, Primary Key, and Table Name filled out above and you have saved or the test will fail.<br></span>
            <input type="button" id="general-test" class="button" value="Test Connection to API">
            <div id="general-response"></div>
        </div>
        <div id="identity-settings">
            <?php
                do_settings_sections('wp_event_data_collector_settings_identity');
            ?>
            <span>Please ensure you have your Workspace ID, Table Name, and all of the fields below "Azure Key Vault Settings" filled out above and you have saved or the test will fail.<br></span>
            <input type="button" id="akv-test" class="button" value="Test Key Vault Connection">
            <input type="button" style="margin-left: 15px;" id="general-test" class="button" value="Test Connection to API">
            <div id="akv-response"></div>
        </div>
        <div id="notification-settings">
            <?php
                do_settings_sections('wp_event_data_collector_settings_notification');
            ?>
            <span>If you don't set an email above and save the test will use your Admin email.<br></span>
            <input type="button" id="notification-test" class="button" value="Test Notifications">
            <div id="notification-response"></div>
        </div>
        <?php submit_button('Save Settings'); ?>        
    </form>
    </div>
    <?php
}