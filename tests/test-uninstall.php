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

		$terms = Helper::get_terms( 'category' );
		$pages = Helper::get_draft_pages();

		foreach ( $terms as $term ) {
			set_theme_mod( Helper::get_term_meta_name( 'page-id', $term ), $page_id );
		}

		\Snow_Monkey\Plugin\ArchiveContent\uninstall_callback();

		foreach ( $terms as $term ) {
			$this->assertFalse( get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) ) );
		}
	}

	/**
	 * @test
	 */
	public function custom_taxonomy() {
		register_taxonomy( 'wptests_tax', 'post' );

		$term_ids     = $this->factory()->term->create_many( 5, [ 'taxonomy' => 'wptests_tax' ] );
		$post_id      = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$page_id      = $this->factory()->post->create( [ 'post_type' => 'page' ] );

		wp_set_object_terms( $post_id, $term_ids, 'wptests_tax' );

		$terms      = [];
		$taxonomies = Helper::get_taxonomies();
		foreach ( $taxonomies as $_taxonomy ) {
			$terms = array_merge( $terms, Helper::get_terms( $_taxonomy ) );
		}

		$pages = Helper::get_draft_pages();

		foreach ( $terms as $term ) {
			set_theme_mod( Helper::get_term_meta_name( 'page-id', $term ), $page_id );
		}

		\Snow_Monkey\Plugin\ArchiveContent\uninstall_callback();

		foreach ( $terms as $term ) {
			$this->assertFalse( get_theme_mod( Helper::get_term_meta_name( 'page-id', $term ) ) );
		}
	}
}
