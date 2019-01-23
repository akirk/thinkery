<?php
$classes = array( 'thing-list-item' );

if ( $active ) {
	$classes[] = 'active';
}
$classes[] = 'canEdit';

if ( get_post_meta( get_the_ID(), 'pinned' ) ) {
	$classes[] = 'pinned';
}
if ( get_post_meta( get_the_ID(), 'todo' ) && get_post_meta( get_the_ID(), 'done' ) ) {
	$classes[] = 'checked';
}

?>
<li class="<?php echo implode( ' ', $classes ); ?>">
	<span class="drag"></span>
	<?php if ( 'publish' === get_post_status() ) { ?>
		<div class="privacy">
			<span class="icn grey public tip canEdit" title="Public: everybody can see it"></span>
		</div>
	<?php } else { ?>
		<div class="privacy" style="display: none">
			<span class="icn grey private canEdit tip" title="Private: only you can see it"></span>
		</div>
	<?php } ?>
	<input type="checkbox" name="bulk[]" value="<?php the_ID(); ?>" class="bulk" />
	<article><?php the_title(); ?></article>
	<sub class="meta">
		<span class="tags"><?php echo get_the_term_list( get_the_ID(), Thinkery_Things::TAG ); ?></span> <span class="info grey dateTime" data-t="<?php echo get_the_time( 'Y,m,d,H,i,s' ); ?>"><?php /* translators: %s is a time span */ printf( __( '%s ago' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></span><span class="actions pin"><a href="" class="status icn pin tip" title="<?php echo get_post_meta( get_the_ID(), 'pinned' ) ? __( 'Unpin this thing', 'thinkery' ) : __( 'Pin this thing', 'thinkery' ); ?>">&nbsp;</a></span><span class="actions"><a href="" class="icn edit tip" title="Edit">&nbsp;</a><a href="" class="icn archive tip" title="<?php echo true ? 'Move to archive' : 'Unarchive'; ?>">&nbsp;</a><a href="" class="icn delete tip" title="Delete">&nbsp;</a></span>
	</sub>
</li>
