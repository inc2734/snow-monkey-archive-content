<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Customizer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'snow_monkey_post_load_customizer', [ $this, '_load_customizer' ] );
	}

	/**
	 * Loads customizer.
	 */
	public function _load_customizer() {
		$this->_load( SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/customizer' );
	}

	/**
	 * Load files.
	 *
	 * @param string $directory Target directory.
	 */
	protected function _load( $directory ) {
		foreach ( glob( untrailingslashit( $directory ) . '/*' ) as $file ) {
			if ( is_dir( $file ) ) {
				$basename = basename( $file );

				$sections = [
					'author',
					'category',
					'custom-post-archive',
					'custom-taxonomy',
					'home',
					'post-tag',
				];

				if ( in_array( $basename, $sections, true ) ) {
					$enable_section = apply_filters( 'snow_monkey_archive_content_enable_assignment_' . $basename, true );
					if ( ! $enable_section ) {
						continue;
					}
				}

				$this->_load( $file );
			} else {
				require_once( $file );
			}
		}
	}
}
