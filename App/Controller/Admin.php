<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

class Admin {

	public function __construct() {
		add_action( 'admin_head', [ $this, '_admin_notices' ] );
	}

	public function _admin_notices() {
		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_no_snow_monkey' ] );
			return;
		}

		if ( ! version_compare( $theme->get( 'Version' ), '7.9.0', '>=' ) ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_invalid_snow_monkey_version' ] );
			return;
		}

		if ( class_exists( '\Snow_Monkey\Plugin\CategoryContent\Bootstrap' ) ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_with_category_content' ] );
			return;
		}
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
				<?php esc_html_e( '[Snow Monkey Archive Content] Needs the Snow Monkey v7.9 or more.', 'snow-monkey-archive-content' ); ?>
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
