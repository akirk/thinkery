<?php
/**
 * This is the main /thinkery/ template.
 *
 * @package Thinkery
 */

$thinkery = Thinkery::get_instance();
include __DIR__ . '/header.php'; ?>
<section class="posts">
	<div class="thinkery-topbar">
		<?php dynamic_sidebar( 'thinkery-topbar' ); ?>
	</div>
	<?php if ( ! have_posts() ) : ?>
		<?php esc_html_e( 'No things found.', 'thinkery' ); ?>
	<?php endif; ?>

	<?php while ( have_posts() ) : ?>
		<?php
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<div class="post-meta">
					<div class="author">
						<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
							<?php
							// translators: %s is an author name.
							printf( __( 'Saved by %s', 'thinkery' ), '<strong>' . get_the_author() . '</strong>' );
							?>
						</a>
					</div>
					<span class="post-date"><?php /* translators: %s is a time span */ printf( __( '%s ago' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></span>
					<?php edit_post_link(); ?>
				</div>
				<button class="thinkery-trash-post" title="<?php esc_attr_e( 'Trash this post', 'thinkery' ); ?>" data-trash-nonce="<?php echo esc_attr( wp_create_nonce( 'trash-post_' . get_the_ID() ) ); ?>" data-untrash-nonce="<?php echo esc_attr( wp_create_nonce( 'untrash-post_' . get_the_ID() ) ); ?>" data-id="<?php echo esc_attr( get_the_ID() ); ?>">
					&#x1F5D1;
				</button>
			</header>

			<h4 class="entry-title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h4>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</section>
<?php
include __DIR__ . '/footer.php';
