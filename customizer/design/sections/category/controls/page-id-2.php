<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

use Inc2734\WP_Customizer_Framework\Framework;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;
use Framework\Controller\Controller;

$all_terms = Helper::get_terms(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
	)
);

$all_pages = Helper::get_draft_pages();

$choices = array(
	0 => __( 'None', 'snow-monkey-archive-content' ),
);
foreach ( $all_pages as $_page ) {
	$choices[ $_page->ID ] = $_page->post_title;
}

foreach ( $all_terms as $_term ) {
	Framework::control(
		'select',
		Helper::get_term_meta_name( 'page-id-2', $_term ),
		array(
			'label'       => __( 'The page used as content', 'snow-monkey-archive-content' ) . '2',
			'description' => __( 'You can select from the draft pages.', 'snow-monkey-archive-content' ) . __( 'This content will be displayed at the bottom of the post list.', 'snow-monkey-archive-content' ),
			'priority'    => 11,
			'default'     => 0,
			'choices'     => $choices,
		)
	);
}

if ( ! is_customize_preview() ) {
	return;
}

$panel = Framework::get_panel( 'design' );

foreach ( $all_terms as $_term ) {
	$section = Framework::get_section( 'design-' . $_term->taxonomy . '-' . $_term->term_id );
	$control = Framework::get_control( Helper::get_term_meta_name( 'page-id-2', $_term ) );
	$control->join( $section )->join( $panel );
}
