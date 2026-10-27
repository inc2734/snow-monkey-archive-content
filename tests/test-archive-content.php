<?php
use Snow_Monkey\Plugin\ArchiveContent\App\Controller\Edit;
use Snow_Monkey\Plugin\ArchiveContent\App\Controller\Front;
use Snow_Monkey\Plugin\ArchiveContent\App\Helper;

class Archive_Content_Test extends WP_UnitTestCase {

	private $term;
	private $page_id;
	private $page_id_2;

	public function set_up() {
		parent::set_up();

		$this->term = get_term( self::factory()->category->create( array( 'name' => 'Archive category' ) ) );
		$this->page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => 'Assigned archive title',
				'post_content' => 'Content before the archive posts.',
			)
		);
		$this->page_id_2 = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_content' => 'Content after the archive posts.',
			)
		);
		self::factory()->post->create( array( 'post_category' => array( $this->term->term_id ) ) );

		set_theme_mod( Helper::get_term_meta_name( 'page-id', $this->term ), $this->page_id );
		set_theme_mod( Helper::get_term_meta_name( 'page-id-2', $this->term ), $this->page_id_2 );
		wp_cache_delete( 'snow-monkey-archive-content', 'terms' );
		$this->go_to( get_category_link( $this->term ) );
	}

	public function test_assigned_content_renders_and_preserves_archive_query() {
		$front = new Front();
		$query = $GLOBALS['wp_query'];
		$post = $GLOBALS['post'];

		ob_start();
		$front->_add_content();
		$before = ob_get_clean();
		ob_start();
		$front->_add_content_2();
		$after = ob_get_clean();

		$this->assertStringContainsString( 'id="snow-monkey-archive-content-body"', $before );
		$this->assertStringContainsString( 'Content before the archive posts.', $before );
		$this->assertStringContainsString( 'id="snow-monkey-archive-content-body-2"', $after );
		$this->assertStringContainsString( 'Content after the archive posts.', $after );
		$this->assertSame( $query, $GLOBALS['wp_query'] );
		$this->assertEquals( $post, $GLOBALS['post'] );
		$this->assertTrue( is_category( $this->term->term_id ) );
	}

	public function test_title_replacement_respects_setting() {
		$front = new Front();
		$html = '<h1 class="c-entry__title">Archive category</h1>';
		$this->assertSame( $html, $front->_replace_page_title( $html ) );

		set_theme_mod( Helper::get_term_meta_name( 'replace-title', $this->term ), true );
		$this->assertSame(
			'<h1 class="c-entry__title">Assigned archive title</h1>',
			$front->_replace_page_title( $html )
		);
	}

	public function test_published_page_is_not_rendered_as_archive_content() {
		wp_update_post( array( 'ID' => $this->page_id, 'post_status' => 'publish' ) );
		$front = new Front();
		ob_start();
		$front->_add_content();
		$content = ob_get_clean();

		$this->assertSame( '', $content );
	}

	public function test_page_list_displays_assigned_category_for_both_slots() {
		$edit = new Edit();
		foreach ( array( $this->page_id, $this->page_id_2 ) as $page_id ) {
			$states = $edit->_display_assigned_term( array( 'draft' => 'Draft' ), get_post( $page_id ) );
			$this->assertContains( 'Assigned Categories: Archive category', $states );
		}

		$this->assertSame( array(), $edit->_display_assigned_term( array(), get_post( $this->page_id ) ) );
	}
}
