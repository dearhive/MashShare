jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update color picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update()
	{
		var $this = $( this ),
			$output = $this.siblings( '.mashsb-rwmb-output' );

    $this.on( 'input propertychange change', function( e )
    {
      $output.html( $this.val() );
    } );

	}

	$( ':input.mashsb-rwmb-range' ).each( update );
	$( '.mashsb-rwmb-input' ).on( 'clone', 'input.mashsb-rwmb-range', update );
} );
