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
		Helper::get_author_meta_name( 'remove-top-margin', $user ),
		[
			'label'    => __( 'Remove top margin of the content', 'snow-monkey-archive-content' ),
			'priority' => 12,
			'default'  => false,
		]
	);
}

if ( ! is_customize_preview() ) {
	return;
}

$panel = Framework::get_panel( 'design' );

foreach ( $all_users as $user ) {
	$section = Framework::get_section( 'design-author-' . $user->ID );
	$control = Framework::get_control( Helper::get_author_meta_name( 'remove-top-margin', $user ) );
	$control->join( $section )->join( $panel );
}
