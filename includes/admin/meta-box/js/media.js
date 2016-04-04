window.rwmb = window.rwmb || {};

jQuery(function ($)
{
    'use strict';

    var views = rwmb.views = rwmb.views || {},
            MediaField, MediaList, MediaItem, MediaButton, MediaStatus;
    rwmb.test = 'spoon';

    MediaList = views.MediaList = Backbone.View.extend({
        tagName: 'ul',
        className: 'mashsb-rwmb-media-list',
        addItemView: function (item)
        {
            if (!this.itemViews[item.cid])
            {
                this.itemViews[item.cid] = new this.itemView({
                    model: item,
                    collection: this.collection,
                    props: this.props
                });
            }
            this.$el.append(this.itemViews[item.cid].el);
        },
        render: function ()
        {
            this.$el.empty();
            this.collection.each(this.addItemView);

        },
        initialize: function (options)
        {
            var that = this;
            this.itemViews = {};
            this.props = options.props;
            this.itemView = options.itemView || MediaItem;

            this.listenTo(this.collection, 'add', this.addItemView);

            this.listenTo(this.collection, 'remove', function (item, collection)
            {
                if (this.itemViews[item.cid])
                {
                    this.itemViews[item.cid].remove();
                    delete this.itemViews[item.cid];
                }
            });

            //Sort media using sortable
            this.initSort();

            this.render();
        },
        initSort: function ()
        {
            this.$el.sortable({delay: 150});
        }
    });

    MediaField = views.MediaField = Backbone.View.extend({
        initialize: function (options)
        {
            var that = this;
            this.$input = $(options.input);
            this.values = this.$input.val().split(',');
            this.props = new Backbone.Model(this.$el.data());
            this.props.set('fieldName', this.$input.attr('name'));

            //Create collection
            this.collection = new wp.media.model.Attachments();

            //Create views
            this.createList();
            this.createAddButton()
            this.createStatus();


            //Render
            this.render();

            //Limit max files
            this.listenTo(this.collection, 'add', function (item, collection)
            {
                var maxFiles = this.props.get('maxFiles');
                if (maxFiles > 0 && this.collection.length > maxFiles)
                {
                    this.collection.pop();
                }
                //Reset some styles
                if (this.collection.length === 1) {
                    mashsb_reset_new(this);
                }
            });
            

            //Load initial media
            if (!_.isEmpty(this.values))
            {
                this.collection.props.set({
                    query: true,
                    include: this.values,
                    orderby: 'post__in',
                    order: 'ASC',
                    type: this.props.get('mimeType'),
                    perPage: this.props.get('maxFiles') || -1
                });
                this.collection.more();
               
                //Reset some styles after initial loading images
                if (this.collection.length === 1) {
                    mashsb_reset_new(this);
                }
            }

            //Listen for destroy event on input
            this.$input
                    .on('remove', function () {
                        if (that.props.get('forceDelete'))
                        {
                            _.each(_.clone(that.collection.models), function (model)
                            {
                                model.destroy();
                            });
                        }
                    })
        },
        createList: function ()
        {
            this.list = new MediaList({collection: this.collection, props: this.props});
        },
        createAddButton: function ()
        {
            this.addButton = new MediaButton({collection: this.collection, props: this.props});
        },
        createStatus: function ()
        {
            this.status = new MediaStatus({collection: this.collection, props: this.props});
        },
        render: function ()
        {
            //Empty then add parts
            this.$el
                    .empty()
                    .append(
                            this.list.el,
                            this.addButton.el,
                            this.status.el
                            );
            
        }
    });

    MediaStatus = views.MediaStatus = Backbone.View.extend({
        tagName: 'span',
        className: 'mashsb-rwmb-media-status',
        template: wp.template('mashsb-rwmb-media-status'),
        initialize: function (options)
        {
            this.props = options.props;
            this.listenTo(this.collection, 'add remove reset', this.render);
            this.render();
        },
        render: function ()
        {
            var data = {
                items: this.collection.length,
                maxFiles: this.props.get('maxFiles')
            };
            this.$el.html(this.template(data));
                          
        }
    });

    MediaButton = views.MediaButton = Backbone.View.extend({
        className: 'mashsb-rwmb-add-media button',
        tagName: 'a',
        events: {
            click: function ()
            {
                var models = this.collection.models;

                // Destroy the previous collection frame.
                if (this._frame)
                {
                    //this.stopListening( this._frame );
                    this._frame.dispose();
                }

                this._frame = wp.media({
                    className: 'media-frame mashsb-rwmb-media-frame',
                    multiple: true,
                    title: 'Select Media',
                    editing: true,
                    library: {
                        type: this.props.get('mimeType')
                    }
                });

                this._frame.on('select', function ()
                {
                    var selection = this._frame.state().get('selection');
                    this.collection.add(selection.models);
                    mashsb_reset_remove(this);
                }, this);

                this._frame.open();
            }
        },
        render: function ()
        {
            this.$el.text(i18nRwmbMedia.add);
            return this;
        },
        initialize: function (options)
        {
            this.props = options.props;
            this.listenTo(this.collection, 'add remove reset', function ()
            {
                var maxFiles = this.props.get('maxFiles');

                if (maxFiles > 0)
                {
                    this.$el.toggle(this.collection.length < maxFiles);
                }
            });

            this.render();
        }
    });

    MediaItem = views.MediaItem = Backbone.View.extend({
        tagName: 'li',
        className: 'mashsb-rwmb-media-item',
        template: wp.template('mashsb-rwmb-media-item'),
        initialize: function (options)
        {
            this.props = options.props;
            this.render();
            this.listenTo(this.model, 'destroy', function (model)
            {
                this.collection.remove(this.model);
            })
                    .listenTo(this.model, 'change', function ()
                    {
                        this.render();
                    });
        },
        events: {
            'click .mashsb-rwmb-remove-media': function (e)
            {
                this.collection.remove(this.model);
                if (this.props.get('forceDelete'))
                {
                    this.model.destroy();
                }
                mashsb_reset_remove(this);
                return false;
            }
        },
        render: function ()
        {
            var attrs = _.clone(this.model.attributes);
            attrs.fieldName = this.props.get('fieldName');
            this.$el.html(this.template(attrs));
            return this;
        }
    });


    /**
     * Initialize media fields
     * @return void
     */
    function initMediaField()
    {
        new MediaField({input: this, el: $(this).siblings('div.mashsb-rwmb-media-view')});
    }


    $(':input.mashsb-rwmb-file_advanced').each(initMediaField);
    $('.mashsb-rwmb-input')
            .on('clone', ':input.mashsb-rwmb-file_advanced', initMediaField);
});


