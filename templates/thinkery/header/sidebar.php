<?php
$styles = array();

function displayTag( $tag, $param ) {

	if ( ! isset( $param['selectedTag'] ) ) {
		$param['selectedTag'] = false;
	}

	$class = array();
	$subtags = array();

	if ( $param['selectedTag'] == $tag->name ) {
		if ( $tag->name && substr( $tag->name, 0, 1 ) != ':' ) {
			$subtags = $tag->name->subtags;
			if ( ! empty( $subtags ) ) {
				$class[] = 'has-subtags';
			}
			if ( ! isset( $param['selectedSubTag'] ) ) {
				$class[] = 'active';
			}
		} else {
			$class[] = 'active';
		}
	}

	?><li
	<?php
	if ( $tag->name !== false && substr( $tag->name, 0, 1 ) != ':' ) {
		// $class = array_merge($class, $tag->name->getSpecialTagClasses());
	}

	if ( ! empty( $class ) ) {
		?>
		 class="<?php echo implode( ' ', $class ); ?>"
		<?php
	}
	if ( $tag->name === false ) {
		?>
		 data-all="true">
		<?php
	} else {
		?>
		 data-tag="<?php echo $tag->name; ?>"
		<?php
		global $user;
		if ( false && $special_tag = $user->specialTag( $tag->name ) ) {
			foreach ( $special_tag as $t ) {
				if ( ! isset( $t['type'] ) ) {
					continue;
				}
				if ( $t['type'] == 'color' ) {
					echo ' data-color="', $t['options']['hex'], '" style="border-left: 2px solid ', $t['options']['hex'], '"';
				} elseif ( $t['type'] == 'todo' ) {
					echo ' data-todo="true"';
				}
			}
		}
		?>
		>
		<?php
	}
	?>
	<a href="/thinkery/<?php echo $tag->slug; ?>" title="<?php printf( _n( '%s thing', '%s things', $tag->count, 'thinkery' ), $tag->count ); ?>">
	<?php

	if ( $tag->name === false ) {
		echo _( 'All' );
	} elseif ( isset( $tag->name ) ) {
		echo $tag->name;
	} else {
		echo htmlspecialchars( $tag->name );
	}

	?>
		 <small class="num"><?php echo $tag->count; ?></small>
		<?php
		if ( $tag->name !== false && substr( $tag->name, 0, 1 ) != ':' ) {
			echo '<div class="settings"></div>';
		}

		?>
		</a>
		<?php

		if ( ! empty( $subtags ) ) {
			?>
		<ul>
				<?php
				$c = 0;
				$min_count = 2;
				$subtag_found = true;
				$selectedSubTag = false;
				if ( isset( $param['selectedSubTag'] ) ) {
					$selectedSubTag = $param['selectedSubTag'];
					$subtag_found = false;
				}
				foreach ( $subtags as $subtag => $count ) {
					if ( $c == 0 && $count < $min_count ) {
						$min_count = 1;
					}
					if ( $count < $min_count || $c++ > 4 ) {
						if ( $subtag_found ) {
							break;
						}
						if ( $subtag != $selectedSubTag ) {
							continue;
						}
					}
					?>
			<li data-tag="<?php echo $tag->name; ?>" data-subtag="<?php echo $subtag; ?>"
					<?php
					if ( $subtag === $selectedSubTag ) {
						echo ' class="active"';
						$subtag_found = true;
					}
					?>
			><a href="/thinkery/<?php echo $tag->slug; ?>/<?php echo $subtag->slug; ?>" title="<?php printf( _n( '%s thing', '%s things', $tag->count, 'thinkery' ), $tag->count ); ?>"><?php echo $subtag; ?> <small class="num"><?php echo $subtag->count; ?></small></a></li>
					<?php
				}
				?>
		</ul>
			<?php
		}
		?>
	</li>
	<?php
}

