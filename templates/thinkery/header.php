<?php
/**
 * The /thinkery/ header
 *
 * @package thinkery
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>thinkery</title>
<?php wp_head(); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo esc_url( plugins_url( 'css/thinkery.css?' . time(), dirname( dirname( __FILE__ ) ) ) ); ?>" />
</head>

<body <?php body_class(); ?>>
	<div id="container" class="container">
		<header>
			<div id="logo">
				<a href="/thinkery/" class="ir">thinkery.me</a>
			</div>
			<?php include __DIR__ . '/header/search.php'; ?>
			<?php include __DIR__ . '/header/bulkcontrols.php'; ?>
		</header>

		<div id="main">
			<?php
			include __DIR__ . '/header/sidebar.php';

