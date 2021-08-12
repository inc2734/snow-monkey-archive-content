<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

use Inc2734\WP_Customizer_Framework\Framework;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;
use Framework\Controller\Controller;


Framework::control(
	'checkbox',
	Helper::get_home_meta_name( 'display-title' ),
	[
		'label'    => __( 'Display page title', 'snow-monkey-archive-content' ),
		'priority' => 11,
		'default'  => true,
	]
);

if ( ! is_customize_preview() ) {
	return;
}

$panel   = Framework::get_panel( 'design' );
$section = Framework::get_section( 'design-home' );
$control = Framework::get_control( Helper::get_home_meta_name( 'display-title' ) );
$control->join( $section )->join( $panel );
