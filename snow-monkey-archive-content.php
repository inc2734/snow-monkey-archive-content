<?php
/**
 * Plugin name: Snow Monkey Archive Content
 * Description: Require Snow Monkey v7.9 or more
 * Version: 0.6.1
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

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_no_snow_monkey' ] );
			return;
		}

		if ( ! version_compare( $theme->get( 'Version' ), '7.13.2', '>=' ) ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_invalid_snow_monkey_version' ] );
			return;
		}

		if ( class_exists( '\Snow_Monkey\Plugin\CategoryContent\Bootstrap' ) ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_with_category_content' ] );
			return;
		}

		new App\Controller\Front();
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

	public function _admin_notice_no_snow_monkey() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php esc_html_e( '[Snow Monkey Archive Content] Needs the Snow Monkey.', 'snow-monkey-archive-content' ); ?>
			</p>
		</div>
		<?php
	}

	public function _admin_notice_invalid_snow_monkey_version() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php esc_html_e( '[Snow Monkey Archive Content] Needs the Snow Monkey v7.13.2 or more.', 'snow-monkey-archive-content' ); ?>
			</p>
		</div>
		<?php
	}

	public function _admin_notice_with_category_content() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php esc_html_e( '[Snow Monkey Archive Content] Cannot be activated with the Snow Monkey Category Content. Stop the Snow Monkey Category Content.', 'snow-monkey-archive-content' ); ?>
			</p>
		</div>
		<?php
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
	$categories        = Helper::get_terms( 'category' );
	$post_tags         = Helper::get_terms( 'post_tag' );
	$terms             = array_merge( $categories, $post_tags );
	$custom_post_types = Helper::get_custom_post_types();
	$users             = Helper::get_users();

	$taxonomies = Helper::get_taxonomies();
	foreach ( $taxonomies as $_taxonomy ) {
		$terms = array_merge( $terms, Helper::get_terms( $_taxonomy ) );
	}

	foreach ( $terms as $term ) {
		remove_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'display-title', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'remove-top-margin', $term ) );
	}

	foreach ( $custom_post_types as $custom_post_type ) {
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'display-title', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'remove-top-margin', $custom_post_type ) );
	}

	foreach ( $users as $user ) {
		remove_theme_mod( Helper::get_author_meta_name( 'page-id', $user ) );
		remove_theme_mod( Helper::get_author_meta_name( 'display-title', $user ) );
		remove_theme_mod( Helper::get_author_meta_name( 'remove-top-margin', $user ) );
	}

	remove_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'display-title' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'remove-top-margin' ) );
}
register_uninstall_hook( __FILE__, 'uninstall_callback' );