function mashsb_reset_remove(elem){
    console.log(elem);
    jQuery(elem.el).prev().css({'height':'100%'});
    jQuery(elem.el).prev().css({'background-image':'none'});
    jQuery(elem.el).prev().css({'background-color':'#fff'});
    
    //var og_image_width = jQuery('#mashsb_meta .mashsb-rwmb-media-view').width();
    //var og_image_height = og_image_width * (1 / 1.91);
    
    //jQuery(elem).parent('mashsb-rwmb-media-list ui-sortable').css({'background-image':'http://src.wordpress-develop.dev/wp-content/plugins/mashsharer/assets/images/og_placeholder_1200_627_v2.png'});
    //jQuery(elem).parent('mashsb-rwmb-media-list ui-sortable').css({'height': og_image_height});
    //jQuery(elem).prepend('<style>#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-og-image .mashsb-rwmb-input ul{height:' + og_image_height + 'px;}</style>');


}
function mashsb_reset_new(elem){
    //console.log(jQuery(elem.el).find('ul'));
    selector = jQuery(elem.el).children('ul');
    
    selector.css({'height':'100%'});
    jQuery(elem.el.firstChild).css({'background-image':'none'});
    jQuery(elem.el.firstChild).css({'background-color':'#fff'});
}

/*function mashsb_reset_after_delete(elem){
    
    elem_new = elem.list.el.className;

    console.log('mashsb_reset_after_delete');
    console.log(elem_new);
    jQuery('.' + elem_new).css({'height':'100%'});
    jQuery(elem).css({'background-image':''});
    jQuery(elem).css({'background-color':'#fff'});
    
}*/

// Function for OG Title Counting
function smTitleRemaining() {
    var smTitle = jQuery('#mashsb_meta .mashsb-og-title textarea').val();
    console.log(smTitle);
    var remaining = 60 - smTitle.length;
    if (smTitle.length > 0 && remaining >= 0) {
        jQuery('#mashsb_meta .mashsb-og-title .mashsb_counter').removeClass('mashsb_exceeded');
    } else if (smTitle.length > 0 && remaining < 0) {
        jQuery('#mashsb_meta .mashsb-og-title .mashsb_counter').addClass('mashsb_exceeded');
    } else {
        jQuery('#mashsb_meta .mashsb-og-title .mashsb_counter').removeClass('mashsb_exceeded');
    }
    jQuery('#mashsb_meta .mashsb-og-title .mashsb_remaining').html(remaining);
}

