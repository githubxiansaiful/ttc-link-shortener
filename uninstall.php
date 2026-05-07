<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
$table = $wpdb->prefix . 'ttcls_links';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

remove_role( 'short_manager' );

$page = get_page_by_path( 'link-shorturl' );
if ( $page ) {
	wp_delete_post( $page->ID, true );
}

$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'ttcls_manage_links' );
}
