<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

use Inc2734\WP_Customizer_Framework\Framework;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;
use Framework\Controller\Controller;

$all_terms = Helper::get_terms( 'post_tag' );

foreach ( $all_terms as $_term ) {
	Framework::control(
		'checkbox',
		Helper::get_term_meta_name( 'remove-top-margin', $_term ),
		[
			'label'           => __( 'Remove top margin of the content', 'snow-monkey-archive-content' ),
			'priority'        => 13,
			'default'         => false,
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

foreach ( $all_terms as $_term ) {
	$section = Framework::get_section( 'design-' . $_term->taxonomy . '-' . $_term->term_id );
	$control = Framework::get_control( Helper::get_term_meta_name( 'remove-top-margin', $_term ) );
	$control->join( $section )->join( $panel );
}
