window.rwmb = window.rwmb || {};

jQuery( function ( $ )
{
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaField = views.MediaField,
		MediaItem = views.MediaItem,
		MediaList = views.MediaList,
		ImageField, ImageList, ImageItem;

	ImageField = views.ImageField = MediaField.extend( {
		createList: function ()
		{
			this.list = new MediaList( { collection: this.collection, props: this.props, itemView: ImageItem } );
		}
	} );

	ImageItem = views.ImageItem = MediaItem.extend( {
		className: 'mashsb-rwmb-image-item',
		template : wp.template( 'mashsb-rwmb-image-item' )
	} );

	/**
	 * Initialize image fields
	 * @return void
	 */
	function initImageField()
	{
		new ImageField( { input: this, el: $( this ).siblings( 'div.mashsb-rwmb-media-view' ) } );
	}
	$( ':input.mashsb-rwmb-image_advanced' ).each( initImageField );
	$( '.mashsb-rwmb-input' )
		.on( 'clone', ':input.mashsb-rwmb-image_advanced', initImageField )
} );
