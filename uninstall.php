<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Data is preserved by default so re-installing or updating the plugin
 * (which can route through delete → reinstall on some hosts) does not wipe
 * the links table.
 *
 * To fully wipe everything on uninstall, set the option before deleting:
 *   update_option( 'ttcls_uninstall_remove_data', 1 );
 */
$remove_data = (bool) get_option( 'ttcls_uninstall_remove_data', 0 );

remove_role( 'short_manager' );

$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'ttcls_manage_links' );
}

if ( ! $remove_data ) {
	return;
}

global $wpdb;
$table = $wpdb->prefix . 'ttcls_links';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

$page = get_page_by_path( 'link-shorturl' );
if ( $page ) {
	wp_delete_post( $page->ID, true );
}

delete_option( 'ttcls_db_version' );
delete_option( 'ttcls_uninstall_remove_data' );
