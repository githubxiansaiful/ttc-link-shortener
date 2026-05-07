<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Admin_Bar {

	public static function init() {
		add_action( 'after_setup_theme', [ __CLASS__, 'maybe_hide_bar' ] );
		add_action( 'admin_init', [ __CLASS__, 'block_wp_admin' ] );
	}

	public static function maybe_hide_bar() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user = wp_get_current_user();
		if ( in_array( TTCLS_ROLE, (array) $user->roles, true ) ) {
			show_admin_bar( false );
		}
	}

	public static function block_wp_admin() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		$user = wp_get_current_user();
		if ( ! in_array( TTCLS_ROLE, (array) $user->roles, true ) ) {
			return;
		}
		wp_safe_redirect( home_url( '/' . TTCLS_PAGE_SLUG . '/' ) );
		exit;
	}
}
