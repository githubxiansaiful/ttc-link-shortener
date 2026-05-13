<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		self::maybe_upgrade();
		TTCLS_Rewrite::init();
		TTCLS_Admin_Bar::init();
		TTCLS_Template::init();
		TTCLS_Assets::init();
		TTCLS_Ajax::init();
		TTCLS_Shortcode::init();
	}

	private static function maybe_upgrade() {
		$stored = get_option( 'ttcls_db_version' );
		if ( $stored === TTCLS_VERSION ) {
			return;
		}
		TTCLS_DB::create_table();
		update_option( 'ttcls_db_version', TTCLS_VERSION, false );
	}
}
