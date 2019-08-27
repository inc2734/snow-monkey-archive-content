<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Front {

	public function __construct() {
		add_action(
			'wp',
			function() {
				if ( is_search() ) {
					return;
				}

				if ( ! is_category() && ! is_tag() && ! is_tax() && ! is_post_type_archive() && ! is_author() && ! is_home() ) {
					return;
				}

				$apply_paged = apply_filters( 'snow_monkey_archive_content_apply_paged', ! is_paged() );
				if ( ! $apply_paged ) {
					return;
				}

				add_filter( 'snow_monkey_template_part_render', [ $this, '_replace_content' ], 10, 2 );
				add_filter( 'document_title_parts', [ $this, '_replace_title' ] );

				add_action( 'wp_enqueue_scripts', [ $this, '_wp_enqueue_scripts' ], 100 );
				add_action( 'wp_head', [ $this, '_hide_page_title' ] );
				add_action( 'wp_head', [ $this, '_remove_top_margin' ] );
				add_action( 'wp_head', [ $this, '_remove_term_description' ] );
				add_action( 'admin_bar_menu', [ $this, '_admin_bar_menu' ], 100 );
			}
		);
	}

	/**
	 * Replace category archive page content
	 *
	 * @param string $html
	 * @param string $slug
	 * @return string
	 */
	public function _replace_content( $html, $slug ) {
		if ( 'templates/view/archive' !== $slug && 'templates/view/home' !== $slug ) {
			return $html;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$page_id = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_author_meta_name( 'page-id', $user ) );
		} elseif ( is_home() ) {
			$page_id = get_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
		}

		if ( ! $page_id || 'draft' !== get_post_status( $page_id ) ) {
			return $html;
		}

		$query = new \WP_Query(
			[
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			]
		);

		if ( ! $query->have_posts() ) {
			return $html;
		}

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		global $wp_query;
		$the_is_singular = $wp_query->is_singular();

		$content = '';
		while ( $query->have_posts() ) {
			$query->the_post();
			$wp_query->is_singular = true;
			ob_start();
			the_content();
			$content = ob_get_clean();
			$wp_query->is_singular = $the_is_singular;
		}
		wp_reset_postdata();
		// phpcs:enable

		return str_replace(
			'<div class="c-entry__body">',
			'<div class="c-entry__body">
				<div class="post-' . esc_attr( $page_id ) . '" id="snow-monkey-archive-content-body">
					<div class="c-entry__content p-entry-content">' . $content . '</div>
				</div>',
			$html
		);
	}

	/**
	 * Replace category archive page title tag
	 *
	 * @param array $title
	 * @return array
	 */
	public function _replace_title( $title ) {
		if ( is_category() || is_tag() || is_tax() ) {
			$term    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$page_id = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_author_meta_name( 'page-id', $user ) );
		} elseif ( is_home() ) {
			$page_id = get_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
		}

		if ( ! $page_id || 'draft' !== get_post_status( $page_id ) ) {
			return $title;
		}

		$title['title'] = get_the_title( $page_id );
		return $title;
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function _wp_enqueue_scripts() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style(
			'snow-monkey-archive-content',
			SNOW_MONKEY_ARCHIVE_CONTENT_URL . '/dist/css/app.min.css',
			[],
			filemtime( SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/dist/css/app.min.css' )
		);
	}

	/**
	 * Hide page title
	 *
	 * @return void
	 */
	public function _hide_page_title() {
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$display_title = get_theme_mod( Helper::get_term_meta_name( 'display-title', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$display_title = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'display-title', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user = get_queried_object();
			$display_title = get_theme_mod( Helper::get_author_meta_name( 'display-title', $user ) );
		} elseif ( is_home() ) {
			$display_title = get_theme_mod( Helper::get_home_meta_name( 'display-title' ) );
		}

		if ( $display_title ) {
			return;
		}
		?>
		<style id="snow-monkey-archive-content-style-display-title">
		.c-entry__header { display: none !important; }
		</style>
		<?php
	}

	/**
	 * Remove top margin of the content
	 *
	 * @return void
	 */
	public function _remove_top_margin() {
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$remove_top_margin = get_theme_mod( Helper::get_term_meta_name( 'remove-top-margin', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$remove_top_margin = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'remove-top-margin', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user = get_queried_object();
			$remove_top_margin = get_theme_mod( Helper::get_author_meta_name( 'remove-top-margin', $user ) );
		} elseif ( is_home() ) {
			$remove_top_margin = get_theme_mod( Helper::get_home_meta_name( 'remove-top-margin' ) );
		}

		if ( ! $remove_top_margin ) {
			return;
		}
		?>
		<style id="snow-monkey-archive-content-style-remove-top-margin">
		.l-contents__inner, .l-contents__main > .c-entry { margin-top: 0 !important; }
		</style>
		<?php
	}

	/**
	 * Remove term-description
	 *
	 * @return void
	 */
	public function _remove_term_description() {
		if ( is_category() || is_tag() || is_tax() ) {
			$term    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$page_id = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_author_meta_name( 'page-id', $user ) );
		} elseif ( is_home() ) {
			$page_id = get_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
		}

		if ( ! $page_id || 'draft' !== get_post_status( $page_id ) ) {
			return;
		}
		?>
		<style id="snow-monkey-archive-content-style-remove-term-description">
		.p-term-description { display: none !important; }
		</style>
		<?php
	}

	/**
	 * Add edit page link to adminbar
	 *
	 * @param WP_Admin_Bar $wp_adminbar
	 * @return void
	 */
	public function _admin_bar_menu( $wp_adminbar ) {
		if ( is_category() || is_tag() || is_tax() ) {
			$term    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$page_id = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'page-id', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_author_meta_name( 'page-id', $user ) );
		} elseif ( is_home() ) {
			$page_id = get_theme_mod( Helper::get_home_meta_name( 'page-id' ) );
		}

		if ( ! $page_id || 'draft' !== get_post_status( $page_id ) ) {
			return;
		}

		$wp_adminbar->add_node(
			[
				'id'    => 'snow-monkey-archive-content-edit-page',
				'title' => __( 'Edit the page used as content', 'snow-monkey-archive-content' ),
				'href'  => get_edit_post_link( $page_id, 'url' ),
			]
		);
	}
}
