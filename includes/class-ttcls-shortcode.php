<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Shortcode {

	public static function init() {
		add_shortcode( 'ttc_link_shortener', [ __CLASS__, 'render' ] );
	}

	public static function render( $atts = [] ) {
		if ( ! is_user_logged_in() ) {
			ob_start();
			include TTCLS_PATH . 'templates/view-login.php';
			return ob_get_clean();
		}

		if ( ! current_user_can( 'ttcls_manage_links' ) ) {
			return '<p>' . esc_html__( 'You do not have permission to access this page.', 'ttc-link-shortener' ) . '</p>';
		}

		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'dashboard';
		$view = in_array( $view, [ 'dashboard', 'all-links' ], true ) ? $view : 'dashboard';

		$user_id = current_user_can( 'manage_options' ) ? null : get_current_user_id();

		$ctx = [
			'view'         => $view,
			'user_id'      => $user_id,
			'total_links'  => TTCLS_DB::total_links( $user_id ),
			'total_clicks' => TTCLS_DB::total_clicks( $user_id ),
			'recent'       => TTCLS_DB::get_recent( 5, $user_id ),
			'page_url'     => home_url( '/' . TTCLS_PAGE_SLUG . '/' ),
		];

		ob_start();
		include TTCLS_PATH . 'templates/dashboard-layout.php';
		return ob_get_clean();
	}
}
