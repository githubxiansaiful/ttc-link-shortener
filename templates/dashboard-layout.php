<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var array $ctx
 */
$view = isset( $ctx['view'] ) ? $ctx['view'] : 'dashboard';
?>
<div class="ttcls-app" data-ttcls-app>
	<div class="ttcls-backdrop" data-ttcls-backdrop aria-hidden="true"></div>

	<?php include TTCLS_PATH . 'templates/partial-sidebar.php'; ?>

	<div class="ttcls-main">
		<?php include TTCLS_PATH . 'templates/partial-topbar.php'; ?>

		<div class="ttcls-content">
			<?php
			if ( 'all-links' === $view ) {
				include TTCLS_PATH . 'templates/view-all-links.php';
			} else {
				include TTCLS_PATH . 'templates/view-dashboard.php';
			}
			?>
		</div>
	</div>
</div>
