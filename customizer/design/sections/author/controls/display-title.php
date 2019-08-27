<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

use Inc2734\WP_Customizer_Framework\Framework;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;
use Framework\Controller\Controller;

$all_users = Helper::get_users();

foreach ( $all_users as $user ) {
	Framework::control(
		'checkbox',
		Helper::get_author_meta_name( 'display-title', $user ),
		[
			'label'       => __( 'Display page title', 'snow-monkey-archive-content' ),
			'priority'    => 11,
			'default'     => true,
			'active_callback' => function() {
				return 'archive' === Controller::get_view();
			},
		]
	);
}

if ( ! is_customize_preview() ) {
	return;
}

$panel = Framework::get_panel( 'design' );

foreach ( $all_users as $user ) {
	$section = Framework::get_section( 'design-author-' . $user->ID );
	$control = Framework::get_control( Helper::get_author_meta_name( 'display-title', $user ) );
	$control->join( $section )->join( $panel );
}
