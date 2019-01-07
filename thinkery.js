/* Trash post */
jQuery( function( $ ) {
	jQuery( document ).on( 'click', 'button.thinkery-trash-post', function() {
		var button = $( this );
		jQuery.post( thinkery.ajax_url, {
			_ajax_nonce: button.data( 'trash-nonce' ),
			action: 'trash-post',
			id: button.data( 'id' ),
		}, function( response ) {
			if ( response ) {
				button.text( thinkery.text_undo ).attr( 'class', 'thinkery-untrash-post' );
			}
		} );

		return false;
	} );
	jQuery( document ).on( 'click', 'button.thinkery-untrash-post', function() {
		var button = $( this );
		jQuery.post( thinkery.ajax_url, {
			_ajax_nonce: button.data( 'untrash-nonce' ),
			action: 'untrash-post',
			id: button.data( 'id' ),
		}, function( response ) {
			if ( response ) {
				button.html( '&#x1F5D1;' ).attr( 'class', 'thinkery-trash-post' );
			}
		} );

		return false;
	} );
});
