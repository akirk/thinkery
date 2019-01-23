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
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$json_tags = array();
include __DIR__ . '/header.php'; ?>
<section id="things">
	<form action="/js/bulk.php" name="bulk" method="post">
		<ul class="things">
		<?php

		$first_post = false;
		if ( ! have_posts() ) {
			include __DIR__ . '/list/no-thing.php';
			$first_post = array(
				'post_title' => '',
			);
		}
		while ( have_posts() ) {
			the_post();
			if ( ! $first_post ) {
				$first_post = $post;
			}
			include __DIR__ . '/list/thing.php';
			$json_thing = array(
				'_id'    => get_the_ID(),
				'title'  => get_the_title(),
				'date'  => get_the_time( 'Y,m,d,H,i,s' ),
				'pinned' => get_post_meta( get_the_ID(), 'pinned' ),
				'tags'   => get_the_term_list( get_the_ID(), Thinkery_Things::TAG ),
				'classNames'   => array(),
				'html'   => get_the_content(),
				'url'   => get_the_permalink(),
				'private' => 'publish' !== get_post_status(),
			);
			if ( ! $json_thing['tags'] ) {
				$json_thing['tags'] = '';
			}
			$json_things[] = $json_thing;
		}
		?>
		</ul>
	</form>
</section>
<section id="flyout" class="show-thing"><?php
	setup_postdata( $first_post );
	include __DIR__ . "/flyout.php";
?>
</section>

<?php
wp_add_inline_script( 'thinkery', 'Thinkery.load( ' . wp_json_encode( $json_things ) . ', ' . wp_json_encode( $json_tags ) . ' );' );
include __DIR__ . '/footer.php';
