<section class="content display">
	<hgroup>
		<a href="<?php echo esc_attr( get_the_permalink() ); ?>" class="flr arrow permalink"></a>
		<h1 class="unlinked" style="display: none"><?php the_title(); ?></h1>
		<h1 class="linked"><a href="<?php echo esc_attr( get_the_permalink() ); ?>"><?php the_title(); ?></a></h1>
		<div class="yui3-g">
			<div class="yui3-u-2-3">
				<div class="meta">
					<span class="tags fll"><?php echo get_the_term_list( get_the_ID(), Thinkery_Things::TAG ); ?></span>
					<span class="info grey dateTime fll" data-t="<?php echo get_the_time( 'Y,m,d,H,i,s' ); ?>"><?php /* translators: %s is a time span */ printf( __( '%s ago' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></span>
					<?php
					// $this->show("header/sharing", $p);
				?>
				</div>
			</div>
			<div class="yui3-u-1-3 alignright">
				<nav class="actions">
					<ul>
						<li><a href="" class="icn edit tip" title="Edit thing">Edit</a></li>
						<li><a href="" class="icn delete tip" title="Delete">Delete</a></li>
					</ul>
				</nav>
			</div>
		</div>
	</hgroup>
	<div class="embed" style="overflow: hidden">
		<?php the_content(); ?>
	</div>
</section>
<section class="content edit" style="display: none">
	<form>
	<hgroup>
		<input type="hidden" value="<?php the_ID(); ?>" class="hidden id" />
		<input type="text" value="<?php echo esc_attr( get_the_title() ); ?>" class="h1 title" placeholder="Title" tabindex="41" />
		<div class="yui3-g">
			<div class="yui3-u-1-2">
				<input type="text" value="<?php echo esc_attr( get_the_term_list( get_the_ID(), Thinkery_Things::TAG ) ); ?>" class="tags" placeholder="Tags (space-separated)" tabindex="42" />
			</div>
			<div class="yui3-u-1-2 alignright">
				<input type="text" value="<?php echo esc_attr( get_the_permalink() ); ?>" class="url" placeholder="URL" tabindex="43" />
			</div>
		</div>
	</hgroup>
	<textarea id="note" class="note" placeholder="Write a note if you wish" tabindex="44"><?php the_content(); ?></textarea>
	<div class="yui3-g">
		<div class="yui3-u-1">
			<nav class="actions">
				<ul>
					<li><button type="submit" value="Save" tabindex="45" class="save">Save</button></li>
					<li><button type="submit" value="Cancel" tabindex="46" class="cancel">Cancel</button></li>
					<li class="autosaved">Autosaved</li>
				</ul>
			</nav>
		</div>
	</div>
	</form>
</section>
<?php
//$this->show("snippets/share-dialog");
