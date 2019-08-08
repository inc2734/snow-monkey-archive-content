<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

use Inc2734\WP_Customizer_Framework\Framework;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;
use Framework\Controller\Controller;

$all_pages = Helper::get_draft_pages();

$choices = [
	0 => __( 'None', 'snow-monkey-archive-content' ),
];
foreach ( $all_pages as $_page ) {
	$choices[ $_page->ID ] = $_page->post_title;
}

Framework::control(
	'select',
	Helper::get_home_meta_name( 'page-id' ),
	[
		'label'       => __( 'The page used as content', 'snow-monkey-archive-content' ),
		'description' => __( 'You can select from the draft pages.', 'snow-monkey-archive-content' ),
		'priority'    => 10,
		'default'     => 0,
		'choices'     => $choices,
		'active_callback' => function() {
			return 'home' === Controller::get_view();
		},
	]
);

if ( ! is_customize_preview() ) {
	return;
}

$panel   = Framework::get_panel( 'design' );
$section = Framework::get_section( 'design-home' );
$control = Framework::get_control( Helper::get_home_meta_name( 'page-id' ) );
$control->join( $section )->join( $panel );
