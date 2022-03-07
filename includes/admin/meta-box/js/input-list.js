jQuery( function( $ )
{
  function update()
  {
    var $this = $( this ),
      $children = $this.closest( 'li' ).children('ul');

    if ( $this.is( ':checked' ) )
    {
      $children.removeClass( 'hidden' );
    }
    else
    {
      $children
        .addClass( 'hidden' )
        .find( 'input' )
        .removeAttr( 'checked' );
    }
  }

  $( '.mashsb-rwmb-input' )
    .on( 'change', '.mashsb-rwmb-input-list.collapse :checkbox', update )
    .on( 'clone', '.mashsb-rwmb-input-list.collapse :checkbox', update );
  $( '.mashsb-rwmb-input-list.collapse :checkbox' ).each( update );
} );
