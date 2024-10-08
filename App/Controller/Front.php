<?php
/**
 * @package snow-monkey-archive-content
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\ArchiveContent\App\Controller;

use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Front {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action(
			'wp',
			function () {
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

				add_filter(
					'snow_monkey_template_part_render_template-parts/archive/entry/header/header',
					array( $this, '_replace_page_title' )
				);

				add_filter(
					'snow_monkey_template_part_render_template-parts/common/page-header',
					array( $this, '_replace_page_title' )
				);

				add_action(
					'snow_monkey_before_archive_entry_content',
					array( $this, '_add_content' )
				);

				add_action(
					'snow_monkey_after_archive_entry_content',
					array( $this, '_add_content_2' )
				);

				add_filter( 'document_title_parts', array( $this, '_replace_document_title' ) );

				add_action( 'wp_enqueue_scripts', array( $this, '_wp_enqueue_scripts' ), 100 );
				add_action( 'wp_head', array( $this, '_hide_page_title' ) );
				add_action( 'wp_head', array( $this, '_remove_top_margin' ) );
				add_action( 'wp_head', array( $this, '_remove_term_description' ) );
				add_action( 'admin_bar_menu', array( $this, '_admin_bar_menu' ), 100 );

				add_filter( 'inc2734_wp_ogp_title', array( $this, '_ogp_title' ), 11 );
				add_filter( 'inc2734_wp_ogp_description', array( $this, '_ogp_description' ), 11 );
				add_filter( 'inc2734_wp_ogp_image', array( $this, '_ogp_image' ), 11 );
				add_filter( 'inc2734_wp_seo_description', array( $this, '_seo_description' ), 11 );
				add_filter( 'inc2734_wp_seo_thumbnail', array( $this, '_seo_thumbnail' ), 11 );
			}
		);
	}

	/**
	 * Replace category archive page title.
	 *
	 * @param string $html The post header.
	 * @return string
	 */
	public function _replace_page_title( $html ) {
		if ( is_category() || is_tag() || is_tax() ) {
			$term          = get_queried_object();
			$replace_title = get_theme_mod( Helper::get_term_meta_name( 'replace-title', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$replace_title    = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'replace-title', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user          = get_queried_object();
			$replace_title = get_theme_mod( Helper::get_author_meta_name( 'replace-title', $user ) );
		} elseif ( is_home() ) {
			$replace_title = get_theme_mod( Helper::get_home_meta_name( 'replace-title' ) );
		}

		if ( ! $replace_title ) {
			return $html;
		}

		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $html;
		}

		return preg_replace(
			array(
				'|(<h1 class="c-entry__title">).*?(</h1>)|ms',
				'|(<h1 class="c-page-header__title">).*?(</h1>)|ms',
			),
			'$1' . get_the_title( $page_id ) . '$2',
			$html
		);
	}

	/**
	 * Add category archive page content to before posts list.
	 */
	public function _add_content() {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return;
		}

		global $post;
		$_post = empty( $post ) ? $post : clone $post;

		// phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
		query_posts(
			array(
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			)
		);
		?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<div class="post-<?php echo esc_attr( $page_id ); ?> snow-monkey-archive-content-body" id="snow-monkey-archive-content-body">
				<div class="c-entry__content p-entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		<?php endwhile; ?>
		<?php
		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		$post = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Add category archive page content to after posts list.
	 */
	public function _add_content_2() {
		$page_id = $this->_get_assigned_page_id_2();
		if ( ! $page_id ) {
			return;
		}

		global $post;
		$_post = empty( $post ) ? $post : clone $post;

		// phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
		query_posts(
			array(
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			)
		);
		?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<div class="post-<?php echo esc_attr( $page_id ); ?> snow-monkey-archive-content-body" id="snow-monkey-archive-content-body-2">
				<div class="c-entry__content p-entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		<?php endwhile; ?>
		<?php
		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		$post = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Replace category archive page title tag.
	 *
	 * @param array $title The post title.
	 * @return array
	 */
	public function _replace_document_title( $title ) {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $title;
		}

		$title['title'] = get_the_title( $page_id );
		return $title;
	}

	/**
	 * Enqueue assets.
	 */
	public function _wp_enqueue_scripts() {
		wp_enqueue_style(
			'snow-monkey-archive-content',
			SNOW_MONKEY_ARCHIVE_CONTENT_URL . '/dist/css/app.css',
			array( \Framework\Helper::get_main_style_handle() ),
			filemtime( SNOW_MONKEY_ARCHIVE_CONTENT_PATH . '/dist/css/app.css' )
		);
	}

	/**
	 * Hide page title.
	 */
	public function _hide_page_title() {
		if ( is_category() || is_tag() || is_tax() ) {
			$term          = get_queried_object();
			$display_title = get_theme_mod( Helper::get_term_meta_name( 'display-title', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$display_title    = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'display-title', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user          = get_queried_object();
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
	 * Remove top margin of the content.
	 */
	public function _remove_top_margin() {
		if ( is_category() || is_tag() || is_tax() ) {
			$term              = get_queried_object();
			$remove_top_margin = get_theme_mod( Helper::get_term_meta_name( 'remove-top-margin', $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object  = get_queried_object();
			$remove_top_margin = get_theme_mod( Helper::get_custom_post_archive_meta_name( 'remove-top-margin', $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user              = get_queried_object();
			$remove_top_margin = get_theme_mod( Helper::get_author_meta_name( 'remove-top-margin', $user ) );
		} elseif ( is_home() ) {
			$remove_top_margin = get_theme_mod( Helper::get_home_meta_name( 'remove-top-margin' ) );
		}

		if ( ! $remove_top_margin ) {
			return;
		}
		?>
		<style id="snow-monkey-archive-content-style-remove-top-margin">
		.l-contents__inner { margin-top: 0 !important; padding-top: 0 !important; }
		</style>
		<?php
	}

	/**
	 * Remove term-description.
	 */
	public function _remove_term_description() {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return;
		}
		?>
		<style id="snow-monkey-archive-content-style-remove-term-description">
		.p-term-description { display: none !important; }
		</style>
		<?php
	}

	/**
	 * Add edit page link to adminbar.
	 *
	 * @param WP_Admin_Bar $wp_adminbar WP_Admin_Bar object.
	 */
	public function _admin_bar_menu( $wp_adminbar ) {
		$page_id = $this->_get_assigned_page_id();
		if ( $page_id ) {
			$wp_adminbar->add_node(
				array(
					'id'    => 'snow-monkey-archive-content-edit-page',
					'title' => __( 'Edit the page used as content', 'snow-monkey-archive-content' ),
					'href'  => get_edit_post_link( $page_id, 'url' ),
				)
			);
		}

		$page_id_2 = $this->_get_assigned_page_id_2();
		if ( $page_id_2 ) {
			$wp_adminbar->add_node(
				array(
					'id'    => 'snow-monkey-archive-content-edit-page-2',
					'title' => __( 'Edit the page used as content', 'snow-monkey-archive-content' ) . '2',
					'href'  => get_edit_post_link( $page_id_2, 'url' ),
				)
			);
		}
	}

	/**
	 * Assign og:title.
	 *
	 * @param string $title og:title.
	 * @return string
	 */
	public function _ogp_title( $title ) {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $title;
		}

		return get_the_title( $page_id );
	}

	/**
	 * Assign og:description.
	 *
	 * @param string $description og:description.
	 * @return string
	 */
	public function _ogp_description( $description ) {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $description;
		}

		if ( is_singular() ) {
			return $description;
		}

		global $post;
		$_post = empty( $post ) ? $post : clone $post;

		// phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
		query_posts(
			array(
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			)
		);

		while ( have_posts() ) {
			the_post();
			$ogp              = new \Inc2734\WP_OGP\Bootstrap();
			$page_description = $ogp->get_description();
			if ( $page_description && get_bloginfo( 'description' ) !== $page_description ) {
				$description = $page_description;
			}
		}

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		$post = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $description;
	}

	/**
	 * Assign og:image
	 *
	 * @param string $image og:image.
	 * @return string
	 */
	public function _ogp_image( $image ) {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $image;
		}

		global $post;
		$_post = empty( $post ) ? $post : clone $post;

		// phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
		query_posts(
			array(
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			)
		);

		while ( have_posts() ) {
			the_post();
			$ogp   = new \Inc2734\WP_OGP\Bootstrap();
			$image = $ogp->get_image();
		}

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		$post = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $image;
	}

	/**
	 * Assign meta description.
	 *
	 * @param string $description The description.
	 * @return string
	 */
	public function _seo_description( $description ) {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $description;
		}

		if ( is_singular() ) {
			return $description;
		}

		global $post;
		$_post = empty( $post ) ? $post : clone $post;

		// phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
		query_posts(
			array(
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			)
		);

		while ( have_posts() ) {
			the_post();
			$page_description = \Inc2734\WP_SEO\Helper::get_the_description( $page_id );
			if ( $page_description && get_bloginfo( 'description' ) !== $page_description ) {
				$description = $page_description;
			}
		}

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		$post = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $description;
	}

	/**
	 * Assign meta thumbnail.
	 *
	 * @param string $thumbnail The thumbnail.
	 * @return string
	 */
	public function _seo_thumbnail( $thumbnail ) {
		$page_id = $this->_get_assigned_page_id();
		if ( ! $page_id ) {
			return $thumbnail;
		}

		global $post;
		$_post = empty( $post ) ? $post : clone $post;

		// phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
		query_posts(
			array(
				'page_id'     => $page_id,
				'post_status' => get_post_status( $page_id ),
			)
		);

		while ( have_posts() ) {
			the_post();
			$thumbnail = \Inc2734\WP_SEO\Helper::get_the_thumbnail( $page_id );
		}

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		$post = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $thumbnail;
	}

	/**
	 * Return assigned page id.
	 *
	 * @return int|false
	 */
	protected function _get_assigned_page_id() {
		return $this->_get_assigned_page_id_with_id( 'page-id' );
	}

	/**
	 * Return assigned page id 2.
	 *
	 * @return int|false
	 */
	protected function _get_assigned_page_id_2() {
		return $this->_get_assigned_page_id_with_id( 'page-id-2' );
	}

	/**
	 * Return assigned page id 1/2.
	 *
	 * @param string $meta_id Meta name. page-id or page-id-2.
	 * @return int|false
	 */
	protected function _get_assigned_page_id_with_id( $meta_id ) {
		if ( is_category() || is_tag() || is_tax() ) {
			$term    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_term_meta_name( $meta_id, $term ) );
		} elseif ( is_post_type_archive() ) {
			$post_type_object = get_queried_object();
			$page_id          = get_theme_mod( Helper::get_custom_post_archive_meta_name( $meta_id, $post_type_object->name ) );
		} elseif ( is_author() ) {
			$user    = get_queried_object();
			$page_id = get_theme_mod( Helper::get_author_meta_name( $meta_id, $user ) );
		} elseif ( is_home() ) {
			$page_id = get_theme_mod( Helper::get_home_meta_name( $meta_id ) );
		}

		if ( empty( $page_id ) || 'draft' !== get_post_status( $page_id ) ) {
			return false;
		}

		return $page_id;
	}
}
