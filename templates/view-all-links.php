<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var array $ctx
 */
?>
<section class="ttcls-page" data-ttcls-page="all-links">
	<header class="ttcls-page-head">
		<h2 class="ttcls-page-title"><?php esc_html_e( 'All Links', 'ttc-link-shortener' ); ?></h2>
		<p class="ttcls-page-sub"><?php esc_html_e( 'Manage every short link you have created.', 'ttc-link-shortener' ); ?></p>
	</header>

	<div class="ttcls-card">
		<header class="ttcls-card-head">
			<div class="ttcls-search">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
				</svg>
				<input type="search"
				       class="ttcls-input"
				       data-ttcls-search
				       placeholder="<?php esc_attr_e( 'Search slug or destination…', 'ttc-link-shortener' ); ?>">
			</div>
		</header>

		<div class="ttcls-table-wrap">
			<table class="ttcls-table" data-ttcls-table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Short URL', 'ttc-link-shortener' ); ?></th>
						<th><?php esc_html_e( 'Destination', 'ttc-link-shortener' ); ?></th>
						<th class="ttcls-num"><?php esc_html_e( 'Clicks', 'ttc-link-shortener' ); ?></th>
						<th><?php esc_html_e( 'Created', 'ttc-link-shortener' ); ?></th>
						<th class="ttcls-actions-col"><?php esc_html_e( 'Actions', 'ttc-link-shortener' ); ?></th>
					</tr>
				</thead>
				<tbody data-ttcls-tbody>
					<tr><td colspan="5" class="ttcls-empty"><?php esc_html_e( 'Loading…', 'ttc-link-shortener' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<footer class="ttcls-card-foot">
			<span class="ttcls-pager-info" data-ttcls-pager-info></span>
			<div class="ttcls-pager">
				<button type="button" class="ttcls-btn ttcls-btn-ghost" data-ttcls-pager="prev">
					← <?php esc_html_e( 'Prev', 'ttc-link-shortener' ); ?>
				</button>
				<button type="button" class="ttcls-btn ttcls-btn-ghost" data-ttcls-pager="next">
					<?php esc_html_e( 'Next', 'ttc-link-shortener' ); ?> →
				</button>
			</div>
		</footer>
	</div>
</section>
