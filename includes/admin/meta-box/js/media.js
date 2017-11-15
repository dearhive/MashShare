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


function mashsb_reset_remove(elem) {
    console.log(elem);
    jQuery(elem.el).prev().css({'height': '100%'});
    jQuery(elem.el).prev().css({'background-image': 'none'});
    jQuery(elem.el).prev().css({'background-color': '#fff'});
}
function mashsb_reset_new(elem) {
    selector = jQuery(elem.el).children('ul');

    selector.css({'height': '100%'});
    jQuery(elem.el.firstChild).css({'background-image': 'none'});
    jQuery(elem.el.firstChild).css({'background-color': '#fff'});
}

/**
 * Remaining og:title characters
 * 
 * @returns jQuery
 */
function mashsb_remaining_og_title() {
    var og_title = jQuery('#mashsb_meta .mashsb-og-title textarea').val();
    var remaining = 95 - og_title.length;
    if (og_title.length > 0 && remaining >= 0) {
        jQuery('#mashsb_meta .mashsb-og-title .mashsb_counter').removeClass('mashsb_exceeded');
    } else if (og_title.length > 0 && remaining < 0) {
        jQuery('#mashsb_meta .mashsb-og-title .mashsb_counter').addClass('mashsb_exceeded');
    } else {
        jQuery('#mashsb_meta .mashsb-og-title .mashsb_counter').removeClass('mashsb_exceeded');
    }
    jQuery('#mashsb_meta .mashsb-og-title .mashsb_remaining').html(remaining);
}
/**
 * Remaining og:description characters
 * 
 * @returns jQuery
 */
function mashsb_remaining_og_desc() {
    var og_desc = jQuery('#mashsb_meta .mashsb-og-desc textarea').val();
    var remaining = 297 - og_desc.length;
    if (og_desc.length > 0 && remaining >= 0) {
        jQuery('#mashsb_meta .mashsb-og-desc .mashsb_counter').removeClass('mashsb_exceeded');
    } else if (og_desc.length > 0 && remaining < 0) {
        jQuery('#mashsb_meta .mashsb-og-desc .mashsb_counter').addClass('mashsb_exceeded');
    } else {
        jQuery('#mashsb_meta .mashsb-og-desc .mashsb_counter').removeClass('mashsb_exceeded');
    }
    jQuery('#mashsb_meta .mashsb-og-desc .mashsb_remaining').html(remaining);
}

/**
 * Return the length of tweet 
 * included a 23 character shortened url
 * 
 * @param string input
 * @returns int
 */
function mashsb_get_tweet_length(input) {
    var url = input.replace(/\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/gi, '.......................');
    return url.length;
}

jQuery(function ($) {

    /**
     * Remaining twitter characters
     *
     * @returns jQuery
     */
    function mashsb_remaining_twitter() {
        var tweet = $('#mashsb_meta .mashsb-custom-tweet textarea').val();
        var handle = $('#mashsb_twitter_handle').val();
        var shortened_post_url = 23; // Twitter is shortening all urls to 23 characters

        if (handle !== 'undefined' && handle.length > 0) {
            var remaining = 280 - mashsb_get_tweet_length(tweet) - handle.length - shortened_post_url;
        } else {
            var remaining = 280 - mashsb_get_tweet_length(tweet) - shortened_post_url;
        }
        if (tweet.length > 0 && remaining >= 0) {
            $('#mashsb_meta .mashsb-custom-tweet .mashsb_counter').removeClass('mashsb_exceeded');
        }
        if (tweet.length > 0 && remaining < 0) {
            $('#mashsb_meta .mashsb-custom-tweet .mashsb_counter').addClass('mashsb_exceeded');
        } else {
            $('#mashsb_meta .mashsb-custom-tweet .mashsb_counter').removeClass('mashsb_exceeded');
        }
        $('#mashsb_meta .mashsb-custom-tweet .mashsb_remaining').html(remaining);
    }


    if ($('#mashsb_meta.postbox').length) {

        // counter Social Media Title
        $('#mashsb_meta #mashsb_og_title').after('<div class="mashsb_counter"><span class="mashsb_remaining">60</span> Characters Remaining</div>');

        // counter Social Media Description
        $('#mashsb_meta #mashsb_og_description').after('<div class="mashsb_counter"><span class="mashsb_remaining">150</span> Characters Remaining</div>');

        // counter Twitter Box
        $('#mashsb_meta #mashsb_custom_tweet').after('<div class="mashsb_counter"><span class="mashsb_remaining">118</span> Characters Remaining</div>');


        mashsb_remaining_og_title();
        $('#mashsb_meta .mashsb-og-title textarea').on('input', function () {
            mashsb_remaining_og_title();
        });

        mashsb_remaining_og_desc();
        $('#mashsb_meta .mashsb-og-desc textarea').on('input', function () {
            mashsb_remaining_og_desc();
        });

        mashsb_remaining_twitter();
        $('#mashsb_custom_tweet').on('input', function () {
            mashsb_remaining_twitter();
        });


        var og_image_width = $('#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-og-image .mashsb-rwmb-input').width();
        var og_image_height = og_image_width * (1 / 1.91);

        var pinterest_width = $('#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-pinterest-image .mashsb-rwmb-input').width();
        var pinterest_height = pinterest_width * (3 / 2);

        $('#mashsb_meta').prepend('<style>#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-og-image .mashsb-rwmb-input ul{height:' + og_image_height + 'px;}</style>');
        $('#mashsb_meta').prepend('<style>#mashsb_meta .mashsb-rwmb-field.mashsb-rwmb-image_advanced-wrapper.mashsb-pinterest-image .mashsb-rwmb-input ul{height:' + pinterest_height + 'px;}</style>');

    }


    // show / hide helper description
    $('.mashsb-helper').click(function (e) {
        e.preventDefault();
        var icon = $(this),
                bubble = $(this).next();

        // Close any that are already open
        $('.mashsb-message').not(bubble).hide();

        var position = icon.position();
        if (bubble.hasClass('bottom')) {
            bubble.css({
                'left': (position.left - bubble.width() / 2) + 'px',
                'top': (position.top + icon.height() + 9) + 'px'
            });
        } else {
            bubble.css({
                'left': (position.left + icon.width() + 9) + 'px',
                'top': (position.top + icon.height() / 2 - 18) + 'px'
            });
        }

        bubble.toggle();
        e.stopPropagation();
    });

    $('body').click(function () {
        $('.mashsb-message').hide();
    });

    $('.mashsb-message').click(function (e) {
        e.stopPropagation();
    });

}(jQuery));
