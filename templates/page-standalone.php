<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo esc_html( wp_get_document_title() ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
while ( have_posts() ) {
	the_post();
	the_content();
}
wp_footer();
?>
</body>
</html>
