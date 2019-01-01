<?php
/**
 * This template is shown on /thinkery/ when there are no posts.
 *
 * @package thinkery
 */

?>
<?php get_header(); ?>
	<h1>
	<?php
	/* translators: %s is a username. */
	printf( __( 'Hi, %s!', 'thinkery' ), wp_get_current_user()->user_login );
	?>
	</h1>

	<p><?php _e( "Your thinkery haven't posted anything yet!", 'thinkery' ); ?></p>
<?php get_footer(); ?>
