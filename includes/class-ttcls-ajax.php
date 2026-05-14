<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Ajax {

	public static function init() {
		add_action( 'wp_ajax_ttcls_create', [ __CLASS__, 'create' ] );
		add_action( 'wp_ajax_ttcls_update', [ __CLASS__, 'update' ] );
		add_action( 'wp_ajax_ttcls_delete', [ __CLASS__, 'delete' ] );
		add_action( 'wp_ajax_ttcls_list',   [ __CLASS__, 'listing' ] );
		add_action( 'wp_ajax_nopriv_ttcls_login', [ __CLASS__, 'login' ] );
		add_action( 'wp_ajax_ttcls_login',        [ __CLASS__, 'login_already' ] );
	}

	public static function login() {
		check_ajax_referer( 'ttcls_login', 'nonce' );

		$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), false ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';

		if ( '' === $username || '' === $password ) {
			wp_send_json_error( [ 'message' => __( 'Username and password are required.', 'ttc-link-shortener' ) ], 400 );
		}

		$user = wp_signon( [
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => true,
		], is_ssl() );

		if ( is_wp_error( $user ) ) {
			$msg = wp_strip_all_tags( $user->get_error_message() );
			if ( '' === $msg ) {
				$msg = __( 'Invalid credentials.', 'ttc-link-shortener' );
			}
			wp_send_json_error( [ 'message' => $msg ], 401 );
		}

		if ( ! user_can( $user, 'ttcls_manage_links' ) ) {
			wp_logout();
			wp_send_json_error( [ 'message' => __( 'Your account does not have access to the dashboard.', 'ttc-link-shortener' ) ], 403 );
		}

		wp_send_json_success( [
			'redirect' => home_url( '/' . TTCLS_PAGE_SLUG . '/' ),
		] );
	}

	public static function login_already() {
		wp_send_json_success( [
			'redirect' => home_url( '/' . TTCLS_PAGE_SLUG . '/' ),
		] );
	}

	private static function guard() {
		check_ajax_referer( 'ttcls_nonce', 'nonce' );
		if ( ! current_user_can( 'ttcls_manage_links' ) ) {
			wp_send_json_error( [ 'message' => __( 'Forbidden', 'ttc-link-shortener' ) ], 403 );
		}
	}

	public static function create() {
		self::guard();

		$raw = isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '';
		$url = TTCLS_Helpers::validate_url( $raw );
		if ( ! $url ) {
			wp_send_json_error( [ 'message' => __( 'Invalid URL', 'ttc-link-shortener' ) ], 400 );
		}

		$custom_raw = isset( $_POST['slug'] ) ? wp_unslash( $_POST['slug'] ) : '';
		$custom_raw = is_string( $custom_raw ) ? trim( $custom_raw ) : '';

		if ( '' !== $custom_raw ) {
			$validated = TTCLS_Helpers::validate_custom_slug( $custom_raw );
			if ( is_wp_error( $validated ) ) {
				$code   = $validated->get_error_code();
				$status = ( 'ttcls_slug_taken' === $code || 'ttcls_slug_reserved' === $code ) ? 409 : 400;
				wp_send_json_error( [
					'message' => $validated->get_error_message(),
					'field'   => 'slug',
					'code'    => $code,
				], $status );
			}
			$slug = $validated;
		} else {
			$slug = TTCLS_Helpers::generate_slug();
		}
		$id   = TTCLS_DB::insert_link( $slug, $url, get_current_user_id() );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'DB error', 'ttc-link-shortener' ) ], 500 );
		}

		wp_send_json_success( [
			'id'          => $id,
			'slug'        => $slug,
			'short_url'   => TTCLS_Helpers::short_url( $slug ),
			'destination' => $url,
			'clicks'      => 0,
			'created_at'  => gmdate( 'Y-m-d H:i:s' ),
			'totals'      => self::totals(),
		] );
	}

	public static function update() {
		self::guard();

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( $id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid ID', 'ttc-link-shortener' ) ], 400 );
		}

		$row = TTCLS_DB::get_by_id( $id );
		if ( ! $row ) {
			wp_send_json_error( [ 'message' => __( 'Link not found', 'ttc-link-shortener' ) ], 404 );
		}
		if ( ! current_user_can( 'manage_options' ) && (int) $row->created_by !== (int) get_current_user_id() ) {
			wp_send_json_error( [ 'message' => __( 'Forbidden', 'ttc-link-shortener' ) ], 403 );
		}

		$raw = isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '';
		$url = TTCLS_Helpers::validate_url( $raw );
		if ( ! $url ) {
			wp_send_json_error( [ 'message' => __( 'Invalid URL', 'ttc-link-shortener' ) ], 400 );
		}

		$slug_raw = isset( $_POST['slug'] ) ? wp_unslash( $_POST['slug'] ) : '';
		$slug_raw = is_string( $slug_raw ) ? trim( $slug_raw ) : '';
		if ( '' === $slug_raw ) {
			wp_send_json_error( [
				'message' => __( 'Slug is required.', 'ttc-link-shortener' ),
				'field'   => 'slug',
			], 400 );
		}

		if ( $slug_raw === $row->slug ) {
			$slug = $row->slug;
		} else {
			$validated = TTCLS_Helpers::validate_custom_slug( $slug_raw, $id );
			if ( is_wp_error( $validated ) ) {
				$code   = $validated->get_error_code();
				$status = ( 'ttcls_slug_taken' === $code || 'ttcls_slug_reserved' === $code ) ? 409 : 400;
				wp_send_json_error( [
					'message' => $validated->get_error_message(),
					'field'   => 'slug',
					'code'    => $code,
				], $status );
			}
			$slug = $validated;
		}

		$ok = TTCLS_DB::update_link( $id, $slug, $url, get_current_user_id() );
		if ( ! $ok ) {
			wp_send_json_error( [ 'message' => __( 'Update failed', 'ttc-link-shortener' ) ], 500 );
		}

		$fresh = TTCLS_DB::get_by_id( $id );
		wp_send_json_success( [
			'id'              => (int) $fresh->id,
			'slug'            => $fresh->slug,
			'short_url'       => TTCLS_Helpers::short_url( $fresh->slug ),
			'destination'     => $fresh->destination_url,
			'clicks'          => (int) $fresh->clicks,
			'created_at'      => $fresh->created_at,
			'last_clicked_at' => $fresh->last_clicked_at,
			'totals'          => self::totals(),
		] );
	}

	public static function delete() {
		self::guard();

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( $id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid ID', 'ttc-link-shortener' ) ], 400 );
		}

		$ok = TTCLS_DB::delete_link( $id, get_current_user_id() );
		if ( ! $ok ) {
			wp_send_json_error( [ 'message' => __( 'Delete failed', 'ttc-link-shortener' ) ], 403 );
		}

		wp_send_json_success( [
			'id'     => $id,
			'totals' => self::totals(),
		] );
	}

	public static function listing() {
		self::guard();

		$page     = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? min( 100, max( 1, (int) $_POST['per_page'] ) ) : 25;
		$search   = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		$user_id = current_user_can( 'manage_options' ) ? null : get_current_user_id();

		$result = TTCLS_DB::get_all( [
			'user_id'  => $user_id,
			'page'     => $page,
			'per_page' => $per_page,
			'search'   => $search,
		] );

		$rows = [];
		foreach ( $result['rows'] as $row ) {
			$rows[] = [
				'id'              => (int) $row->id,
				'slug'            => $row->slug,
				'short_url'       => TTCLS_Helpers::short_url( $row->slug ),
				'destination'    => $row->destination_url,
				'clicks'          => (int) $row->clicks,
				'created_at'      => $row->created_at,
				'last_clicked_at' => $row->last_clicked_at,
			];
		}

		wp_send_json_success( [
			'rows'     => $rows,
			'total'    => $result['total'],
			'page'     => $result['page'],
			'pages'    => $result['pages'],
			'per_page' => $result['per_page'],
		] );
	}

	private static function totals() {
		$user_id = current_user_can( 'manage_options' ) ? null : get_current_user_id();
		return [
			'links'  => TTCLS_DB::total_links( $user_id ),
			'clicks' => TTCLS_DB::total_clicks( $user_id ),
		];
	}
}
