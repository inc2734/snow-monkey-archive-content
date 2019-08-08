<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App;

use Framework\Helper as Snow_Monkey_Helper;

class Helper {

	/**
	 * Load files
	 *
	 * @param string $directory
	 */
	public static function load( $directory ) {
		foreach ( glob( untrailingslashit( $directory ) . '/*' ) as $file ) {
			if ( is_dir( $file ) ) {
				static::load( $file );
			} else {
				require_once( $file );
			}
		}
	}

	/**
	 * Return draft root pages
	 *
	 * @return array
	 */
	public static function get_draft_pages() {
		$pages = wp_cache_get( 'snow-monkey-archive-content', 'draft-pages' );
		if ( is_array( $pages ) ) {
			return $pages;
		}

		$pages = get_pages(
			[
				'parent'      => 0,
				'post_status' => 'draft',
			]
		);

		wp_cache_set( 'snow-monkey-archive-content', $pages, 'draft-pages' );
		return $pages;
	}

	/**
	 * Return all terms
	 *
	 * @param string $taxonomy
	 * @return array
	 */
	public static function get_terms( $taxonomy ) {
		return Snow_Monkey_Helper::get_terms( $taxonomy );
	}

	/**
	 * Return all custom post types
	 *
	 * @return array
	 */
	public static function get_custom_post_types() {
		return Snow_Monkey_Helper::get_custom_post_types();
	}

	/**
	 * Return all taxonomies
	 *
	 * @return array
	 */
	public static function get_taxonomies() {
		return Snow_Monkey_Helper::get_taxonomies();
	}

	/**
	 * Return meta name of the posts page
	 *
	 * @param string $key
	 * @return string
	 */
	public static function get_home_meta_name( $key ) {
		return 'snow-monkey-archive-content/home/' . $key;
	}

	/**
	 * Return meta name of the custom post type
	 *
	 * @param string $key
	 * @param string $post_type
	 * @return string
	 */
	public static function get_custom_post_archive_meta_name( $key, $post_type ) {
		return 'snow-monkey-archive-content/custom-post-types/' . $post_type . '/' . $key;
	}

	/**
	 * Return meta name of the term
	 *
	 * @param string $key
	 * @param WP_Term $term
	 * @return string
	 */
	public static function get_term_meta_name( $key, $term ) {
		return 'snow-monkey-archive-content/term/' . $term->taxonomy . '/' . $term->term_id . '/' . $key;
	}

	/**
	 * Return array of assigned terms
	 *
	 * @return array
	 */
	protected static function get_assigned_terms() {
		$terms = wp_cache_get( 'snow-monkey-archive-content', 'terms' );
		if ( false !== $terms ) {
			return $terms;
		}

		$theme_mods = get_theme_mods();
		$terms = [];

		foreach ( $theme_mods as $key => $value ) {
			if ( ! preg_match( '/^snow-monkey-archive-content/term/(.+?)/(\d+?)/page-id$/', $key, $matches ) ) {
				continue;
			}

			$term = get_term( $matches[2], $matches[1] );
			if ( is_wp_error( $term ) ) {
				continue;
			}

			$terms[ $value ] = $term;
		}

		wp_cache_set( 'snow-monkey-archive-content', $terms, 'terms' );
		return $terms;
	}

	/**
	 * Return assigned term
	 *
	 * @param int $page_id
	 * @return null|WP_Term
	 */
	public static function get_term_by_page_id( $page_id ) {
		$assigned_terms = static::get_assigned_terms();
		if ( isset( $assigned_terms[ $page_id ] ) ) {
			return $assigned_terms[ $page_id ];
		}
	}

	/**
	 * Return array of assigned custom post types
	 *
	 * @return array
	 */
	protected static function get_assigned_custom_post_types() {
		$custom_post_types = wp_cache_get( 'snow-monkey-archive-content', 'custom-post-types' );
		if ( false !== $custom_post_types ) {
			return $custom_post_types;
		}

		$theme_mods = get_theme_mods();
		$custom_post_types = [];

		foreach ( $theme_mods as $key => $value ) {
			if ( ! preg_match( '/^snow-monkey-archive-content/custom-post-types/(.+)/page-id$/', $key, $matches ) ) {
				continue;
			}

			$post_type_object = get_post_type_object( $matches[1] );
			if ( ! $post_type_object ) {
				continue;
			}

			$custom_post_types[ $value ] = $post_type_object;
		}

		wp_cache_set( 'snow-monkey-archive-content', $custom_post_types, 'custom-post-types' );
		return $custom_post_types;
	}

	/**
	 * Return assigned custom post type
	 *
	 * @param int $page_id
	 * @return null|object
	 */
	public static function get_custom_post_type_by_page_id( $page_id ) {
		$assigned_custom_post_types = static::get_assigned_custom_post_types();
		if ( isset( $assigned_custom_post_types[ $page_id ] ) ) {
			return $assigned_custom_post_types[ $page_id ];
		}
	}

	/**
	 * Return true when posts page assigned
	 *
	 * @param int $page_id
	 * @return null|object
	 */
	public static function is_home_assigned( $page_id ) {
		return (int) get_theme_mod( static::get_home_meta_name( 'page-id' ) ) === (int) $page_id;
	}
}
