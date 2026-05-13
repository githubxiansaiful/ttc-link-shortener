<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Rewrite {

	public static function init() {
		add_action( 'template_redirect', [ __CLASS__, 'maybe_redirect' ], 1 );
	}

	public static function maybe_redirect() {
		if ( is_admin() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

		if ( '' === $path ) {
			return;
		}

		// Strip site subdirectory (e.g. /Shorter/abc123 → abc123)
		$home_path = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( '' !== $home_path && 0 === strpos( $path, $home_path . '/' ) ) {
			$path = substr( $path, strlen( $home_path ) + 1 );
		} elseif ( $path === $home_path ) {
			return;
		}

		if ( ! preg_match( '/^[A-Za-z0-9][A-Za-z0-9_-]{1,62}[A-Za-z0-9]$/', $path ) ) {
			return;
		}

		// Real page wins
		if ( get_page_by_path( $path ) ) {
			return;
		}

		$row = TTCLS_DB::get_by_slug( $path );
		if ( ! $row || (int) $row->status !== 1 ) {
			return;
		}

		TTCLS_DB::increment_click( $path );

		$dest = esc_url_raw( $row->destination_url );
		if ( ! $dest ) {
			return;
		}

		nocache_headers();
		wp_redirect( $dest, 301 );
		exit;
	}
}
