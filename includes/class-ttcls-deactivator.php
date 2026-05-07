<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TTCLS_Deactivator {

	public static function deactivate() {
		TTCLS_Roles::remove_role();
		flush_rewrite_rules();
	}
}
