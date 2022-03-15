<?php
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Uninstall_Test extends WP_UnitTestCase {

	/**
	 * @test
	 */
	public function category() {
		$category_ids = $this->factory()->category->create_many( 5 );
		$post_id      = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$page_id      = $this->factory()->post->create( [ 'post_type' => 'page' ] );

		wp_set_object_terms( $post_id, $category_ids, 'category' );

		$terms = Helper::get_terms(
			[
				'taxonomy'   => 'category',
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			set_theme_mod( Helper::get_term_meta_name( 'page-id', $term ), $page_id );
		}

		\Snow_Monkey\Plugin\ArchiveContent\uninstall_callback();

		foreach ( $terms as $term ) {
			$this->assertFalse( boolval( get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) ) ) );
		}
	}

	/**
	 * @test
	 */
	public function custom_taxonomy() {
		register_taxonomy( 'wptests_tax', 'post' );

		$term_ids = $this->factory()->term->create_many( 5, [ 'taxonomy' => 'wptests_tax' ] );
		$post_id  = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$page_id  = $this->factory()->post->create( [ 'post_type' => 'page' ] );

		wp_set_object_terms( $post_id, $term_ids, 'wptests_tax' );

		$terms      = [];
		$taxonomies = Helper::get_taxonomies();
		if ( $taxonomies ) {
			$terms = Helper::get_terms(
				[
					'taxonomy'   => $taxonomies,
					'hide_empty' => false,
				]
			);
		}

		foreach ( $terms as $term ) {
			set_theme_mod( Helper::get_term_meta_name( 'page-id', $term ), $page_id );
			set_theme_mod( Helper::get_term_meta_name( 'page-id-2', $term ), $page_id );
		}

		\Snow_Monkey\Plugin\ArchiveContent\uninstall_callback();

		foreach ( $terms as $term ) {
			$this->assertFalse( get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) ) );
			$this->assertFalse( get_theme_mod( Helper::get_term_meta_name( 'page-id-2', $term ) ) );
		}
	}
}
