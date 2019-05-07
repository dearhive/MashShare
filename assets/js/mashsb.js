var strict;

jQuery(document).ready(function ($) {

    /* Show Whatsapp button on mobile devices iPhones and Android only */
    if (navigator.userAgent.match(/(iPhone)/i) || navigator.userAgent.match(/(Android)/i)) {
        $('.mashicon-whatsapp').show();
    }

    /**
     * Get facebook share count vi js client request
     * 
     * @returns {undefined}
     * 
     * not used any longer
     */
    var mashsb_get_fb_shares = function ()
    {


        if (document.querySelector('.mashsb-buttons') === null) {
            return false;
        }

        if ('undefined' !== typeof (mashsb.refresh) && mashsb.refresh === '0') {
            return false;
        }

        if ('undefined' === typeof (mashsb.share_url) && mashsb.share_url !== '') {
            return false;
        }

        if ('undefined' === typeof (mashsb.postid) && mashsb.postid !== '') {
            return false;
        }

        if (mashsb_is_rate_limit()) {
            return false;
        }

        //mashsb.share_url = 'https://www.google.de';

        var facebookGraphURL = 'https://graph.facebook.com/?id=' + mashsb.share_url;
        $.ajax({
            type: 'GET',
            url: facebookGraphURL,
            dataType: 'json',
            success: function (data) {
                mashsb_set_fb_sharecount(data);
                console.log(data);
            },
            error: function (e) {
                console.log(e)
            }
        })


    }
    // Make sure page has been loaded completely before requesting any shares via ajax
    // This also prevents hitting the server too often
    //setTimeout(mashsb_get_fb_shares, 3000);

    /**
     * If page is older than 30 second it's cached. So do not call FB API again 
     * @returns {Boolean}
     */
    function mashsb_is_rate_limit() {

        if ("undefined" === typeof (mashsb.servertime)) {
            return true;
        }

        var serverTime = Number(mashsb.servertime);
        var clientTime = Math.floor(Date.now() / 1000);

        if (clientTime > (serverTime + 30)) {
            console.log('rate limited: ' + (serverTime + 30));
            return true;
        } else {
            console.log('not rate limited: ' + (serverTime + 30));
            return false;
        }
    }

    /**
     * Store FB return data in mashshare cache vi js client request
     * @returns {undefined}
     */
    function mashsb_set_fb_sharecount(result) {

        if ('undefined' === typeof (result.share)) {
            console.log('No valid result' + result);
            return false;
        }

        var data = {
            action: 'mashsb_set_fb_shares',
            shares: result.share,
            postid: mashsb.postid,
            url: mashsb.share_url,
            nonce: mashsb.nonce
        }

        $.ajax({
            type: "post",
            url: mashsb.ajaxurl,
            data: data,
            success: function (res) {
                console.log('Save fb results: ' + res);
            },
            error: function (e) {
                console.log('Unknown error ' + e)
            }
        })
    }


    // pinterest button logic
    $('body')
            .off('click', '.mashicon-pinterest')
            .on('click', '.mashicon-pinterest', function (e) {
                e.preventDefault();
                console.log('preventDefault:' + e);
                winWidth = 520;
                winHeight = 350;
                var winTop = (screen.height / 2) - (winHeight / 2);
                var winLeft = (screen.width / 2) - (winWidth / 2);
                var url = $(this).attr('data-mashsb-url');

                window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight + ',resizable=yes');

            });

    /* Load Pinterest Popup window
     * 
     * @param string html container
     * @returns void
     */
    function load_pinterest(html) {

        mashnet_load_pinterest_body();

        jQuery('.mashnet_pinterest_header').fadeIn(500);
        jQuery('.mashnet_pinterest_inner').html(html);

        /* Close Pinterest popup*/
        jQuery('.mashnet_pinterest_close').click(function (e) {
            e.preventDefault();
            jQuery('.mashnet_pinterest_header').hide();
        });
    }

    /**
     * Load pinterest wrapper
     * 
     * @returns voids
     */
    function load_pinterest_body() {
        var winWidth = window.innerWidth;
        var popupWidth = 350;
        var popupHeight = 310;

        /* Load Pinterest popup into body of page */
        if (winWidth <= 330)
            var popupWidth = 310;
        if (winWidth > 400)
            var popupWidth = 390;
        if (winWidth > 500)
            var popupWidth = 490;

        var winTop = (window.innerHeight / 2) - (popupHeight / 2);
        var winLeft = (window.innerWidth / 2) - (popupWidth / 2);
        var struct = '<div class="mashnet_pinterest_header" style="position:fixed;z-index:999999;max-width:' + popupWidth + 'px; margin-left:' + winLeft + 'px;top:' + winTop + 'px;">\n\
                        <div class="mashnet_pinit_wrapper" style="background-color:white;"><span class="mashnet_pin_it">Pin it! </span><span class="mashnet_pinicon"></span> \n\
<div class="mashnet_pinterest_close" style="float:right;"><a href="#">X</a></div></div>\n\
<div class="mashnet_pinterest_inner"></div>\n\
                </div>\n\
                ';

        jQuery('body').append(struct);
    }

    /* Get all images on site 
     * 
     * @return html
     * */
    function get_images(url) {

        var allImages = jQuery('img').not("[nopin='nopin']");
        var html = '';
        var url = '';

        var largeImages = allImages.filter(function () {
            return (jQuery(this).width() > 70) || (jQuery(this).height() > 70)
        })
        for (i = 0; i < largeImages.length; i++) {
            html += '<li><a target="_blank" rel="" id="mashnetPinterestPopup" href="https://pinterest.com/pin/create/button/?url=' + encodeURIComponent(window.location.href) + '%2F&media=' + largeImages[i].src + '&description=' + largeImages[i].alt + '"><img src="' + largeImages[i].src + '"></a></li>';
        }
    }




    // Fix for the inline post plugin which removes the zero share count
    if ($('.mashsbcount').text() == '') {
        $('.mashsbcount').text(0);
    }

    // check the sharecount caching method
    mashsb_check_cache();

    /**
     * Check Cache
     *
     */
    function mashsb_check_cache() {
        
        if (mashsb_is_rate_limit()) {
            return false;
        }
        
        setTimeout(function () {
            if (typeof (mashsb) && mashsb.refresh == "1") {
                mashsb_update_cache();
            }

        }, 6000);
    }

    function mashsb_update_cache() {
        var mashsb_url = window.location.href;
        if (mashsb_url.indexOf("?") > -1) {
            mashsb_url += "&mashsb-refresh";
        } else {
            mashsb_url += "?mashsb-refresh";
        }
        var xhr = new XMLHttpRequest();
        xhr.open("GET", mashsb_url, true);
        xhr.send();
    }

    /* Opens a new minus button when plus sign is clicked */
    /* Toogle function for more services */
    $('.onoffswitch').on('click', function () {
        var $parent = $(this).parents('.mashsb-container');
        $parent.find('.onoffswitch').hide();
        $parent.find('.secondary-shares').show();
        $parent.find('.onoffswitch2').show();
    });
    $('.onoffswitch2').on('click', function () {
        var $parent = $(this).parents('.mashsb-container');
        $parent.find('.onoffswitch').show();
        $parent.find('.secondary-shares').hide();
    });

    /* Network sharer scripts */
    /* deactivate FB sharer when likeaftershare is enabled */
    if (typeof lashare_fb == "undefined" && typeof mashsb !== 'undefined') {
        $('.mashicon-facebook').click(function (e) {
            winWidth = 520;
            winHeight = 550;
            var winTop = (screen.height / 2) - (winHeight / 2);
            var winLeft = (screen.width / 2) - (winWidth / 2);
            var url = $(this).attr('href');

            window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
            e.preventDefault();
            return false;
        });
    }

    if (typeof mashsb !== 'undefined') {
        $('.mashicon-twitter').click(function (e) {
            winWidth = 520;
            winHeight = 350;
            var winTop = (screen.height / 2) - (winHeight / 2);
            var winLeft = (screen.width / 2) - (winWidth / 2);
            var url = $(this).attr('href');

            // deprecated and removed because TW popup opens twice
            if (mashsb.twitter_popup === '1') {
                window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
            }
            e.preventDefault();
            return false;
        });
    }

    if (typeof mashsb !== 'undefined' && mashsb.subscribe === 'content') {
        /* Toogle container display:none */
        $('.mashicon-subscribe').not('.trigger_active').nearest('.mashsb-toggle-container').hide();
        $('.mashicon-subscribe').click(function () {
            var trig = $(this);
            if (trig.hasClass('trigger_active')) {
                $(trig).nearest('.mashsb-toggle-container').slideToggle('fast');
                trig.removeClass('trigger_active');
                //$(".mashicon-subscribe").css({"padding-bottom":"10px"});
            } else {
                $('.trigger_active').nearest('.mashsb-toggle-container').slideToggle('slow');
                $('.trigger_active').removeClass('trigger_active');
                $(trig).nearest('.mashsb-toggle-container').slideToggle('fast');
                trig.addClass('trigger_active');
                //$(".mashicon-subscribe").css({"padding-bottom":"13px"});
            }
            ;
            return false;
        });
    }

    if (typeof mashsb !== 'undefined' && mashsb.subscribe === 'link') {
        $('.mashicon-subscribe').click(function () {
            var href = mashsb.subscribe_url;
            $(this).attr("href", href);
        });
    }
    ;


    /* Round the shares callback function
     * 
     * @param {type} value
     * @returns {String|@exp;value@call;toFixed}
     */
    function roundShares(value) {
        if (typeof mashsb !== "undefined" && mashsb.round_shares == 1) {
            if (value > 1000000) {
                shares = Math.round((value / 1000000) * 10) / 10 + 'M';
                return shares;

            }
            if (value > 1000) {
                shares = Math.round((value / 1000) * 10) / 10 + 'k';
                return shares;

            }
        }
        /* zero decimals */
        return value.toFixed(0);
    }



    /* Count up script jquery-countTo
     * by mhuggins
     * 
     * Source: https://github.com/mhuggins/jquery-countTo
     */
    (function ($) {
        $.fn.countTo = function (options) {
            options = options || {};

            return $(this).each(function () {
                // set options for current element
                var settings = $.extend({}, $.fn.countTo.defaults, {
                    from: $(this).data('from'),
                    to: $(this).data('to'),
                    speed: $(this).data('speed'),
                    refreshInterval: $(this).data('refresh-interval'),
                    decimals: $(this).data('decimals')
                }, options);

                // how many times to update the value, and how much to increment the value on each update
                var loops = Math.ceil(settings.speed / settings.refreshInterval),
                        increment = (settings.to - settings.from) / loops;

                // references & variables that will change with each update
                var self = this,
                        $self = $(this),
                        loopCount = 0,
                        value = settings.from,
                        data = $self.data('countTo') || {};

                $self.data('countTo', data);

                // if an existing interval can be found, clear it first
                if (data.interval) {
                    clearInterval(data.interval);
                }
                data.interval = setInterval(updateTimer, settings.refreshInterval);

                // initialize the element with the starting value
                render(value);

                function updateTimer() {
                    value += increment;
                    loopCount++;

                    render(value);

                    if (typeof (settings.onUpdate) == 'function') {
                        settings.onUpdate.call(self, value);
                    }

                    if (loopCount >= loops) {
                        // remove the interval
                        $self.removeData('countTo');
                        clearInterval(data.interval);
                        value = settings.to;

                        if (typeof (settings.onComplete) == 'function') {
                            settings.onComplete.call(self, value);
                        }
                    }
                }

                function render(value) {
                    var formattedValue = settings.formatter.call(self, value, settings);
                    $self.text(formattedValue);
                }
            });
        };

        $.fn.countTo.defaults = {
            from: 0, // the number the element should start at
            to: 0, // the number the element should end at
            speed: 1000, // how long it should take to count between the target numbers
            refreshInterval: 100, // how often the element should be updated
            decimals: 0, // the number of decimal places to show
            //formatter: formatter,  // handler for formatting the value before rendering
            formatter: roundShares,
            onUpdate: null, // callback method for every time the element is updated
            onComplete: null       // callback method for when the element finishes updating
        };

        function formatter(value, settings) {
            return value.toFixed(settings.decimals);
        }


    }(jQuery));

    /* 
     * Start the counter
     * 
     */
    if (typeof mashsb !== 'undefined' && mashsb.animate_shares == 1 && $('.mashsbcount').length) {
        $('.mashsbcount').countTo({from: 0, to: mashsb.shares, speed: 1000, refreshInterval: 100});
    }
});


/*!------------------------------------------------------
 * jQuery nearest v1.0.3
 * http://github.com/jjenzz/jQuery.nearest
 * ------------------------------------------------------
 * Copyright (c) 2012 J. Smith (@jjenzz)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
(function ($, d) {
    $.fn.nearest = function (selector) {
        var self, nearest, el, s, p,
                hasQsa = d.querySelectorAll;

        function update(el) {
            nearest = nearest ? nearest.add(el) : $(el);
        }

        this.each(function () {
            self = this;

            $.each(selector.split(','), function () {
                s = $.trim(this);

                if (!s.indexOf('#')) {
                    // selector starts with an ID
                    update((hasQsa ? d.querySelectorAll(s) : $(s)));
                } else {
                    // is a class or tag selector
                    // so need to traverse
                    p = self.parentNode;
                    while (p) {
                        el = hasQsa ? p.querySelectorAll(s) : $(p).find(s);
                        if (el.length) {
                            update(el);
                            break;
                        }
                        p = p.parentNode;
                    }
                }
            });

        });

        return nearest || $();
    };
}(jQuery, document));
