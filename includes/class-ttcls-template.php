<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Template {

	public static function init() {
		add_filter( 'template_include',        [ __CLASS__, 'use_standalone' ], 99 );
		add_filter( 'auth_cookie_expiration',  [ __CLASS__, 'extend_session' ], 10, 3 );
		add_filter( 'login_redirect',          [ __CLASS__, 'login_redirect' ], 10, 3 );
		add_filter( 'body_class',              [ __CLASS__, 'body_class' ] );
	}

	public static function use_standalone( $template ) {
		if ( ! is_page() ) {
			return $template;
		}
		$post = get_post();
		if ( ! $post || $post->post_type !== 'page' ) {
			return $template;
		}
		if ( $post->post_name !== TTCLS_PAGE_SLUG ) {
			return $template;
		}
		return TTCLS_PATH . 'templates/page-standalone.php';
	}

	public static function extend_session( $expiration, $user_id, $remember ) {
		$user = get_userdata( $user_id );
		if ( $user && in_array( TTCLS_ROLE, (array) $user->roles, true ) ) {
			return 90 * DAY_IN_SECONDS;
		}
		return $expiration;
	}

	public static function login_redirect( $redirect_to, $requested, $user ) {
		if ( ! is_wp_error( $user ) && isset( $user->roles ) && in_array( TTCLS_ROLE, (array) $user->roles, true ) ) {
			return home_url( '/' . TTCLS_PAGE_SLUG . '/' );
		}
		return $redirect_to;
	}

	public static function body_class( $classes ) {
		if ( is_page() ) {
			$post = get_post();
			if ( $post && $post->post_name === TTCLS_PAGE_SLUG ) {
				$classes[] = 'ttcls-standalone';
			}
		}
		return $classes;
	}
}
