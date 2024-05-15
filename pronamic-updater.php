<?php
/**
 * Pronamic WordPress Updater
 *
 * @package   PronamicWordPressUpdater
 * @author    Pronamic
 * @copyright 2023 Pronamic
 *
 * @wordpress-plugin
 * Plugin Name: Pronamic Updater
 * Description: This WordPress plugin extends the WordPress update system with updates from Pronamic.
 * Version:     1.0.2
 * Author:      Pronamic
 * Author URI:  https://www.pronamic.eu/
 * Text Domain: pronamic-updater
 * Domain Path: /languages/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.htm
 * Update URI:  https://wp.pronamic.directory/plugins/pronamic-payment-gateways-fees-for-woocommerce/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload_packages.php';

\Pronamic\WordPress\Updater\Plugin::instance()->setup();
