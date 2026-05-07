<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Activator {

	public static function activate() {
		TTCLS_DB::create_table();
		TTCLS_Roles::add_role();
		self::create_dashboard_page();
		flush_rewrite_rules();
	}

	private static function create_dashboard_page() {
		$existing = get_page_by_path( TTCLS_PAGE_SLUG );
		if ( $existing ) {
			return;
		}
		wp_insert_post( [
			'post_title'   => 'Link ShortURL',
			'post_name'    => TTCLS_PAGE_SLUG,
			'post_content' => '[ttc_link_shortener]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		] );
	}
}
