<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Customizer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'snow_monkey_post_load_customizer', array( $this, '_load_customizer' ) );
	}

	/**
	 * Loads customizer.
	 */
	public function _load_customizer() {
		$directory = SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/customizer';
		$iterator  = new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS );
		$iterator  = new RecursiveIteratorIterator( $iterator );

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}

			if ( 'php' !== $file->getExtension() ) {
				continue;
			}

			$filepath = $file->getPathname();
			$basename = basename( dirname( dirname( $filepath ) ) );

			$sections = array(
				'author',
				'category',
				'custom-post-archive',
				'custom-taxonomy',
				'home',
				'post-tag',
			);

			if ( in_array( $basename, $sections, true ) ) {
				$enable_section = apply_filters( 'snow_monkey_archive_content_enable_assignment_' . $basename, true );
				if ( ! $enable_section ) {
					continue;
				}
			}

			include_once( $file );
		}
	}
}
