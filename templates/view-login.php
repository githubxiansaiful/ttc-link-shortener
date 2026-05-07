<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$site_name = get_bloginfo( 'name' );
?>
<div class="ttcls-login-shell">
	<div class="ttcls-login-card">
		<div class="ttcls-login-brand">
			<div class="ttcls-brand-mark" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 1 0-7.07-7.07l-1.5 1.5"></path>
					<path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 1 0 7.07 7.07l1.5-1.5"></path>
				</svg>
			</div>
			<div>
				<h2 class="ttcls-login-title"><?php esc_html_e( 'TTC Link Shortener', 'ttc-link-shortener' ); ?></h2>
				<p class="ttcls-login-sub">
					<?php
					/* translators: %s: site name */
					printf( esc_html__( 'Sign in to %s to manage your short links.', 'ttc-link-shortener' ), esc_html( $site_name ) );
					?>
				</p>
			</div>
		</div>

		<form class="ttcls-form ttcls-login-form" data-ttcls-form="login" novalidate>
			<label class="ttcls-field">
				<span class="ttcls-field-label"><?php esc_html_e( 'Username or Email', 'ttc-link-shortener' ); ?></span>
				<input type="text"
				       class="ttcls-input"
				       name="username"
				       autocomplete="username"
				       autocapitalize="none"
				       spellcheck="false"
				       required>
			</label>

			<label class="ttcls-field">
				<span class="ttcls-field-label"><?php esc_html_e( 'Password', 'ttc-link-shortener' ); ?></span>
				<div class="ttcls-pw-wrap">
					<input type="password"
					       class="ttcls-input"
					       name="password"
					       autocomplete="current-password"
					       required>
					<button type="button" class="ttcls-pw-toggle" data-ttcls-pw-toggle aria-label="<?php esc_attr_e( 'Show password', 'ttc-link-shortener' ); ?>">
						<svg class="ttcls-icon-eye" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
							<circle cx="12" cy="12" r="3"></circle>
						</svg>
						<svg class="ttcls-icon-eye-off" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
							<path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a18.46 18.46 0 0 1-2.16 3.19"></path>
							<path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"></path>
							<line x1="1" y1="1" x2="23" y2="23"></line>
						</svg>
					</button>
				</div>
			</label>

			<button type="submit" class="ttcls-btn ttcls-btn-primary ttcls-btn-block">
				<?php esc_html_e( 'Sign In', 'ttc-link-shortener' ); ?>
			</button>

			<p class="ttcls-form-msg" data-ttcls-form-msg role="status" aria-live="polite"></p>

			<div class="ttcls-login-foot">
				<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>">
					<?php esc_html_e( 'Forgot password?', 'ttc-link-shortener' ); ?>
				</a>
			</div>
		</form>
	</div>
</div>
