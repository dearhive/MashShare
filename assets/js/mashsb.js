var strict;

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

jQuery(document).ready(function ($) {

    mashsb_check_cache();

    /**
     * Check Cache
     *
     */
    function mashsb_check_cache() {
        setTimeout(function () {
            if (typeof(mashsb) && mashsb.refresh == "1") {
                mashsb_update_cache();
                //console.log('Cache will be updated');
            }

        }, 6000);
    }

    /**
     *
     * Deprecated
     */
    /*if (typeof('mashsb') && mashsb.restapi == "1"){
     mashsb_restapi_check_cache();
     }
     else if (typeof('mashsb') && mashsb.restapi == "0"){
     mashsb_check_cache_ajax();
     }*/
    /**
     * Check Cache via ajax endpoint
     *
     */
    function mashsb_check_cache_ajax() {

        setTimeout(function () {

            var data = {
                action: 'mashsb_refresh_cache',
            };
            $.post(ajaxurl, data, function (resp, status, xhr) {
                if (resp == "1") {
                    mashsb_update_cache();
                    //console.log('cache must be updated ' + + xhr.status + ' ' + xhr.statusText + xhr.statusText);
                }
            }).fail(function (xhr) {
                console.log('Fatal Error:' + xhr.status + ' ' + xhr.statusText + xhr.statusText);
            });
        }, 4000);
    }
    /**
     * Check Cache via rest api
     *
     */
    function mashsb_restapi_check_cache() {

        setTimeout(function () {

            var data = {};
            var mash_rest_url = 'http://src.wordpress-develop.dev/wp-json/mashshare/v1/verifycache/';
            $.get(mash_rest_url, data, function (resp, status, xhr) {
                if (resp == "1") {
                    mashsb_update_cache();
                    //console.log('cache must be updated ');
                }
            }).fail(function (xhr) {
                console.log('Fatal Error:' + xhr.status + ' ' + xhr.statusText + xhr.statusText);
            });
        }, 4000);
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
        //console.log('Update Cache');
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
        $('.mashicon-facebook').click(function (mashfb) {

            winWidth = 520;
            winHeight = 550;
            var winTop = (screen.height / 2) - (winHeight / 2);
            var winLeft = (screen.width / 2) - (winWidth / 2);
            var url = $(this).attr('href');

            window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
            mashfb.preventDefault(mashfb);
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
        jQuery('.mashicon-subscribe').not('.trigger_active').nearest('.mashsb-toggle-container').hide();
        jQuery('.mashicon-subscribe').click(function () {
            var trig = jQuery(this);
            if (trig.hasClass('trigger_active')) {
                jQuery(trig).nearest('.mashsb-toggle-container').slideToggle('fast');
                trig.removeClass('trigger_active');
                //jQuery(".mashicon-subscribe").css({"padding-bottom":"10px"});
            } else {
                jQuery('.trigger_active').nearest('.mashsb-toggle-container').slideToggle('slow');
                jQuery('.trigger_active').removeClass('trigger_active');
                jQuery(trig).nearest('.mashsb-toggle-container').slideToggle('fast');
                trig.addClass('trigger_active');
                //jQuery(".mashicon-subscribe").css({"padding-bottom":"13px"});
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

    /**
     * Responsive Buttons
     */
    function responsiveButtons()
    {
        // Responsive buttons are not in use
        if (mashsb.dynamic_buttons != 1) return;

        // Ajax Listener
        var ajaxListener                        = {},
            interval                            = {};
        //$primaryButtons                     = $("aside.mashsb-container.mashsb-main > .mashsb-box > .mashsb-buttons > a[class^='mashicon-']:visible:not(.secondary-shares a)"),
        //$secondaryShareButtonsContainer     = $("aside.mashsb-container .secondary-shares");

        // Added listener so in case if somehow the ajax request is being made, the buttons will resize again.
        // This is useful for good reasons for example;
        // 1. No need to include responsiveButtons() in case if anything changes or ajax request needs to be added
        // or modified.
        // 2. If the ajax request is done outside of MashShare work such as theme customisations
        ajaxListener.open       = XMLHttpRequest.prototype.open;
        ajaxListener.send       = XMLHttpRequest.prototype.send;
        ajaxListener.callback   = function (pointer) {
            // Request is not completed yet
            if (pointer.readyState != 4 || pointer.status != 200) {
                return;
            }

            var action = getAction(pointer.responseURL);

            // Re-calculate the width of the buttons on Get View ajax call
            if (action === "mashpv_get_views") {
                console.log("Get views is called");
                // Adjust for animation
                setTimeout(function() {
                    console.log("calling calculate");
                    calculate();
                }, 1100);
            }

            //console.log(interval);
            // Clear the interval for it
            clearInterval(interval[action]);
        };

        // Executes 5 min later to clear IF any interval that's left
        setTimeout(function() {
            var key;
            for (key in interval) {
                if (interval.hasOwnProperty(key)) {
                    clearInterval(interval[key]);
                }
            }

        }, 5 * (60 * 1000));

        // When an ajax requests is opened
        XMLHttpRequest.prototype.open = function(method, url) {
            // In case if they are not defined
            if (!method) method = '';
            if (!url) url = '';

            // Attach values
            ajaxListener.open.apply(this, arguments);
            ajaxListener.method = method;
            ajaxListener.url = url;

            // If that's the get method, attach data to our listener
            if (method.toLowerCase() === "get") {
                ajaxListener.data   = url.split('?');
                ajaxListener.data   = ajaxListener.data[1];
                ajaxListener.action = getAction(ajaxListener.data);
            }
        };

        // When an ajax request is sent
        XMLHttpRequest.prototype.send = function(data, params) {
            ajaxListener.send.apply(this, arguments);

            // If that's the post method, attach data to our listener
            if (ajaxListener.method.toLowerCase() === "post") {
                ajaxListener.data   = data;
                ajaxListener.action = getAction(ajaxListener.data);
            }

            // jQuery overwrites onstatechange (darn you jQuery!),
            // we need to monitor readyState and the status
            var pointer     = this;
            interval[ajaxListener.action] = window.setInterval(ajaxListener.callback, 100, pointer);
        };

        // Recalculate width of the buttons when plus / minus button is clicked
        $("body")
            .on("click", ".onoffswitch", function() {
                //$secondaryShareButtonsContainer.css("display","block");
                setTimeout(function() {calculate();}, 200);
            })
            .on("click", ".onoffswitch2", function() {
                calculate();
            });

        // Window resize
        $(window).resize(function() {
            calculate();
        });

        // When there is no ajax call, this one is required to be here!
        // No worries though, once ajax call is done, it will adjust
        // Adjustment for animation
        if (mashsb.animate_shares == 1) {
            setTimeout(function() {
                calculate();
            }, 500);
        }
        // No need animation adjusting
        else calculate();

        /**
         * Calculation for buttons
         */
        function calculate()
        {
            var $container = $("aside.mashsb-container.mashsb-main");
            console.log("calculating...");

            if ($container.length > 0) {
                $container.each(function() {
                    var $this           = $(this),
                        $primaryButtons = $this.find(".mashsb-box > .mashsb-buttons > .mashsb-primary-shares > a[class^='mashicon-']");

                    // Variables
                    var averageWidth = getAverageWidth($primaryButtons);

                    // Do the styling...
                    $primaryButtons.css({
                        //"width"             : averageWidth + "px", // Need to de-activate this for long labels
                        "min-width"         : averageWidth + "px",
                        // Below this part is just to ensure the stability...
                        // Not all themes are apparently adding these rules
                        // thus messing up the whole width of the elements
                        "box-sizing"        : "border-box",
                        "-moz-box-sizing"   : "border-box",
                        "-webkit-box-sizing": "border-box"
                    });
                });
            }
        }

        /**
         * Get action from URL string
         * @param data
         * @returns {*}
         */
        function getAction(data)
        {
            // Split data
            data = data.split('&');

            // Let's work our magic here
            // Split data
            var dataLength  = data.length,
                i;

            if (dataLength == 1) return data[0];

            // Get the action
            for (i = 0; i < dataLength; i++) {
                if (data[i].startsWith("action=")) {
                    return data[i].replace("action=", '');
                }
            }

            return '';
        }

        /**
         * Floors / rounds down given number to its closest with allowed decimal points
         * @param number
         * @param decimals
         * @returns {number}
         */
        function floorDown(number, decimals)
        {
            decimals = decimals || 0;
            return ( Math.floor( number * Math.pow(10, decimals) ) / Math.pow(10, decimals) );
        }

        /**
         * Rounds up given number to is closest with allowed decimal points
         * @param number
         * @param decimals
         * @returns {number}
         */
        function round(number, decimals)
        {
            return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
        }

        /**
         * Gets average widht of each primary button
         * @returns {number|*}
         */
        function getAverageWidth(primaryButtons)
        {
            // Variables
            var $mashShareContainer             = primaryButtons.parents("aside.mashsb-container.mashsb-main"),
                $container                      = $mashShareContainer.find(".mashsb-buttons > .mashsb-primary-shares"),
                $shareCountContainer            = $mashShareContainer.find(".mashsb-box > .mashsb-count:not(.mashpv)"),
                isShareCountContainerVisible    = ($shareCountContainer.length > 0 && $shareCountContainer.is(":visible")),
                $viewCounterContainer           = $mashShareContainer.find(".mashsb-box > .mashpv.mashsb-count"),
                isViewCounterContainerVisible   = $viewCounterContainer.is(":visible"),
                $plusButton                     = $container.find(".onoffswitch"),
                isPlusButtonVisible             = $plusButton.is(":visible"),
                totalUsedWidth                  = 0,
                averageWidth;

            $plusButton.css("margin-right", 0);

            // Share counter is visible
            if (isShareCountContainerVisible === true) {
                var shareCountContainerWidth = parseFloat($shareCountContainer.css("margin-right"));
                if (isNaN(shareCountContainerWidth)) shareCountContainerWidth = 0;
                shareCountContainerWidth = shareCountContainerWidth + $shareCountContainer[0].getBoundingClientRect().width;
                shareCountContainerWidth = round(shareCountContainerWidth, 2);

                totalUsedWidth += shareCountContainerWidth;
            }

            // View counter is visible
            if (isViewCounterContainerVisible === true) {
                var viewCountContainerWidth = parseFloat($viewCounterContainer.css("margin-right"));
                if (isNaN(viewCountContainerWidth)) viewCountContainerWidth = 0;
                viewCountContainerWidth = viewCountContainerWidth + $viewCounterContainer[0].getBoundingClientRect().width;
                viewCountContainerWidth = round(viewCountContainerWidth, 2);

                totalUsedWidth += viewCountContainerWidth;
            }

            // Plus button is visible
            if (isPlusButtonVisible === true) {
                totalUsedWidth += $plusButton[0].getBoundingClientRect().width;
            }

            var tempWidth = $container[0].getBoundingClientRect().width;

            // Calculate average width of each button (including their margins)
            // We need to get precise width of the container, jQuery's width() is rounding up the numbers
            averageWidth = ($container[0].getBoundingClientRect().width - totalUsedWidth) / primaryButtons.length;
            if (isNaN(averageWidth)) {
                console.log("Couldn't calculate average width");
                return;
            }

            // We're only interested in positive numbers
            if (averageWidth < 0) averageWidth = Math.abs(averageWidth);

            // Now get the right width without the margin
            averageWidth = averageWidth - (primaryButtons.first().outerWidth(true) - primaryButtons.first().outerWidth());
            // Floor it down
            averageWidth = floorDown(averageWidth, 2);

            return averageWidth;
        }
    }

    responsiveButtons();

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

    /* Start the counter
     * 
     */
    if (typeof mashsb !== 'undefined' && mashsb.animate_shares == 1 && $('.mashsbcount').length) {
        $('.mashsbcount').countTo({from: 0, to: mashsb.shares, speed: 1000, refreshInterval: 100});
    }
});