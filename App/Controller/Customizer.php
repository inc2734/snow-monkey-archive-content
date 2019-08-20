<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Customizer {

	public function __construct() {
		add_action( 'snow_monkey_post_load_customizer', [ $this, '_load_customizer' ] );
	}

	/**
	 * Loads customizer
	 */
	public function _load_customizer() {
		Helper::load( SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/customizer' );
	}
}
