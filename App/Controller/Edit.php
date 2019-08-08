<?php
/**
 * @package snow-monkey-member-post
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Edit {

	public function __construct() {
		add_filter( 'display_post_states', [ $this, '_display_assigned_term' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, '_display_assigned_custom_post_type' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, '_display_assigned_home' ], 10, 2 );
	}

	/**
	 * Add post status comment
	 *
	 * @param array $post_states
	 * @param WP_Post $post
	 * @return array
	 */
	public function _display_assigned_term( $post_states, $post ) {
		if ( ! $this->_is_draft_page( $post->post_type, $post_states ) ) {
			return $post_states;
		}

		$term = Helper::get_term_by_page_id( $post->ID );
		if ( ! $term ) {
			return $post_states;
		}

		$taxonomy = get_taxonomy( $term->taxonomy );

		$post_states[] = sprintf(
			/* translators: %1: Taxonomy label, %2: Term name */
			esc_html__( 'Assigned %1$s: %2$s', 'snow-monkey-archive-content' ),
			$taxonomy->label,
			$term->name
		);

		return $post_states;
	}

	/**
	 * Add post status comment
	 *
	 * @param array $post_states
	 * @param WP_Post $post
	 * @return array
	 */
	public function _display_assigned_custom_post_type( $post_states, $post ) {
		if ( ! $this->_is_draft_page( $post->post_type, $post_states ) ) {
			return $post_states;
		}

		$custom_post_type = Helper::get_custom_post_type_by_page_id( $post->ID );
		if ( ! $custom_post_type ) {
			return $post_states;
		}

		$post_states[] = sprintf(
			/* translators: %1: Taxonomy label, %2: Term name */
			esc_html__( 'Assigned %1$s archive', 'snow-monkey-archive-content' ),
			$custom_post_type->label
		);

		return $post_states;
	}

	/**
	 * Add post status comment
	 *
	 * @param array $post_states
	 * @param WP_Post $post
	 * @return array
	 */
	public function _display_assigned_home( $post_states, $post ) {
		if ( ! $this->_is_draft_page( $post->post_type, $post_states ) ) {
			return $post_states;
		}

		$assigned = Helper::is_home_assigned( $post->ID );
		if ( ! $assigned ) {
			return $post_states;
		}

		$post_states[] = esc_html__( 'Assigned posts page', 'snow-monkey-archive-content' );

		return $post_states;
	}

	protected function _is_draft_page( $post_type, $post_states ) {
		return 'page' === $post_type && array_key_exists( 'draft', $post_states );
	}
}
