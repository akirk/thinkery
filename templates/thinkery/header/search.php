<div id="search">
	<div id="search-input">
		<form action="<?php echo isset( $_REQUEST['search'] ) && is_string( $_REQUEST['search'] ) ? '/thinkery/add' : '/thinkery/'; ?>" method="<?php echo isset( $_REQUEST['search'] ) && is_string( $_REQUEST['search'] ) ? 'post' : 'get'; ?>">
			<input type="hidden" name="tag" id="currentTag" value="<?php
			if ( isset( $_GET['tag'] ) ) {
				echo htmlspecialchars( $_GET['tag'] );
			} if ( isset( $_GET['subtag'] ) ) {
				echo ' ', htmlspecialchars( $_GET['subtag'] );}
			?>" />
			<input placeholder="<?php _e( 'Search your thinkery' ); ?>" name="<?php echo isset( $_REQUEST['search'] ) && is_string( $_REQUEST['search'] ) ? 'thing' : 's'; ?>" type="text" id="searchadd" autocomplete="off" tabindex="1" value="<?php echo isset( $_REQUEST['s'] ) && is_string( $_REQUEST['s'] ) ? htmlspecialchars( $_REQUEST['s'] ) : ''; ?>" />
			<div class="search-buttons">
				<button class="searchadd ir" type="submit" tabindex="2">Search</button>
			</div>
		</form>

	</div>

	<sub class="hint hidden">No entry yet. Click the "+"-Button to create a new thing</sub><sub class="results
	<?php
	if ( ! isset( $_GET['archived'] ) && ! isset( $_REQUEST['s'] ) ) {
		echo ' hidden';
	}
	?>
	">
	<?php
	if ( isset( $_REQUEST['s'] ) ) {
		?>
		Search results for "<?php echo htmlspecialchars( strlen( $_REQUEST['s'] ) > 40 ? '...' + substr( $_REQUEST['s'], strlen( $_REQUEST['s'] ) - 20 ) : $_REQUEST['s'] ); ?>":
		<?php
	}
	?>
	</sub>
</div>
