jQuery( function( $ )
{
	'use strict';

	$( '.mashsb-rwmb-oembed-wrapper .spinner' ).hide();

	$( 'body' ).on( 'click', '.mashsb-rwmb-oembed-wrapper .show-embed', function() {
		var $this = $( this ),
			$spinner = $this.siblings( '.spinner' ),
			data = {
				action: 'rwmb_get_embed',
				url: $this.siblings( 'input' ).val()
			};

		$spinner.show();
		$.post( ajaxurl, data, function( r )
		{
			$spinner.hide();
			$this.siblings( '.embed-code' ).html( r.data );
		}, 'json' );

		return false;
	} );
} );