?>
<nav id="menu">
	<div class="create-new-tag"><div class="grey bold fll">+</div> Drop for new tag</div>
	<ul class="tags all">
		<?php
			// $all = $things->get_total();
			// $all['tag'] = false;
			// $all['url'] = '/thinkery/';
			// displayTag( $all, $param );

			// $hideGlobalTags = false;
			// foreach (array(
			// ":urls" => array( THINKERY_GLOBAL_TAG_BOOKMARKS, "Bookmarks" ),
			// ":notes" => array( THINKERY_GLOBAL_TAG_NOTES, "Notes" ),
			// ":todos" => array( THINKERY_GLOBAL_TAG_TODOS, "Todos" ),
			// ) as $tag => $v) {
			// if ($hideGlobalTags & $v[0]) continue;
			// $global = array(
			// "tag" => $tag,
			// "count" => $display_user->tags->count($tag),
			// "title" => $v[1],
			// "url" => BuildUrl::TagUrl($tag, $display_user->username),
			// );
			// displayTag($global, $param);
			// }
		?>
	</ul>
	<?php

	$limit = 10;
	function sortByCount( $a, $b ) {
		if ( $a['count'] == $b['count'] ) {
			return 0;
		}
		return ( $a['count'] > $b['count'] ) ? -1 : 1;
	}

	$favorites = array();
	// $favorites = array_merge($display_user->tags->favorites(isset($_GET["archived"])), $display_user->smartfolders->tags());
	usort( $favorites, 'sortByCount' );
	?>
	<h6 class="toggle">Favorite</h6>
<ul class="tags" id="favoriteTags">
	<?php
	if ( ! empty( $favorites ) ) {
		foreach ( $favorites as $tag ) {
			displayTag( $tag, $param );
			$limit -= 1;
		}
	} else {
		echo '<li class="lightgrey italic" style="padding-left: 1.75em">no favorites yet</li>';
	}
	?>
</ul><h6 class="toggle">All</h6>
<ul class="tags" id="nonFavoriteTags">
	<?php
			// $tags = $display_user->tags->allNonFavorite(isset($_GET["archived"]), isset($_GET["full"]) ? false : $limit);
			$i = 1;
	foreach ( $tags as $tag ) {
		displayTag( $tag, $param );
	}
		// }
	?>
</ul>
	<?php if ( $display_user->tags->lastTotal > $limit ) : ?>
		<div class="moretags
		<?php
		if ( isset( $_GET['full'] ) ) {
			echo ' loaded';}
		?>
		">
			<?php if ( isset( $_GET['full'] ) ) : ?>
				<a href="" class="expanded">Fewer tags &uarr;</a>
			<?php else : ?>
				<a href="" class="collapsed">All tags &darr;</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<nav id="filter">
		<ul>
				<li>
					<a class="button action-show-archived" href="
					<?php
					if ( ! isset( $_GET['archived'] ) ) {
						echo '?archived'; } else {
						if ( isset( $_GET['tag'] ) ) {
							echo htmlspecialchars( $_GET['tag'] );
						} else {
							echo $display_user->getOverviewUrl();
						}
						}
						?>
					"><?php echo isset( $_GET['archived'] ) ? 'Hide' : 'Show'; ?> archived &raquo;</a>
				</li>
				<li>
					<a href="" id="toggleBulk" class="icn bulkedit">Bulk edit</a>
				</li>
				<li class="grey">
					<small><b>Tip:</b> Shift-click a thing in the list to start bulk edit</small>
				</li>
		</ul>
	</nav>
</nav>

<div id="tagSettings" style="display: none"><form>
	<h3></h3>
	<label><input type="checkbox" name="favorite" value="1" /> Favorite tag<br/>
		<span>Moves the tag to the &quot;Favorite&quot; section on the left</span>
	</label>
	<label><input type="checkbox" name="todo" value="1" /> Todo tag<br/>
		<span>Adds a checkbox to every thing with this tag</span></label>
	<label>Color<input type="text" name="color" value="" /></label>
</form></div>
