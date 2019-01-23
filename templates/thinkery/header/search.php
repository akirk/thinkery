<div id="search">
	<div id="search-input">
		<form action="<?php echo isset( $param['search'] ) && is_string( $param['search'] ) ? '/add' : '/thinkery/search'; ?>" method="<?php echo isset( $param['search'] ) && is_string( $param['search'] ) ? 'post' : 'get'; ?>">
			<input type="hidden" name="tag" id="currentTag" value="
			<?php
			if ( isset( $_GET['tag'] ) ) {
				echo htmlspecialchars( $_GET['tag'] );
			} if ( isset( $_GET['subtag'] ) ) {
				echo ' ', htmlspecialchars( $_GET['subtag'] );}
			?>
			" />
			<input placeholder="<?php _e( 'Search your thinkery' ); ?>" name="<?php echo isset( $param['search'] ) && is_string( $param['search'] ) ? 'thing' : 'q'; ?>" type="text" id="searchadd" autocomplete="off" tabindex="1" value="<?php echo isset( $param['q'] ) && is_string( $param['q'] ) ? htmlspecialchars( $param['q'] ) : ''; ?>" />
			<div class="search-buttons">
				<button class="searchadd ir" type="submit" tabindex="2">Search</button>
			</div>
		</form>

	</div>

	<sub class="hint hidden">No entry yet. Click the "+"-Button to create a new thing</sub><sub class="results
	<?php
	if ( ! isset( $_GET['archived'] ) && ! isset( $param['q'] ) ) {
		echo ' hidden';
	}
	?>
	">
	<?php
	if ( isset( $param['q'] ) ) {
		?>
		Search results for "<?php echo htmlspecialchars( strlen( $param['q'] ) > 40 ? '...' + substr( $param['q'], strlen( $param['q'] ) - 20 ) : $param['q'] ); ?>":
		<?php
	}
	?>
	</sub>
</div>
