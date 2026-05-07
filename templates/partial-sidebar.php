<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var array $ctx
 */
$page_url = isset( $ctx['page_url'] ) ? $ctx['page_url'] : home_url( '/' . TTCLS_PAGE_SLUG . '/' );
$view     = isset( $ctx['view'] ) ? $ctx['view'] : 'dashboard';
?>
<aside class="ttcls-sidebar" aria-label="<?php esc_attr_e( 'Primary navigation', 'ttc-link-shortener' ); ?>">
	<button type="button" class="ttcls-sidebar-close" data-ttcls-close="sidebar" aria-label="<?php esc_attr_e( 'Close menu', 'ttc-link-shortener' ); ?>">
		<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<line x1="18" y1="6" x2="6" y2="18"></line>
			<line x1="6" y1="6" x2="18" y2="18"></line>
		</svg>
	</button>

	<div class="ttcls-brand">
		<div class="ttcls-brand-mark" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 1 0-7.07-7.07l-1.5 1.5"></path>
				<path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 1 0 7.07 7.07l1.5-1.5"></path>
			</svg>
		</div>
		<div class="ttcls-brand-text">
			<span class="ttcls-brand-name">ttc.link</span>
			<span class="ttcls-brand-sub"><?php esc_html_e( 'URL Shortener', 'ttc-link-shortener' ); ?></span>
		</div>
	</div>

	<nav class="ttcls-nav">
		<div class="ttcls-nav-label"><?php esc_html_e( 'Navigation', 'ttc-link-shortener' ); ?></div>
		<a class="ttcls-nav-link <?php echo $view === 'dashboard' ? 'is-active' : ''; ?>"
		   href="<?php echo esc_url( $page_url ); ?>">
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<rect x="3" y="3" width="7" height="7"></rect>
				<rect x="14" y="3" width="7" height="7"></rect>
				<rect x="14" y="14" width="7" height="7"></rect>
				<rect x="3" y="14" width="7" height="7"></rect>
			</svg>
			<span><?php esc_html_e( 'Dashboard', 'ttc-link-shortener' ); ?></span>
		</a>
		<a class="ttcls-nav-link <?php echo $view === 'all-links' ? 'is-active' : ''; ?>"
		   href="<?php echo esc_url( add_query_arg( 'view', 'all-links', $page_url ) ); ?>">
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 1 0-7.07-7.07l-1.5 1.5"></path>
				<path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 1 0 7.07 7.07l1.5-1.5"></path>
			</svg>
			<span><?php esc_html_e( 'All Links', 'ttc-link-shortener' ); ?></span>
		</a>
	</nav>

	<div class="ttcls-sidebar-foot">
		<a class="ttcls-nav-link" href="<?php echo esc_url( wp_logout_url( $page_url ) ); ?>">
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
				<polyline points="16 17 21 12 16 7"></polyline>
				<line x1="21" y1="12" x2="9" y2="12"></line>
			</svg>
			<span><?php esc_html_e( 'Sign Out', 'ttc-link-shortener' ); ?></span>
		</a>
	</div>
</aside>
