<?php
/**
 * Plugin name: Snow Monkey Archive Content
 * Description: Require Snow Monkey v7.9 or more
 * Version: 0.4.1
 * Author: inc2734
 * Author URI: https://2inc.org
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-monkey-archive-content
 *
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent;

use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

define( 'SNOW_MONKEY_ARCHIVE_CONTENT_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SNOW_MONKEY_ARCHIVE_CONTENT_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class Bootstrap {

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, '_plugins_loaded' ] );
	}

	public function _plugins_loaded() {
		load_plugin_textdomain( 'snow-monkey-archive-content', false, basename( __DIR__ ) . '/languages' );

		add_action( 'init', [ $this, '_activate_autoupdate' ] );

		new App\Controller\Front();
		new App\Controller\Admin();
		new App\Controller\Edit();
		new App\Controller\Customizer();
	}

	/**
	 * Activate auto update using GitHub
	 *
	 * @return void
	 */
	public function _activate_autoupdate() {
		new \Inc2734\WP_GitHub_Plugin_Updater\Bootstrap(
			plugin_basename( __FILE__ ),
			'inc2734',
			'snow-monkey-archive-content'
		);
	}
}

require_once( SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/vendor/autoload.php' );
new Bootstrap();

/**
 * Uninstall callback function
 *
 * @return void
 */
function uninstall_callback() {
	$categories = Helper::get_terms( 'category' );
	$post_tags  = Helper::get_terms( 'post_tag' );
	$terms      = array_merge( $categories, $post_tags );

	$taxonomies = Helper::get_taxonomies();
	foreach ( $taxonomies as $_taxonomy ) {
		$terms = array_merge( $terms, Helper::get_terms( $_taxonomy ) );
	}

	foreach ( $terms as $term ) {
		remove_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'display-title', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'remove-top-margin', $term ) );
	}

	$custom_post_types = Helper::get_custom_post_types();
	foreach ( $custom_post_types as $custom_post_type ) {
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'display-title', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'remove-top-margin', $custom_post_type ) );
	}

	remove_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'display-title' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'remove-top-margin' ) );
}
register_uninstall_hook( __FILE__, 'uninstall_callback' );
