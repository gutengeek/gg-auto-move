<?php
/**
 * Plugin Name:       GG Auto Move
 * Plugin URI:        https://gutengeek.com
 * Description:       Move product with hight view count to a product category.
 * Version:           1.0.1
 * Author:            GutenGeek
 * Author URI:        https://gutengeek.com/contact
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gg-auto-move
 * Domain Path:       /languages
 * WC requires at least: 3.6
 * WC tested up to: 3.9.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */
define( 'GGAUTOMOVE', 'gg-auto-move' );
define( 'GGAUTOMOVE_VERSION', '1.0.1' );
define( 'GGAUTOMOVE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GGAUTOMOVE_URL', plugin_dir_url( __FILE__ ) );
define( 'GGAUTOMOVE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'GGAUTOMOVE_PLUGIN_TEXT_DOMAIN', 'gg-auto-move' );
define( 'GGAUTOMOVE_METABOX_PREFIX', '_' );

require_once( GGAUTOMOVE_DIR . 'includes/functions.php' );
require_once( GGAUTOMOVE_DIR . 'includes/admin/settings.php' );
require_once( GGAUTOMOVE_DIR . 'includes/modules/auto-move.php' );
require_once( GGAUTOMOVE_DIR . 'includes/modules/category-move.php' );
require_once( GGAUTOMOVE_DIR . 'includes/modules/dashboard-widget.php' );

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */
register_activation_hook( __FILE__, 'gg_auto_move_activate' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */
register_deactivation_hook( __FILE__, 'gg_auto_move_deactivate' );

function gg_auto_move_activate() {
	if ( ! is_blog_installed() ) {
		return;
	}

	gg_auto_move_create_cron_jobs();
}

function gg_auto_move_deactivate() {

}
