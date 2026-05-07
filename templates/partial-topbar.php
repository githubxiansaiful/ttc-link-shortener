<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var array $ctx
 */
$view = isset( $ctx['view'] ) ? $ctx['view'] : 'dashboard';
$title = $view === 'all-links'
	? __( 'All Links', 'ttc-link-shortener' )
	: __( 'TTC URL Shortener', 'ttc-link-shortener' );
?>
<header class="ttcls-topbar">
	<button type="button" class="ttcls-iconbtn" data-ttcls-toggle="sidebar" aria-label="<?php esc_attr_e( 'Toggle sidebar', 'ttc-link-shortener' ); ?>">
		<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<line x1="3" y1="6" x2="21" y2="6"></line>
			<line x1="3" y1="12" x2="21" y2="12"></line>
			<line x1="3" y1="18" x2="21" y2="18"></line>
		</svg>
	</button>

	<h1 class="ttcls-topbar-title"><?php echo esc_html( $title ); ?></h1>

	<div class="ttcls-topbar-actions">
		<button type="button" class="ttcls-iconbtn" data-ttcls-toggle="theme" aria-label="<?php esc_attr_e( 'Toggle theme', 'ttc-link-shortener' ); ?>">
			<svg class="ttcls-icon-moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
			</svg>
			<svg class="ttcls-icon-sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="12" cy="12" r="5"></circle>
				<line x1="12" y1="1" x2="12" y2="3"></line>
				<line x1="12" y1="21" x2="12" y2="23"></line>
				<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
				<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
				<line x1="1" y1="12" x2="3" y2="12"></line>
				<line x1="21" y1="12" x2="23" y2="12"></line>
				<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
				<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
			</svg>
		</button>
	</div>
</header>
