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
		TTCLS_Rewrite::init();
		TTCLS_Admin_Bar::init();
		TTCLS_Template::init();
		TTCLS_Assets::init();
		TTCLS_Ajax::init();
		TTCLS_Shortcode::init();
	}
}
