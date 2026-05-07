<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Roles {

	public static function add_role() {
		add_role( TTCLS_ROLE, __( 'Short Manager', 'ttc-link-shortener' ), [
			'read'               => true,
			'ttcls_manage_links' => true,
		] );

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'ttcls_manage_links' );
		}
	}

	public static function remove_role() {
		remove_role( TTCLS_ROLE );
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->remove_cap( 'ttcls_manage_links' );
		}
	}
}
