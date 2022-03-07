jQuery( function ( $ )
{
	'use strict';

	$( 'body' ).on( 'change', '.mashsb-rwmb-image-select input', function ()
	{
		var $this = $( this ),
			type = $this.attr( 'type' ),
			selected = $this.is( ':checked' ),
			$parent = $this.parent(),
			$others = $parent.siblings();
		if ( selected )
		{
			$parent.addClass( 'mashsb-rwmb-active' );
			if ( type === 'radio' )
			{
				$others.removeClass( 'mashsb-rwmb-active' );
			}
		}
		else
		{
			$parent.removeClass( 'mashsb-rwmb-active' );
		}
	} );
	$( '.mashsb-rwmb-image-select input' ).trigger( 'change' );
} );
