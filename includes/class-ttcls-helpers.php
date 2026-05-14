<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Helpers {

	public static function generate_slug( $length = 6 ) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$max   = strlen( $chars ) - 1;
		$attempts = 0;
		do {
			$slug = '';
			for ( $i = 0; $i < $length; $i++ ) {
				$slug .= $chars[ random_int( 0, $max ) ];
			}
			$attempts++;
			if ( $attempts > 20 ) {
				$length++;
				$attempts = 0;
			}
		} while ( TTCLS_DB::slug_exists( $slug ) || self::is_reserved_slug( $slug ) );
		return $slug;
	}

	const SLUG_MIN = 3;
	const SLUG_MAX = 64;

	public static function reserved_slugs() {
		$base = [
			TTCLS_PAGE_SLUG,
			'wp-admin', 'wp-login', 'wp-content', 'wp-includes', 'wp-json',
			'admin', 'login', 'logout', 'register', 'feed', 'rss', 'comments',
			'sitemap', 'sitemap.xml', 'sitemap_index.xml', 'robots.txt',
			'favicon.ico', 'xmlrpc.php', 'api', 'cron',
		];
		return apply_filters( 'ttcls_reserved_slugs', $base );
	}

	public static function is_reserved_slug( $slug ) {
		$slug = strtolower( $slug );
		if ( in_array( $slug, array_map( 'strtolower', self::reserved_slugs() ), true ) ) {
			return true;
		}
		if ( get_page_by_path( $slug ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Validate a user-supplied custom slug.
	 * Returns sanitized slug string on success, or WP_Error.
	 */
	public static function validate_custom_slug( $slug, $exclude_id = 0 ) {
		$slug = trim( (string) $slug );
		if ( '' === $slug ) {
			return new WP_Error( 'ttcls_slug_empty', __( 'Custom slug is empty.', 'ttc-link-shortener' ) );
		}
		if ( ! preg_match( '/^[A-Za-z0-9](?:[A-Za-z0-9_-]*[A-Za-z0-9])?$/', $slug ) ) {
			return new WP_Error( 'ttcls_slug_chars', __( 'Slug can contain letters, numbers, hyphens, underscores. Must start and end with a letter or number.', 'ttc-link-shortener' ) );
		}
		$len = strlen( $slug );
		if ( $len < self::SLUG_MIN || $len > self::SLUG_MAX ) {
			/* translators: 1: min length, 2: max length */
			return new WP_Error( 'ttcls_slug_len', sprintf( __( 'Slug must be %1$d–%2$d characters.', 'ttc-link-shortener' ), self::SLUG_MIN, self::SLUG_MAX ) );
		}
		if ( self::is_reserved_slug( $slug ) ) {
			return new WP_Error( 'ttcls_slug_reserved', __( 'That slug is reserved. Please pick another.', 'ttc-link-shortener' ) );
		}
		if ( TTCLS_DB::slug_exists( $slug, $exclude_id ) ) {
			return new WP_Error( 'ttcls_slug_taken', __( 'That slug is already taken. Please pick another.', 'ttc-link-shortener' ) );
		}
		return $slug;
	}

	public static function validate_url( $url ) {
		$url = esc_url_raw( trim( (string) $url ) );
		if ( '' === $url ) {
			return false;
		}
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
			return false;
		}
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}
		return $url;
	}

	public static function short_url( $slug ) {
		return home_url( '/' . $slug );
	}

	public static function format_date( $datetime ) {
		if ( empty( $datetime ) || '0000-00-00 00:00:00' === $datetime ) {
			return '—';
		}
		$ts = strtotime( $datetime . ' UTC' );
		if ( ! $ts ) {
			return esc_html( $datetime );
		}
		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts );
	}
}
