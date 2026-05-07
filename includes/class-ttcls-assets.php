<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Assets {

	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
	}

	public static function enqueue() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}
		if ( ! has_shortcode( $post->post_content, 'ttc_link_shortener' ) ) {
			return;
		}

		wp_enqueue_style(
			'ttcls-inter',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			'ttcls-dashboard',
			TTCLS_URL . 'assets/css/dashboard.css',
			[ 'ttcls-inter' ],
			TTCLS_VERSION
		);

		wp_enqueue_script(
			'ttcls-dashboard',
			TTCLS_URL . 'assets/js/dashboard.js',
			[],
			TTCLS_VERSION,
			true
		);

		wp_localize_script( 'ttcls-dashboard', 'TTCLS', [
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'ttcls_nonce' ),
			'login_nonce' => wp_create_nonce( 'ttcls_login' ),
			'home_url'    => home_url( '/' ),
			'page_url'    => home_url( '/' . TTCLS_PAGE_SLUG . '/' ),
			'i18n'        => [
				'invalid_url'    => __( 'Please enter a valid URL', 'ttc-link-shortener' ),
				'copied'         => __( 'Copied!', 'ttc-link-shortener' ),
				'copy'           => __( 'Copy', 'ttc-link-shortener' ),
				'confirm_del'    => __( 'Delete this link?', 'ttc-link-shortener' ),
				'create_failed'  => __( 'Failed to create short link.', 'ttc-link-shortener' ),
				'delete_failed'  => __( 'Failed to delete link.', 'ttc-link-shortener' ),
				'no_links'       => __( 'No links yet. Create your first short URL above.', 'ttc-link-shortener' ),
				'clicks'         => __( 'clicks', 'ttc-link-shortener' ),
				'login_failed'   => __( 'Sign-in failed. Please try again.', 'ttc-link-shortener' ),
				'signing_in'     => __( 'Signing in…', 'ttc-link-shortener' ),
			],
		] );
	}
}
