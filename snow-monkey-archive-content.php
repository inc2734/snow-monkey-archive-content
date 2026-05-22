<?php
/**
 * Plugin name: Snow Monkey Archive Content
 * Description: Activating this plug-in, you will be able to assign pages to archive pages.
 * Version: 1.2.11
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

		new App\Updater();

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
}

require_once SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/vendor/autoload.php';
new Bootstrap();

/**
 * Uninstall callback function.
 */
function uninstall_callback() {
	$theme_mods = get_theme_mods();
	if ( ! is_array( $theme_mods ) ) {
		return;
	}

	foreach ( array_keys( $theme_mods ) as $key ) {
		if ( 0 !== strpos( $key, 'snow-monkey-archive-content/' ) ) {
			continue;
		}

		remove_theme_mod( $key );
	}
}
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\uninstall_callback' );
