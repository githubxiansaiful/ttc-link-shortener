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
		} while ( TTCLS_DB::slug_exists( $slug ) );
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
