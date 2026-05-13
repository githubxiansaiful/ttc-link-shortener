<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_DB {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . TTCLS_TABLE;
	}

	public static function create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			slug VARCHAR(64) NOT NULL,
			destination_url TEXT NOT NULL,
			clicks BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			last_clicked_at DATETIME NULL,
			status TINYINT(1) NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY created_by (created_by),
			KEY status (status)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function insert_link( $slug, $url, $user_id ) {
		global $wpdb;
		$result = $wpdb->insert(
			self::table(),
			[
				'slug'            => $slug,
				'destination_url' => $url,
				'clicks'          => 0,
				'created_by'      => (int) $user_id,
				'created_at'      => gmdate( 'Y-m-d H:i:s' ),
				'status'          => 1,
			],
			[ '%s', '%s', '%d', '%d', '%s', '%d' ]
		);
		return $result ? (int) $wpdb->insert_id : false;
	}

	public static function get_by_slug( $slug ) {
		global $wpdb;
		$table = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug )
		);
	}

	public static function get_by_id( $id ) {
		global $wpdb;
		$table = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", (int) $id )
		);
	}

	public static function increment_click( $slug ) {
		global $wpdb;
		$table = self::table();
		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET clicks = clicks + 1, last_clicked_at = %s WHERE slug = %s",
				gmdate( 'Y-m-d H:i:s' ),
				$slug
			)
		);
	}

	public static function get_recent( $limit = 5, $user_id = null ) {
		global $wpdb;
		$table = self::table();
		$limit = max( 1, (int) $limit );
		if ( null !== $user_id ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE created_by = %d ORDER BY created_at DESC LIMIT %d",
					(int) $user_id,
					$limit
				)
			);
		}
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d", $limit )
		);
	}

	public static function get_all( $args = [] ) {
		global $wpdb;
		$table = self::table();
		$defaults = [
			'user_id'  => null,
			'per_page' => 25,
			'page'     => 1,
			'search'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		];
		$args = wp_parse_args( $args, $defaults );

		$allowed_orderby = [ 'id', 'slug', 'clicks', 'created_at', 'last_clicked_at' ];
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$per_page = max( 1, (int) $args['per_page'] );
		$page     = max( 1, (int) $args['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		$where  = 'WHERE 1=1';
		$params = [];

		if ( null !== $args['user_id'] ) {
			$where    .= ' AND created_by = %d';
			$params[]  = (int) $args['user_id'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like      = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where    .= ' AND ( slug LIKE %s OR destination_url LIKE %s )';
			$params[]  = $like;
			$params[]  = $like;
		}

		$params[] = $per_page;
		$params[] = $offset;

		$sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		// Total count
		$count_sql    = "SELECT COUNT(*) FROM {$table} {$where}";
		$count_params = [];
		if ( null !== $args['user_id'] ) {
			$count_params[] = (int) $args['user_id'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$count_params[] = $like;
			$count_params[] = $like;
		}
		$total = $count_params
			? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $count_params ) )
			: (int) $wpdb->get_var( $count_sql );

		return [
			'rows'     => $rows,
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
			'pages'    => (int) ceil( $total / $per_page ),
		];
	}

	public static function delete_link( $id, $user_id ) {
		global $wpdb;
		$table = self::table();
		$id    = (int) $id;
		$row   = self::get_by_id( $id );
		if ( ! $row ) {
			return false;
		}
		if ( ! current_user_can( 'manage_options' ) && (int) $row->created_by !== (int) $user_id ) {
			return false;
		}
		return (bool) $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
	}

	public static function total_links( $user_id = null ) {
		global $wpdb;
		$table = self::table();
		if ( null !== $user_id ) {
			return (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_by = %d", (int) $user_id )
			);
		}
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	public static function total_clicks( $user_id = null ) {
		global $wpdb;
		$table = self::table();
		if ( null !== $user_id ) {
			return (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COALESCE(SUM(clicks),0) FROM {$table} WHERE created_by = %d", (int) $user_id )
			);
		}
		return (int) $wpdb->get_var( "SELECT COALESCE(SUM(clicks),0) FROM {$table}" );
	}

	public static function slug_exists( $slug ) {
		global $wpdb;
		$table = self::table();
		return (bool) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s LIMIT 1", $slug )
		);
	}
}
