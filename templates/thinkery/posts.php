<?php
/**
 * This is the main /thinkery/ template.
 *
 * @package Thinkery
 */

$thinkery = Thinkery::get_instance();
include __DIR__ . '/header.php'; ?>
<section id="things">
	<form action="/js/bulk.php" name="bulk" method="post">
		<ul class="things">
		<?php if ( ! have_posts() ) : ?>
			<?php include __DIR__ . '/list/no-thing.php'; ?>
		<?php endif; ?>

		<?php while ( have_posts() ) : ?>
			<?php include __DIR__ . '/list/thing.php'; ?>
		<?php endwhile; ?>
		</ul>
	</form>
</section>
<?php
include __DIR__ . '/footer.php';
