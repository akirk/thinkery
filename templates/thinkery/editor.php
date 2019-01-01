<?php
/**
 * This template contains the small editor shown on /thinkery/.
 *
 * @package thinkery
 */

?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" class="thinkery-post-inline">
	<?php wp_nonce_field( 'thinkery_publish' ); ?>
	<input type="hidden" name="action" value="thinkery_publish" />
	<input type="text" name="title" value="" placeholder="<?php echo esc_attr( __( 'Title' ) ); ?>" /><br />
	<textarea name="content" rows="5" cols="70" placeholder="<?php echo /* translators: %s is a user display name. */ esc_attr( sprintf( __( 'What are you up to, %s?', 'thinkery' ), wp_get_current_user()->display_name ) ); ?>"></textarea><br/>
	<button>Post to your thinkery</button>
	<span style="margin-left: 2em"><input type="hidden" name="status" value="private" /> Published Privately</span>
</form>
