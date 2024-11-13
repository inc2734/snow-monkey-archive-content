<?php
/**
 * Plugin name: Snow Monkey Archive Content
 * Description: Activating this plug-in, you will be able to assign pages to archive pages.
 * Version: 1.2.4
 * Tested up to: 6.7
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Requires Snow Monkey: 15.3.0
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

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_plugins_loaded' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function _plugins_loaded() {
		add_action( 'init', array( $this, '_load_textdomain' ) );
		add_action( 'init', array( $this, '_activate_autoupdate' ) );

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php esc_html_e( '[Snow Monkey Archive Content] Needs the Snow Monkey.', 'snow-monkey-archive-content' ); ?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		$data = get_file_data(
			__FILE__,
			array(
				'RequiresSnowMonkey' => 'Requires Snow Monkey',
			)
		);

		if (
			isset( $data['RequiresSnowMonkey'] ) &&
			version_compare( $theme->get( 'Version' ), $data['RequiresSnowMonkey'], '<' )
		) {
			add_action(
				'admin_notices',
				function () use ( $data ) {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php
							echo esc_html(
								sprintf(
									// translators: %1$s: version.
									__(
										'[Snow Monkey Archive Content] Needs the Snow Monkey %1$s or more.',
										'snow-monkey-archive-content'
									),
									'v' . $data['RequiresSnowMonkey']
								)
							);
							?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		if ( class_exists( '\Snow_Monkey\Plugin\CategoryContent\Bootstrap' ) ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php esc_html_e( '[Snow Monkey Archive Content] Cannot be activated with the Snow Monkey Category Content. Stop the Snow Monkey Category Content.', 'snow-monkey-archive-content' ); ?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		new App\Controller\Front();
		new App\Controller\Edit();
		new App\Controller\Customizer();
	}

	/**
	 * Load textdomain.
	 */
	public function _load_textdomain() {
		load_plugin_textdomain( 'snow-monkey-archive-content', false, basename( __DIR__ ) . '/languages' );
	}

	/**
	 * Activate auto update using GitHub.
	 */
	public function _activate_autoupdate() {
		new \Inc2734\WP_GitHub_Plugin_Updater\Bootstrap(
			plugin_basename( __FILE__ ),
			'inc2734',
			'snow-monkey-archive-content',
			array(
				'homepage' => 'https://snow-monkey.2inc.org',
			)
		);
	}
}

require_once SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/vendor/autoload.php';
new Bootstrap();

/**
 * Uninstall callback function.
 */
function uninstall_callback() {
	$categories = Helper::get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
		)
	);

	$post_tags = Helper::get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
		)
	);

	$terms             = array_merge( $categories, $post_tags );
	$custom_post_types = Helper::get_custom_post_types();
	$users             = Helper::get_users();

	$taxonomies = Helper::get_taxonomies();
	foreach ( $taxonomies as $_taxonomy ) {
		$terms = array_merge(
			$terms,
			Helper::get_terms(
				array(
					'taxonomy'   => $_taxonomy,
					'hide_empty' => false,
				)
			)
		);
	}

	foreach ( $terms as $term ) {
		remove_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'page-id-2', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'display-title', $term ) );
		remove_theme_mod( Helper::get_term_meta_name( 'remove-top-margin', $term ) );
	}

	foreach ( $custom_post_types as $custom_post_type ) {
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id-2', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'display-title', $custom_post_type ) );
		remove_theme_mod( Helper::get_custom_post_archive_meta_name( 'remove-top-margin', $custom_post_type ) );
	}

	foreach ( $users as $user ) {
		remove_theme_mod( Helper::get_author_meta_name( 'page-id', $user ) );
		remove_theme_mod( Helper::get_author_meta_name( 'page-id-2', $user ) );
		remove_theme_mod( Helper::get_author_meta_name( 'display-title', $user ) );
		remove_theme_mod( Helper::get_author_meta_name( 'remove-top-margin', $user ) );
	}

	remove_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'page-id-2' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'display-title' ) );
	remove_theme_mod( Helper::get_home_meta_name( 'remove-top-margin' ) );
}
register_uninstall_hook( __FILE__, 'uninstall_callback' );
