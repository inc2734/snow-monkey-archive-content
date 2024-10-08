<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

use Inc2734\WP_Customizer_Framework\Framework;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;
use Framework\Controller\Controller;

$custom_post_types = Helper::get_custom_post_types();

foreach ( $custom_post_types as $custom_post_type ) {
	Framework::control(
		'checkbox',
		Helper::get_custom_post_archive_meta_name( 'replace-title', $custom_post_type ),
		array(
			'label'           => __( 'Replace page title', 'snow-monkey-archive-content' ),
			'priority'        => 12,
			'default'         => false,
			'active_callback' => function () use ( $custom_post_type ) {
				return is_post_type_archive( $custom_post_type )
						&& get_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $custom_post_type ) )
						&& get_theme_mod( Helper::get_custom_post_archive_meta_name( 'display-title', $custom_post_type ) );
			},
		)
	);
}

if ( ! is_customize_preview() ) {
	return;
}

$panel = Framework::get_panel( 'design' );

foreach ( $custom_post_types as $custom_post_type ) {
	$section = Framework::get_section( 'design-' . $custom_post_type . '-archive' );
	$control = Framework::get_control( Helper::get_custom_post_archive_meta_name( 'replace-title', $custom_post_type ) );
	$control->join( $section )->join( $panel );
}
