<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var array $ctx
 */
$total_links  = isset( $ctx['total_links'] ) ? (int) $ctx['total_links'] : 0;
$total_clicks = isset( $ctx['total_clicks'] ) ? (int) $ctx['total_clicks'] : 0;
$recent       = isset( $ctx['recent'] ) ? (array) $ctx['recent'] : [];
$site_host    = wp_parse_url( home_url(), PHP_URL_HOST );
?>
<section class="ttcls-page">
	<header class="ttcls-page-head">
		<h2 class="ttcls-page-title"><?php esc_html_e( 'Dashboard', 'ttc-link-shortener' ); ?></h2>
		<p class="ttcls-page-sub">
			<?php
			/* translators: %s: site host */
			printf( esc_html__( 'Create short links for %s', 'ttc-link-shortener' ), esc_html( $site_host ) );
			?>
		</p>
	</header>

	<div class="ttcls-stats">
		<div class="ttcls-stat">
			<span class="ttcls-stat-label"><?php esc_html_e( 'Total Links', 'ttc-link-shortener' ); ?></span>
			<span class="ttcls-stat-value" data-ttcls-stat="links"><?php echo esc_html( $total_links ); ?></span>
		</div>
		<div class="ttcls-stat">
			<span class="ttcls-stat-label"><?php esc_html_e( 'Total Clicks', 'ttc-link-shortener' ); ?></span>
			<span class="ttcls-stat-value" data-ttcls-stat="clicks"><?php echo esc_html( $total_clicks ); ?></span>
		</div>
	</div>

	<div class="ttcls-card">
		<h3 class="ttcls-card-title"><?php esc_html_e( 'Shorten a URL', 'ttc-link-shortener' ); ?></h3>
		<form class="ttcls-form" data-ttcls-form="create" novalidate>
			<div class="ttcls-input-row">
				<input type="url"
				       class="ttcls-input"
				       name="url"
				       inputmode="url"
				       placeholder="<?php esc_attr_e( 'https://example.com/long/url', 'ttc-link-shortener' ); ?>"
				       required>
				<button type="submit" class="ttcls-btn ttcls-btn-primary">
					<?php esc_html_e( 'Shorten', 'ttc-link-shortener' ); ?>
				</button>
			</div>
			<div class="ttcls-input-row ttcls-slug-row">
				<span class="ttcls-slug-prefix"><?php echo esc_html( $site_host . '/' ); ?></span>
				<input type="text"
				       class="ttcls-input ttcls-slug-input"
				       name="slug"
				       maxlength="64"
				       autocomplete="off"
				       spellcheck="false"
				       pattern="[A-Za-z0-9][A-Za-z0-9_-]{1,62}[A-Za-z0-9]"
				       placeholder="<?php esc_attr_e( 'custom-slug (optional)', 'ttc-link-shortener' ); ?>">
			</div>
			<p class="ttcls-form-hint">
				<?php esc_html_e( 'Leave the slug blank to auto-generate a 6-character code. Letters, numbers, hyphens and underscores allowed (3–64 chars).', 'ttc-link-shortener' ); ?>
			</p>
			<p class="ttcls-form-msg" data-ttcls-form-msg role="status" aria-live="polite"></p>
		</form>
	</div>

	<div class="ttcls-card">
		<header class="ttcls-card-head">
			<h3 class="ttcls-card-title">
				<span aria-hidden="true">📈</span>
				<?php esc_html_e( 'Recent links', 'ttc-link-shortener' ); ?>
			</h3>
			<a class="ttcls-link-btn" href="<?php echo esc_url( add_query_arg( 'view', 'all-links', $ctx['page_url'] ) ); ?>">
				<?php esc_html_e( 'View all', 'ttc-link-shortener' ); ?> →
			</a>
		</header>

		<ul class="ttcls-link-list" data-ttcls-recent>
			<?php if ( empty( $recent ) ) : ?>
				<li class="ttcls-empty" data-ttcls-empty>
					<?php esc_html_e( 'No links yet. Create your first short URL above.', 'ttc-link-shortener' ); ?>
				</li>
			<?php else : ?>
				<?php foreach ( $recent as $row ) : ?>
					<?php
					$short_url = TTCLS_Helpers::short_url( $row->slug );
					?>
					<li class="ttcls-link-row" data-ttcls-row="<?php echo esc_attr( $row->id ); ?>">
						<div class="ttcls-link-main">
							<a class="ttcls-link-short" href="<?php echo esc_url( $short_url ); ?>" target="_blank" rel="noopener">
								<?php echo esc_html( $site_host . '/' . $row->slug ); ?>
							</a>
							<span class="ttcls-link-dest" title="<?php echo esc_attr( $row->destination_url ); ?>">
								<?php echo esc_html( $row->destination_url ); ?>
							</span>
						</div>
						<div class="ttcls-link-meta">
							<span class="ttcls-badge">
								<?php
								/* translators: %d: click count */
								printf( esc_html( _n( '%d click', '%d clicks', (int) $row->clicks, 'ttc-link-shortener' ) ), (int) $row->clicks );
								?>
							</span>
							<button type="button" class="ttcls-iconbtn" data-ttcls-copy="<?php echo esc_attr( $short_url ); ?>" aria-label="<?php esc_attr_e( 'Copy short URL', 'ttc-link-shortener' ); ?>">
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
									<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
								</svg>
							</button>
							<button type="button" class="ttcls-iconbtn ttcls-iconbtn-danger" data-ttcls-delete="<?php echo esc_attr( $row->id ); ?>" aria-label="<?php esc_attr_e( 'Delete link', 'ttc-link-shortener' ); ?>">
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<polyline points="3 6 5 6 21 6"></polyline>
									<path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
									<path d="M10 11v6"></path>
									<path d="M14 11v6"></path>
								</svg>
							</button>
						</div>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>
</section>
