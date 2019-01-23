<?php
/**
 * This is the main /thinkery/ template.
 *
 * @package Thinkery
 */

$thinkery = Thinkery::get_instance();
$json_things = array();
$json_tags = array();
$things = new WP_Query(
	array(
		'post_type'   => Thinkery_Things::CPT,
		'post_status' => array( 'publish', 'private', 'trash' ),
	)
);

$tags = get_terms(
	array(
		'taxonomy'   => Thinkery_Things::TAG,
		'hide_empty' => false,
	)
);
$json_tags = array();

include __DIR__ . '/header.php'; ?>
<section id="things">
	<form action="/js/bulk.php" name="bulk" method="post">
		<ul class="things">
		<?php if ( ! have_posts() ) : ?>
			<?php include __DIR__ . '/list/no-thing.php'; ?>
		<?php endif; ?>

		<?php
		while ( have_posts() ) {
			the_post();
			include __DIR__ . '/list/thing.php';
			$json_things[] = array(
				'_id'    => get_the_ID(),
				'title'  => get_the_title(),
				'pinned' => get_post_meta( get_the_ID(), 'pinned' ),
				'tags'   => get_the_terms( get_the_ID(), Thinkery_Things::TAG ),
				'classNames'   => array(),
				'html'   => get_the_content(),
			);
		}
		?>
		</ul>
	</form>
</section>
<section id="flyout" class="show-thing"><?php
	include __DIR__ . "/flyout.php";
?>
</section>

<?php
wp_add_inline_script( 'thinkery', 'Thinkery.load( ' . wp_json_encode( $json_things ) . ', ' . wp_json_encode( $json_tags ) . ' );' );
include __DIR__ . '/footer.php';
