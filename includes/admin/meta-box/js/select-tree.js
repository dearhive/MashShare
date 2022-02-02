jQuery( function( $ )
{
	'use strict';

	function update()
	{
		var $this = $( this ),
			val = $this.val(),
			$selected = $this.siblings( "[data-parent-id='" + val + "']" ),
			$notSelected = $this.parent().find( '.mashsb-rwmb-select-tree' ).not( $selected );

		$selected.removeClass( 'hidden' );
		$notSelected
			.addClass( 'hidden' )
			.find( 'select' )
			.prop( 'selectedIndex', 0 );
	}

	$( '.mashsb-rwmb-input' )
		.on( 'change', '.mashsb-rwmb-select-tree select', update )
		.on( 'clone', '.mashsb-rwmb-select-tree select', update );
} );