// Function for SM Description Counting
function smDescriptionRemaining() {
    var smDescription = jQuery('#mashsb_meta .mashsb-og-desc textarea').val();
    var remaining = 160 - smDescription.length;
    if (smDescription.length > 0 && remaining >= 0) {
        jQuery('#mashsb_meta .mashsb-og-desc .mashsb_counter').removeClass('mashsb_exceeded');
    } else if (smDescription.length > 0 && remaining < 0) {
        jQuery('#mashsb_meta .mashsb-og-desc .mashsb_counter').addClass('mashsb_exceeded');
    } else {
        jQuery('#mashsb_meta .mashsb-og-desc .mashsb_counter').removeClass('mashsb_exceeded');
    }
    jQuery('#mashsb_meta .mashsb-og-desc .mashsb_remaining').html(remaining);
}

// Function for Twitter Box Counting
function twitterRemaining() {
    var smTwitter = jQuery('#mashsb_meta .mashsb-custom-tweet textarea').val();
    //var handle = jQuery('#mashsb_meta .twitterID label').html();
    if (smTwitter.indexOf('http') > -1) {
        linkSpace = 0;
        jQuery('.tweetLinkSection').css({'text-decoration': 'line-through'});
    } else {
        linkSpace = 23;
        jQuery('.tweetLinkSection').css({'text-decoration': 'none'});
    }
    ;
    if (typeof handle === 'undefined') {
        var remaining = 140 - getTweetLength(smTwitter) - linkSpace;
    } else {
        //var remaining = 140 - getTweetLength(smTwitter) - handle.length - linkSpace - 6;
        var remaining = 140 - getTweetLength(smTwitter) - linkSpace - 6;
    }
    if (smTwitter.length > 0 && remaining >= 0) {
        jQuery('#mashsb_meta .mashsb-custom-tweet .mashsb_counter').removeClass('mashsb_exceeded');
    } else if (smTwitter.length > 0 && remaining < 0) {
        jQuery('#mashsb_meta .mashsb-custom-tweet .mashsb_counter').addClass('mashsb_exceeded');
    } else {
        jQuery('#mashsb_meta .mashsb-custom-tweet .mashsb_counter').removeClass('mashsb_exceeded');
    }
    jQuery('#mashsb_meta .mashsb-custom-tweet .mashsb_remaining').html(remaining);
}

function getTweetLength(input) {
    var tmp = "";
    for (var i = 0; i < 22; i++) {
        tmp += "o"
    }
    return input.replace(/(http:\/\/[\S]*)/g, tmp).length;
}
;

jQuery(function ($) {
    if (jQuery('#mashsb_meta.postbox').length) {

        // counter Social Media Title
        jQuery('#mashsb_meta #mashsb_og_title').after('<div class="mashsb_counter"><span class="mashsb_remaining">60</span> Characters Remaining</div>');

        // counter Social Media Description
        jQuery('#mashsb_meta #mashsb_og_description').after('<div class="mashsb_counter"><span class="mashsb_remaining">150</span> Characters Remaining</div>');

        // counter Twitter Box
        jQuery('#mashsb_meta #mashsb_custom_tweet').after('<div class="mashsb_counter"><span class="mashsb_remaining">118</span> Characters Remaining</div>');

        smTitleRemaining();
        jQuery('#mashsb_meta .mashsb-og-title textarea').on('input', function () {
            smTitleRemaining();
        });

        smDescriptionRemaining();
        jQuery('#mashsb_meta .mashsb-og-desc textarea').on('input', function () {
            smDescriptionRemaining();
        });

        twitterRemaining();
        jQuery('#mashsb_custom_tweet').on('input', function () {
            twitterRemaining();
        });


        //var og_image_width = jQuery('#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-og-image .mashsb-rwmb-input').width(); 
        var og_image_width = jQuery('#mashsb_meta .mashsb-rwmb-media-view').width();
        var og_image_height = og_image_width * (1 / 1.91);

        var pinterest_width = jQuery('#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-pinterest-image .mashsb-rwmb-input').width();
        var pinterest_height = pinterest_width * (3 / 2);

        jQuery('#mashsb_meta').prepend('<style>#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-og-image .mashsb-rwmb-input ul{height:' + og_image_height + 'px;}</style>');
        jQuery('#mashsb_meta').prepend('<style>#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-pinterest-image .mashsb-rwmb-input ul{height:' + pinterest_height + 'px;}</style>');


    };
});
