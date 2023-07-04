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
    <form method="post" action="options.php">
        <?php
        settings_fields('wp_event_data_collector_options');
        do_settings_sections('wp_event_data_collector_settings');
        submit_button();
        ?>        
    </form>
    </div>
    <?php
}