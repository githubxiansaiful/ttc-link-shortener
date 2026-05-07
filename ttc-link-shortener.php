<?php
/**
 * Plugin Name:       TTC Link Shortener
 * Plugin URI:        https://xiansaiful.com/
 * Description:       Custom URL shortener with a branded dashboard for the short_manager role.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Xian Saiful
 * Author URI:        https://xiansaiful.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ttc-link-shortener
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TTCLS_VERSION',   '1.0.0' );
define( 'TTCLS_FILE',      __FILE__ );
define( 'TTCLS_PATH',      plugin_dir_path( __FILE__ ) );
define( 'TTCLS_URL',       plugin_dir_url( __FILE__ ) );
define( 'TTCLS_BASENAME',  plugin_basename( __FILE__ ) );
define( 'TTCLS_TABLE',     'ttcls_links' );
define( 'TTCLS_PAGE_SLUG', 'link-shorturl' );
define( 'TTCLS_ROLE',      'short_manager' );

require_once TTCLS_PATH . 'includes/class-ttcls-helpers.php';
require_once TTCLS_PATH . 'includes/class-ttcls-db.php';
require_once TTCLS_PATH . 'includes/class-ttcls-roles.php';
require_once TTCLS_PATH . 'includes/class-ttcls-activator.php';
require_once TTCLS_PATH . 'includes/class-ttcls-deactivator.php';
require_once TTCLS_PATH . 'includes/class-ttcls-rewrite.php';
require_once TTCLS_PATH . 'includes/class-ttcls-shortcode.php';
require_once TTCLS_PATH . 'includes/class-ttcls-ajax.php';
require_once TTCLS_PATH . 'includes/class-ttcls-assets.php';
require_once TTCLS_PATH . 'includes/class-ttcls-admin-bar.php';
require_once TTCLS_PATH . 'includes/class-ttcls-template.php';
require_once TTCLS_PATH . 'includes/class-ttcls-plugin.php';

register_activation_hook(   __FILE__, [ 'TTCLS_Activator',   'activate'   ] );
register_deactivation_hook( __FILE__, [ 'TTCLS_Deactivator', 'deactivate' ] );

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'ttc-link-shortener', false, dirname( TTCLS_BASENAME ) . '/languages' );
	TTCLS_Plugin::instance();
} );
