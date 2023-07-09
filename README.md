# WP Security Audit Log to Microsoft Data Collector API Connector

The WP Security Audit Log to Microsoft Data Collector API Connector is a WordPress plugin that integrates the WP Security Audit Log plugin with the Microsoft Data Collector API. It allows you to send security audit logs from your WordPress website to your Microsoft Data Collector API endpoint for centralized logging and analysis within the Log Analytics Workspace or Azure Sentinel.

## Installation

1. Download the plugin ZIP file from the [releases](https://github.com/your-username/your-plugin/releases) page.
2. In your WordPress admin panel, navigate to **Plugins > Add New**.
3. Click on the **Upload Plugin** button and select the downloaded ZIP file.
4. Click **Install Now** and then **Activate** the plugin.

## Configuration

1. Go to the plugin settings page under **Settings > Event Collector Settings**.
2. Enter your Credentials.
3. Configure any additional settings or filters as needed.
4. Save the settings.

## Usage

Once the plugin is installed and configured, it will automatically start sending security audit logs to your Microsoft Data Collector API endpoint every ten minutes. The WP Security Audit Log plugin captures various activities and events occurring on your WordPress website, such as user logins, content changes, plugin activations, and more. These logs will now be forwarded to your Microsoft Data Collector API for further analysis and monitoring.

## Contributing

Contributions are welcome! If you'd like to contribute to this project, please follow these guidelines:

1. Fork the repository and clone it to your local machine.
2. Create a new branch for your feature or bug fix.
3. Make your changes and test them thoroughly.
4. Commit your changes and push them to your forked repository.
5. Submit a pull request, explaining the purpose and scope of your changes.

Please ensure that your code follows the WordPress coding standards and includes appropriate documentation.

## License

This project is licensed under a proprietary license. 

## Acknowledgments

This plugin makes use of the following third-party libraries and resources:

- [WP Security Audit Log](https://wordpress.org/plugins/wp-security-audit-log/): The main plugin that captures and logs security events in WordPress.

## Support

If you encounter any issues, have questions, or need assistance, you can reach out to us through the [issue tracker](https://github.com/your-username/your-plugin/issues) for this project or email clacksoncarter@gmail.com.

## Roadmap

We have the following features planned for future releases:

- Support for custom log filters and exclusions.
- Enhanced error handling and logging.
- Support for multiple custom emails
- Support for other notification streams like Teams or Slack
- Enhanced backend UI and plugin specific page instead of just settings.

Stay tuned for updates!

## Changelog

### 1.0.0

- Initial release of the WP Security Audit Log to Microsoft Data Collector API Connector.

### 1.1.0

- Added tests for user to check connections to Azure Auth, Azure Key Vault, and Data Collector API.
- Added tests for user for custom email notifications.
- Added confirmation of last successful push to UI.
- Improved logic and error messaging for failed pushes to API.
