jQuery(document).ready(function ($) {

    $('.mashsb-color-box').each(function () {
        // Start colorpicker
        $(this).colpick({
            layout: 'hex',
            submit: 0,
            colorScheme: 'light',
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                $(el).css('border-color', '#' + hex);
                // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                if (!bySetColor)
                    $(el).val(hex);
            }
        }).keyup(function () {
            $(this).colpickSetColor(this.value);
        });
        $(this).colpick({
            layout: 'hex',
            submit: 0,
            colorScheme: 'light',
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                $(el).css('border-color', '#' + hex);
                // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                if (!bySetColor)
                    $(el).val(hex);
            }
        }).keyup(function () {
            $(this).colpickSetColor(this.value);
        });

    });
    

    $('#mashsb_verify_fbtoken').on("click",function(e){
        e.preventDefault();
        if ($('#mashsb_settings\\[fb_access_token_new\\]').val()){
            check_access_token();
        }
    });
    
    /**
     * Check if access token is valid and api returns a valid result
     * 
     * @returns {undefined}
     */
    function check_access_token()
    {
        $.ajax("https://graph.facebook.com/v2.11/?id=http://www.google.com&access_token=" + $('#mashsb_settings\\[fb_access_token_new\\]').val())
            .done(function (e) {  
                
                try {
                    if (e.share.share_count) {
                        $('#mashsb_token_notice').html('<strong>Token valid:</strong> Facebook share count for http://google.com: ' + e.share.share_count )
                    }
                } catch(e) {
                        $('#mashsb_token_notice').html('<span style="color:red;"> <strong>Error:</strong> Access Token Invalid!</span>');
                }    
//                
//                console.log(e);
//                if (e.share.share_count && "undefined" !== typeof (e.share.share_count)){
//                    $('#mashsb_token_notice').html('<strong>Token valid:</strong> Facebook share count for http://google.com: ' + e.share.share_count );
//                } else {
//                    $('#mashsb_token_notice').html('<span style="color:red;"> <strong>Error:</strong> Access Token Invalid!</span>');
//                    //console.log(e);
//                }
//            })
//            .fail(function (e) {
//                $('#mashsb_token_notice').html('<span style="color:red;"> <strong>Error:</strong> Access Token Invalid!</span>');
//                //console.log(e);
            })
    }

        
    $('#mashsb_fb_auth').click(function (e) {
        e.preventDefault();
        winWidth = 520;
        winHeight = 350;
        var winTop = (screen.height / 2) - (winHeight / 2);
        var winLeft = (screen.width / 2) - (winWidth / 2);
        var url = $(this).attr('href');
        mashsb_fb_auth = window.open(url, 'mashsb_fb_auth', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight + ',resizable=yes');
    });
    
//    // Share Color Picker
//    $('.share_color').colpick({
//        layout: 'hex',
//        submit: 0,
//        colorScheme: 'light',
//        onChange: function (hsb, hex, rgb, el, bySetColor) {
//            $(el).css('border-color', '#' + hex);
//            // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
//            if (!bySetColor)
//                $(el).val(hex);
//        }
//    }).mouseup(function () {
//        $(this).colpickSetColor(this.value);
//    });
    
        
    // Toggle Admin Settings Dynamic Button Resize + Button Width
    if ($("#mashsb_settings\\[responsive_buttons\\]").attr('checked')) {
        $("#mashsb_settings\\[button_width\\]").closest('.row').css("display", "none");
    } else {
        $("#mashsb_settings\\[button_width\\]").closest('.row').fadeIn(300).css("display", "table-row");
    }
    $("#mashsb_settings\\[responsive_buttons\\]").click(function () {
        if ($(this).attr('checked')) {
            $("#mashsb_settings\\[button_width\\]").closest('.row').css("display", "none");
        } else {
            $("#mashsb_settings\\[button_width\\]").closest('.row').fadeIn(300).css("display", "table-row");
        }
    })
    

    
    // Activate chosen select boxes
    $(".mashsb-chosen-select").chosen({width: "400px"});

    function mashsb_setCookie(name, value, days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
        }
        else
            var expires = "";
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function mashsb_getCookie(name) {
        var nameEQ = name + "=";

        var ca = document.cookie.split(";");
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0)
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function mashsb_eraseCookie(name) {
        setCookie(name, "", -1);
    }
    
    
    /* Fade in Caching method settings if needed */
    $('#mashsb_settings\\[caching_method\\]').change(function () {
        if ($('#mashsb_settings\\[caching_method\\]').val() === "refresh_loading")
        {
            $('#mashsb_settings\\[mashsharer_cache\\]').closest('.row').fadeIn(300).css("display", "table-row");
        }
        else
        {
            $('#mashsb_settings\\[mashsharer_cache\\]').closest('.row').css("display", "none");
        }
    });


    /*make visible when setting "Refresh on Loading" is used*/
    if ($('#mashsb_settings\\[caching_method\\]').val() === "refresh_loading")
    {
        $('#mashsb_settings\\[mashsharer_cache\\]').closest('.row').fadeIn(300).css("display", "table-row");
    }
    else
    {
        $('#mashsb_settings\\[mashsharer_cache\\]').closest('.row').css("display", "none");
    }

    // Find active tab and set cookie with #ID
    function find_active_tab() {
        var tab = jQuery('.mashsb-tabs.active').find("a").attr("href");
        mashsb_setCookie("mashsb_active_tab", tab);
    }

    // Get last active tab from cookie or return default value
    function mashsb_get_tab_from_cookie() {
        var tab = mashsb_getCookie('mashsb_active_tab');
        if (tab == null) {
            tab = '#mashsb_settingsgeneral_header';
        }
        return tab;
    }


    function mashsb_get_default_array() {
        var tab_addons, tab_licenses;
        var active_sub_tab;

        // If active tab is Add-On Settings return empty defaultTab value
        tab_addons = jQuery('.mashsb.nav-tab-wrapper a.nav-tab-active:nth-child(2)');
        tab_licenses = jQuery('.mashsb.nav-tab-wrapper a.nav-tab-active:nth-child(3)');

        if (tab_addons.length > 0 || tab_licenses.length > 0) {
            return;
        }
        // Return active tab from cookie
        return mashsb_get_tab_from_cookie() + '-nav';
    }

    // Start easytabs()
    if ($(".mashsb-tabs").length) {
        $('#mashsb_container').easytabs({
            animate: true,
            updateHash: true,
            defaultTab: mashsb_get_default_array()
        });
    }

    // Get active tab (Not for Add-On Settings)
    $('#mashsb_container').bind('easytabs:after', function () {
        if (jQuery('.mashsb.nav-tab-wrapper a.nav-tab-active:nth-child(2)').length == 0) {
            find_active_tab();
        }
    });

    if ($(".mashtab").length) {
        $('.tabcontent_container').easytabs({
            animate: true,
        });
    }

// Drag n drop social networks
    $('#mashsb_network_list').sortable({
        items: '.mashsb_list_item',
        opacity: 0.6,
        cursor: 'move',
        axis: 'y',
        update: function () {
            var order = $(this).sortable('serialize') + '&action=mashsb_update_order';
            $.post(ajaxurl, order, function (response) {
                //alert(response);

            });
        }
    });


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

});

/*
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function ($, e, b) {
    var c = "hashchange", h = document, f, g = $.event.special, i = h.documentMode, d = "on" + c in e && (i === b || i > 7);
    function a(j) {
        j = j || location.href;
        return"#" + j.replace(/^[^#]*#?(.*)$/, "$1")
    }
    $.fn[c] = function (j) {
        return j ? this.bind(c, j) : this.trigger(c)
    };
    $.fn[c].delay = 50;
    g[c] = $.extend(g[c], {setup: function () {
            if (d) {
                return false
            }
            $(f.start)
        }, teardown: function () {
            if (d) {
                return false
            }
            $(f.stop)
        }});
    f = (function () {
        var j = {}, p, m = a(), k = function (q) {
            return q
        }, l = k, o = k;
        j.start = function () {
            p || n()
        };
        j.stop = function () {
            p && clearTimeout(p);
            p = b
        };
        function n() {
            var r = a(), q = o(m);
            if (r !== m) {
                l(m = r, q);
                $(e).trigger(c)
            } else {
                if (q !== m) {
                    location.href = location.href.replace(/#.*/, "") + q
                }
            }
            p = setTimeout(n, $.fn[c].delay)
        }
        $.browser.msie && !d && (function () {
            var q, r;
            j.start = function () {
                if (!q) {
                    r = $.fn[c].src;
                    r = r && r + a();
                    q = $('<iframe tabindex="-1" title="empty"/>').hide().one("load", function () {
                        r || l(a());
                        n()
                    }).attr("src", r || "javascript:0").insertAfter("body")[0].contentWindow;
                    h.onpropertychange = function () {
                        try {
                            if (event.propertyName === "title") {
                                q.document.title = h.title
                            }
                        } catch (s) {
                        }
                    }
                }
            };
            j.stop = k;
            o = function () {
                return a(q.location.href)
            };
            l = function (v, s) {
                var u = q.document, t = $.fn[c].domain;
                if (v !== s) {
                    u.title = h.title;
                    u.open();
                    t && u.write('<script>document.domain="' + t + '"<\/script>');
                    u.close();
                    q.location.hash = v
                }
            }
        })();
        return j
    })()
})(jQuery, this);

/*
 * jQuery EasyTabs plugin 3.2.0
 *
 * Copyright (c) 2010-2011 Steve Schwartz (JangoSteve)
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: Thu May 09 17:30:00 2013 -0500
 */
(function ($) {

    $.easytabs = function (container, options) {

        // Attach to plugin anything that should be available via
        // the $container.data('easytabs') object
        var plugin = this,
                $container = $(container),
                defaults = {
                    animate: true,
                    panelActiveClass: "active",
                    tabActiveClass: "active",
                    defaultTab: "li:first-child",
                    animationSpeed: "normal",
                    tabs: "> ul > li",
                    updateHash: true,
                    cycle: false,
                    collapsible: false,
                    collapsedClass: "collapsed",
                    collapsedByDefault: true,
                    uiTabs: false,
                    transitionIn: 'fadeIn',
                    transitionOut: 'fadeOut',
                    transitionInEasing: 'swing',
                    transitionOutEasing: 'swing',
                    transitionCollapse: 'slideUp',
                    transitionUncollapse: 'slideDown',
                    transitionCollapseEasing: 'swing',
                    transitionUncollapseEasing: 'swing',
                    containerClass: "",
                    tabsClass: "",
                    tabClass: "",
                    panelClass: "",
                    cache: true,
                    event: 'click',
                    panelContext: $container
                },
        // Internal instance variables
        // (not available via easytabs object)
        $defaultTab,
                $defaultTabLink,
                transitions,
                lastHash,
                skipUpdateToHash,
                animationSpeeds = {
                    fast: 200,
                    normal: 400,
                    slow: 600
                },
        // Shorthand variable so that we don't need to call
        // plugin.settings throughout the plugin code
        settings;

        // =============================================================
        // Functions available via easytabs object
        // =============================================================

        plugin.init = function () {

            plugin.settings = settings = $.extend({}, defaults, options);
            settings.bind_str = settings.event + ".easytabs";

            // Add jQuery UI's crazy class names to markup,
            // so that markup will match theme CSS
            if (settings.uiTabs) {
                settings.tabActiveClass = 'ui-tabs-selected';
                settings.containerClass = 'ui-tabs ui-widget ui-widget-content ui-corner-all';
                settings.tabsClass = 'ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all';
                settings.tabClass = 'ui-state-default ui-corner-top';
                settings.panelClass = 'ui-tabs-panel ui-widget-content ui-corner-bottom';
            }

            // If collapsible is true and defaultTab specified, assume user wants defaultTab showing (not collapsed)
            if (settings.collapsible && options.defaultTab !== undefined && options.collpasedByDefault === undefined) {
                settings.collapsedByDefault = false;
            }

            // Convert 'normal', 'fast', and 'slow' animation speed settings to their respective speed in milliseconds
            if (typeof (settings.animationSpeed) === 'string') {
                settings.animationSpeed = animationSpeeds[settings.animationSpeed];
            }

            $('a.anchor').remove().prependTo('body');

            // Store easytabs object on container so we can easily set
            // properties throughout
            $container.data('easytabs', {});

            plugin.setTransitions();

            plugin.getTabs();

            addClasses();

            setDefaultTab();

            bindToTabClicks();

            initHashChange();

            initCycle();

            // Append data-easytabs HTML attribute to make easy to query for
            // easytabs instances via CSS pseudo-selector
            $container.attr('data-easytabs', true);
        };

        // Set transitions for switching between tabs based on options.
        // Could be used to update transitions if settings are changes.
        plugin.setTransitions = function () {
            transitions = (settings.animate) ? {
                show: settings.transitionIn,
                hide: settings.transitionOut,
                speed: settings.animationSpeed,
                collapse: settings.transitionCollapse,
                uncollapse: settings.transitionUncollapse,
                halfSpeed: settings.animationSpeed / 2
            } :
                    {
                        show: "show",
                        hide: "hide",
                        speed: 0,
                        collapse: "hide",
                        uncollapse: "show",
                        halfSpeed: 0
                    };
        };

        // Find and instantiate tabs and panels.
        // Could be used to reset tab and panel collection if markup is
        // modified.
        plugin.getTabs = function () {
            var $matchingPanel;

            // Find the initial set of elements matching the setting.tabs
            // CSS selector within the container
            plugin.tabs = $container.find(settings.tabs),
                    // Instantiate panels as empty jquery object
                    plugin.panels = $(),
                    plugin.tabs.each(function () {
                        var $tab = $(this),
                                $a = $tab.children('a'),
                                // targetId is the ID of the panel, which is either the
                                // `href` attribute for non-ajax tabs, or in the
                                // `data-target` attribute for ajax tabs since the `href` is
                                // the ajax URL
                                targetId = $tab.children('a').data('target');

                        $tab.data('easytabs', {});

                        // If the tab has a `data-target` attribute, and is thus an ajax tab
                        if (targetId !== undefined && targetId !== null) {
                            $tab.data('easytabs').ajax = $a.attr('href');
                        } else {
                            targetId = $a.attr('href');
                        }
                        targetId = targetId.match(/#([^\?]+)/)[1];

                        $matchingPanel = settings.panelContext.find("#" + targetId);

                        // If tab has a matching panel, add it to panels
                        if ($matchingPanel.length) {

                            // Store panel height before hiding
                            $matchingPanel.data('easytabs', {
                                position: $matchingPanel.css('position'),
                                visibility: $matchingPanel.css('visibility')
                            });

                            // Don't hide panel if it's active (allows `getTabs` to be called manually to re-instantiate tab collection)
                            $matchingPanel.not(settings.panelActiveClass).hide();

                            plugin.panels = plugin.panels.add($matchingPanel);

                            $tab.data('easytabs').panel = $matchingPanel;

                            // Otherwise, remove tab from tabs collection
                        } else {
                            plugin.tabs = plugin.tabs.not($tab);
                            if ('console' in window) {
                                console.warn('Warning: tab without matching panel for selector \'#' + targetId + '\' removed from set');
                            }
                        }
                    });
        };

        // Select tab and fire callback
        plugin.selectTab = function ($clicked, callback) {
            var url = window.location,
                    hash = url.hash.match(/^[^\?]*/)[0],
                    $targetPanel = $clicked.parent().data('easytabs').panel,
                    ajaxUrl = $clicked.parent().data('easytabs').ajax;

            // Tab is collapsible and active => toggle collapsed state
            if (settings.collapsible && !skipUpdateToHash && ($clicked.hasClass(settings.tabActiveClass) || $clicked.hasClass(settings.collapsedClass))) {
                plugin.toggleTabCollapse($clicked, $targetPanel, ajaxUrl, callback);

                // Tab is not active and panel is not active => select tab
            } else if (!$clicked.hasClass(settings.tabActiveClass) || !$targetPanel.hasClass(settings.panelActiveClass)) {
                activateTab($clicked, $targetPanel, ajaxUrl, callback);

                // Cache is disabled => reload (e.g reload an ajax tab).
            } else if (!settings.cache) {
                activateTab($clicked, $targetPanel, ajaxUrl, callback);
            }

        };

        // Toggle tab collapsed state and fire callback
        plugin.toggleTabCollapse = function ($clicked, $targetPanel, ajaxUrl, callback) {
            plugin.panels.stop(true, true);

            if (fire($container, "easytabs:before", [$clicked, $targetPanel, settings])) {
                plugin.tabs.filter("." + settings.tabActiveClass).removeClass(settings.tabActiveClass).children().removeClass(settings.tabActiveClass);

                // If panel is collapsed, uncollapse it
                if ($clicked.hasClass(settings.collapsedClass)) {

                    // If ajax panel and not already cached
                    if (ajaxUrl && (!settings.cache || !$clicked.parent().data('easytabs').cached)) {
                        $container.trigger('easytabs:ajax:beforeSend', [$clicked, $targetPanel]);

                        $targetPanel.load(ajaxUrl, function (response, status, xhr) {
                            $clicked.parent().data('easytabs').cached = true;
                            $container.trigger('easytabs:ajax:complete', [$clicked, $targetPanel, response, status, xhr]);
                        });
                    }

                    // Update CSS classes of tab and panel
                    $clicked.parent()
                            .removeClass(settings.collapsedClass)
                            .addClass(settings.tabActiveClass)
                            .children()
                            .removeClass(settings.collapsedClass)
                            .addClass(settings.tabActiveClass);

                    $targetPanel
                            .addClass(settings.panelActiveClass)
                            [transitions.uncollapse](transitions.speed, settings.transitionUncollapseEasing, function () {
                        $container.trigger('easytabs:midTransition', [$clicked, $targetPanel, settings]);
                        if (typeof callback == 'function')
                            callback();
                    });

                    // Otherwise, collapse it
                } else {

                    // Update CSS classes of tab and panel
                    $clicked.addClass(settings.collapsedClass)
                            .parent()
                            .addClass(settings.collapsedClass);

                    $targetPanel
                            .removeClass(settings.panelActiveClass)
                            [transitions.collapse](transitions.speed, settings.transitionCollapseEasing, function () {
                        $container.trigger("easytabs:midTransition", [$clicked, $targetPanel, settings]);
                        if (typeof callback == 'function')
                            callback();
                    });
                }
            }
        };


        // Find tab with target panel matching value
        plugin.matchTab = function (hash) {
            return plugin.tabs.find("[href='" + hash + "'],[data-target='" + hash + "']").first();
        };

        // Find panel with `id` matching value
        plugin.matchInPanel = function (hash) {
            return (hash && plugin.validId(hash) ? plugin.panels.filter(':has(' + hash + ')').first() : []);
        };

        // Make sure hash is a valid id value (admittedly strict in that HTML5 allows almost anything without a space)
        // but jQuery has issues with such id values anyway, so we can afford to be strict here.
        plugin.validId = function (id) {
            return id.substr(1).match(/^[A-Za-z][A-Za-z0-9\-_:\.]*$/);
        };

        // Select matching tab when URL hash changes
        plugin.selectTabFromHashChange = function () {
            var hash = window.location.hash.match(/^[^\?]*/)[0],
                    $tab = plugin.matchTab(hash),
                    $panel;

            if (settings.updateHash) {

                // If hash directly matches tab
                if ($tab.length) {
                    skipUpdateToHash = true;
                    plugin.selectTab($tab);

                } else {
                    $panel = plugin.matchInPanel(hash);

                    // If panel contains element matching hash
                    if ($panel.length) {
                        hash = '#' + $panel.attr('id');
                        $tab = plugin.matchTab(hash);
                        skipUpdateToHash = true;
                        plugin.selectTab($tab);

                        // If default tab is not active...
                    } else if (!$defaultTab.hasClass(settings.tabActiveClass) && !settings.cycle) {

                        // ...and hash is blank or matches a parent of the tab container or
                        // if the last tab (before the hash updated) was one of the other tabs in this container.
                        if (hash === '' || plugin.matchTab(lastHash).length || $container.closest(hash).length) {
                            skipUpdateToHash = true;
                            plugin.selectTab($defaultTabLink);
                        }
                    }
                }
            }
        };

        // Cycle through tabs
        plugin.cycleTabs = function (tabNumber) {
            if (settings.cycle) {
                tabNumber = tabNumber % plugin.tabs.length;
                $tab = $(plugin.tabs[tabNumber]).children("a").first();
                skipUpdateToHash = true;
                plugin.selectTab($tab, function () {
                    setTimeout(function () {
                        plugin.cycleTabs(tabNumber + 1);
                    }, settings.cycle);
                });
            }
        };

        // Convenient public methods
        plugin.publicMethods = {
            select: function (tabSelector) {
                var $tab;

                // Find tab container that matches selector (like 'li#tab-one' which contains tab link)
                if (($tab = plugin.tabs.filter(tabSelector)).length === 0) {

                    // Find direct tab link that matches href (like 'a[href="#panel-1"]')
                    if (($tab = plugin.tabs.find("a[href='" + tabSelector + "']")).length === 0) {

                        // Find direct tab link that matches selector (like 'a#tab-1')
                        if (($tab = plugin.tabs.find("a" + tabSelector)).length === 0) {

                            // Find direct tab link that matches data-target (lik 'a[data-target="#panel-1"]')
                            if (($tab = plugin.tabs.find("[data-target='" + tabSelector + "']")).length === 0) {

                                // Find direct tab link that ends in the matching href (like 'a[href$="#panel-1"]', which would also match http://example.com/currentpage/#panel-1)
                                if (($tab = plugin.tabs.find("a[href$='" + tabSelector + "']")).length === 0) {

                                    $.error('Tab \'' + tabSelector + '\' does not exist in tab set');
                                }
                            }
                        }
                    }
                } else {
                    // Select the child tab link, since the first option finds the tab container (like <li>)
                    $tab = $tab.children("a").first();
                }
                plugin.selectTab($tab);
            }
        };

        // =============================================================
        // Private functions
        // =============================================================

        // Triggers an event on an element and returns the event result
        var fire = function (obj, name, data) {
            var event = $.Event(name);
            obj.trigger(event, data);
            return event.result !== false;
        }

        // Add CSS classes to markup (if specified), called by init
        var addClasses = function () {
            $container.addClass(settings.containerClass);
            plugin.tabs.parent().addClass(settings.tabsClass);
            plugin.tabs.addClass(settings.tabClass);
            plugin.panels.addClass(settings.panelClass);
        };

        // Set the default tab, whether from hash (bookmarked) or option,
        // called by init
        var setDefaultTab = function () {
            var hash = window.location.hash.match(/^[^\?]*/)[0],
                    $selectedTab = plugin.matchTab(hash).parent(),
                    $panel;

            // If hash directly matches one of the tabs, active on page-load
            if ($selectedTab.length === 1) {
                $defaultTab = $selectedTab;
                settings.cycle = false;

            } else {
                $panel = plugin.matchInPanel(hash);

                // If one of the panels contains the element matching the hash,
                // make it active on page-load
                if ($panel.length) {
                    hash = '#' + $panel.attr('id');
                    $defaultTab = plugin.matchTab(hash).parent();

                    // Otherwise, make the default tab the one that's active on page-load
                } else {
                    $defaultTab = plugin.tabs.parent().find(settings.defaultTab);
                    if ($defaultTab.length === 0) {
                        $.error("The specified default tab ('" + settings.defaultTab + "') could not be found in the tab set ('" + settings.tabs + "') out of " + plugin.tabs.length + " tabs.");
                    }
                }
            }

            $defaultTabLink = $defaultTab.children("a").first();

            activateDefaultTab($selectedTab);
        };

        // Activate defaultTab (or collapse by default), called by setDefaultTab
        var activateDefaultTab = function ($selectedTab) {
            var defaultPanel,
                    defaultAjaxUrl;

            if (settings.collapsible && $selectedTab.length === 0 && settings.collapsedByDefault) {
                $defaultTab
                        .addClass(settings.collapsedClass)
                        .children()
                        .addClass(settings.collapsedClass);

            } else {

                defaultPanel = $($defaultTab.data('easytabs').panel);
                defaultAjaxUrl = $defaultTab.data('easytabs').ajax;

                if (defaultAjaxUrl && (!settings.cache || !$defaultTab.data('easytabs').cached)) {
                    $container.trigger('easytabs:ajax:beforeSend', [$defaultTabLink, defaultPanel]);
                    defaultPanel.load(defaultAjaxUrl, function (response, status, xhr) {
                        $defaultTab.data('easytabs').cached = true;
                        $container.trigger('easytabs:ajax:complete', [$defaultTabLink, defaultPanel, response, status, xhr]);
                    });
                }

                $defaultTab.data('easytabs').panel
                        .show()
                        .addClass(settings.panelActiveClass);

                $defaultTab
                        .addClass(settings.tabActiveClass)
                        .children()
                        .addClass(settings.tabActiveClass);
            }

            // Fire event when the plugin is initialised
            $container.trigger("easytabs:initialised", [$defaultTabLink, defaultPanel]);
        };

        // Bind tab-select funtionality to namespaced click event, called by
        // init
        var bindToTabClicks = function () {
            plugin.tabs.children("a").bind(settings.bind_str, function (e) {

                // Stop cycling when a tab is clicked
                settings.cycle = false;

                // Hash will be updated when tab is clicked,
                // don't cause tab to re-select when hash-change event is fired
                skipUpdateToHash = false;

                // Select the panel for the clicked tab
                plugin.selectTab($(this));

                // Don't follow the link to the anchor
                e.preventDefault ? e.preventDefault() : e.returnValue = false;
            });
        };

        // Activate a given tab/panel, called from plugin.selectTab:
        //
        //   * fire `easytabs:before` hook
        //   * get ajax if new tab is an uncached ajax tab
        //   * animate out previously-active panel
        //   * fire `easytabs:midTransition` hook
        //   * update URL hash
        //   * animate in newly-active panel
        //   * update CSS classes for inactive and active tabs/panels
        //
        // TODO: This could probably be broken out into many more modular
        // functions
        var activateTab = function ($clicked, $targetPanel, ajaxUrl, callback) {
            plugin.panels.stop(true, true);

            if (fire($container, "easytabs:before", [$clicked, $targetPanel, settings])) {
                var $visiblePanel = plugin.panels.filter(":visible"),
                        $panelContainer = $targetPanel.parent(),
                        targetHeight,
                        visibleHeight,
                        heightDifference,
                        showPanel,
                        hash = window.location.hash.match(/^[^\?]*/)[0];

                if (settings.animate) {
                    targetHeight = getHeightForHidden($targetPanel);
                    visibleHeight = $visiblePanel.length ? setAndReturnHeight($visiblePanel) : 0;
                    heightDifference = targetHeight - visibleHeight;
                }

                // Set lastHash to help indicate if defaultTab should be
                // activated across multiple tab instances.
                lastHash = hash;

                // TODO: Move this function elsewhere
                showPanel = function () {
                    // At this point, the previous panel is hidden, and the new one will be selected
                    $container.trigger("easytabs:midTransition", [$clicked, $targetPanel, settings]);

                    // Gracefully animate between panels of differing heights, start height change animation *after* panel change if panel needs to contract,
                    // so that there is no chance of making the visible panel overflowing the height of the target panel
                    if (settings.animate && settings.transitionIn == 'fadeIn') {
                        if (heightDifference < 0)
                            $panelContainer.animate({
                                height: $panelContainer.height() + heightDifference
                            }, transitions.halfSpeed).css({'min-height': ''});
                    }

                    if (settings.updateHash && !skipUpdateToHash) {
                        //window.location = url.toString().replace((url.pathname + hash), (url.pathname + $clicked.attr("href")));
                        // Not sure why this behaves so differently, but it's more straight forward and seems to have less side-effects
                        if (window.history.pushState) {
                            window.history.pushState(null, null, '#' + $targetPanel.attr('id'));
                        }
                        else {
                            window.location.hash = '#' + $targetPanel.attr('id');
                        }
                    } else {
                        skipUpdateToHash = false;
                    }

                    $targetPanel
                            [transitions.show](transitions.speed, settings.transitionInEasing, function () {
                        $panelContainer.css({height: '', 'min-height': ''}); // After the transition, unset the height
                        $container.trigger("easytabs:after", [$clicked, $targetPanel, settings]);
                        // callback only gets called if selectTab actually does something, since it's inside the if block
                        if (typeof callback == 'function') {
                            callback();
                        }
                    });
                };

                if (ajaxUrl && (!settings.cache || !$clicked.parent().data('easytabs').cached)) {
                    $container.trigger('easytabs:ajax:beforeSend', [$clicked, $targetPanel]);
                    $targetPanel.load(ajaxUrl, function (response, status, xhr) {
                        $clicked.parent().data('easytabs').cached = true;
                        $container.trigger('easytabs:ajax:complete', [$clicked, $targetPanel, response, status, xhr]);
                    });
                }

                // Gracefully animate between panels of differing heights, start height change animation *before* panel change if panel needs to expand,
                // so that there is no chance of making the target panel overflowing the height of the visible panel
                if (settings.animate && settings.transitionOut == 'fadeOut') {
                    if (heightDifference > 0) {
                        $panelContainer.animate({
                            height: ($panelContainer.height() + heightDifference)
                        }, transitions.halfSpeed);
                    } else {
                        // Prevent height jumping before height transition is triggered at midTransition
                        $panelContainer.css({'min-height': $panelContainer.height()});
                    }
                }

                // Change the active tab *first* to provide immediate feedback when the user clicks
                plugin.tabs.filter("." + settings.tabActiveClass).removeClass(settings.tabActiveClass).children().removeClass(settings.tabActiveClass);
                plugin.tabs.filter("." + settings.collapsedClass).removeClass(settings.collapsedClass).children().removeClass(settings.collapsedClass);
                $clicked.parent().addClass(settings.tabActiveClass).children().addClass(settings.tabActiveClass);

                plugin.panels.filter("." + settings.panelActiveClass).removeClass(settings.panelActiveClass);
                $targetPanel.addClass(settings.panelActiveClass);

                if ($visiblePanel.length) {
                    $visiblePanel
                            [transitions.hide](transitions.speed, settings.transitionOutEasing, showPanel);
                } else {
                    $targetPanel
                            [transitions.uncollapse](transitions.speed, settings.transitionUncollapseEasing, showPanel);
                }
            }
        };

        // Get heights of panels to enable animation between panels of
        // differing heights, called by activateTab
        var getHeightForHidden = function ($targetPanel) {

            if ($targetPanel.data('easytabs') && $targetPanel.data('easytabs').lastHeight) {
                return $targetPanel.data('easytabs').lastHeight;
            }

            // this is the only property easytabs changes, so we need to grab its value on each tab change
            var display = $targetPanel.css('display'),
                    outerCloak,
                    height;

            // Workaround with wrapping height, because firefox returns wrong
            // height if element itself has absolute positioning.
            // but try/catch block needed for IE7 and IE8 because they throw
            // an "Unspecified error" when trying to create an element
            // with the css position set.
            try {
                outerCloak = $('<div></div>', {'position': 'absolute', 'visibility': 'hidden', 'overflow': 'hidden'});
            } catch (e) {
                outerCloak = $('<div></div>', {'visibility': 'hidden', 'overflow': 'hidden'});
            }
            height = $targetPanel
                    .wrap(outerCloak)
                    .css({'position': 'relative', 'visibility': 'hidden', 'display': 'block'})
                    .outerHeight();

            $targetPanel.unwrap();

            // Return element to previous state
            $targetPanel.css({
                position: $targetPanel.data('easytabs').position,
                visibility: $targetPanel.data('easytabs').visibility,
                display: display
            });

            // Cache height
            $targetPanel.data('easytabs').lastHeight = height;

            return height;
        };

        // Since the height of the visible panel may have been manipulated due to interaction,
        // we want to re-cache the visible height on each tab change, called
        // by activateTab
        var setAndReturnHeight = function ($visiblePanel) {
            var height = $visiblePanel.outerHeight();

            if ($visiblePanel.data('easytabs')) {
                $visiblePanel.data('easytabs').lastHeight = height;
            } else {
                $visiblePanel.data('easytabs', {lastHeight: height});
            }
            return height;
        };

        // Setup hash-change callback for forward- and back-button
        // functionality, called by init
        var initHashChange = function () {

            // enabling back-button with jquery.hashchange plugin
            // http://benalman.com/projects/jquery-hashchange-plugin/
            if (typeof $(window).hashchange === 'function') {
                $(window).hashchange(function () {
                    plugin.selectTabFromHashChange();
                });
            } else if ($.address && typeof $.address.change === 'function') { // back-button with jquery.address plugin http://www.asual.com/jquery/address/docs/
                $.address.change(function () {
                    plugin.selectTabFromHashChange();
                });
            }
        };

        // Begin cycling if set in options, called by init
        var initCycle = function () {
            var tabNumber;
            if (settings.cycle) {
                tabNumber = plugin.tabs.index($defaultTab);
                setTimeout(function () {
                    plugin.cycleTabs(tabNumber + 1);
                }, settings.cycle);
            }
        };


        plugin.init();

    };

    $.fn.easytabs = function (options) {
        var args = arguments;

        return this.each(function () {
            var $this = $(this),
                    plugin = $this.data('easytabs');

            // Initialization was called with $(el).easytabs( { options } );
            if (undefined === plugin) {
                plugin = new $.easytabs(this, options);
                $this.data('easytabs', plugin);
            }

            // User called public method
            if (plugin.publicMethods[options]) {
                return plugin.publicMethods[options](Array.prototype.slice.call(args, 1));
            }
        });
    };

})(jQuery);


/*
 colpick Color Picker
 Copyright 2013 Jose Vargas. Licensed under GPL license. Based on Stefan Petre's Color Picker www.eyecon.ro, dual licensed under the MIT and GPL licenses
 
 For usage and examples: colpick.com/plugin
 */

(function ($) {
    var colpick = function () {
        var
                tpl = '<div class="colpick"><div class="colpick_color"><div class="colpick_color_overlay1"><div class="colpick_color_overlay2"><div class="colpick_selector_outer"><div class="colpick_selector_inner"></div></div></div></div></div><div class="colpick_hue"><div class="colpick_hue_arrs"><div class="colpick_hue_larr"></div><div class="colpick_hue_rarr"></div></div></div><div class="colpick_new_color"></div><div class="colpick_current_color"></div><div class="colpick_hex_field"><div class="colpick_field_letter">#</div><input type="text" maxlength="6" size="6" /></div><div class="colpick_rgb_r colpick_field"><div class="colpick_field_letter">R</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_rgb_g colpick_field"><div class="colpick_field_letter">G</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_rgb_b colpick_field"><div class="colpick_field_letter">B</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_hsb_h colpick_field"><div class="colpick_field_letter">H</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_hsb_s colpick_field"><div class="colpick_field_letter">S</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_hsb_b colpick_field"><div class="colpick_field_letter">B</div><input type="text" maxlength="3" size="3" /><div class="colpick_field_arrs"><div class="colpick_field_uarr"></div><div class="colpick_field_darr"></div></div></div><div class="colpick_submit"></div></div>',
                defaults = {
                    showEvent: 'click',
                    onShow: function () {
                    },
                    onBeforeShow: function () {
                    },
                    onHide: function () {
                    },
                    onChange: function () {
                    },
                    onSubmit: function () {
                    },
                    colorScheme: 'light',
                    color: '3289c7',
                    livePreview: true,
                    flat: false,
                    layout: 'full',
                    submit: 1,
                    submitText: 'OK',
                    height: 156
                },
        //Fill the inputs of the plugin
        fillRGBFields = function (hsb, cal) {
            var rgb = hsbToRgb(hsb);
            $(cal).data('colpick').fields
                    .eq(1).val(rgb.r).end()
                    .eq(2).val(rgb.g).end()
                    .eq(3).val(rgb.b).end();
        },
                fillHSBFields = function (hsb, cal) {
                    $(cal).data('colpick').fields
                            .eq(4).val(Math.round(hsb.h)).end()
                            .eq(5).val(Math.round(hsb.s)).end()
                            .eq(6).val(Math.round(hsb.b)).end();
                },
                fillHexFields = function (hsb, cal) {
                    $(cal).data('colpick').fields.eq(0).val(hsbToHex(hsb));
                },
                //Set the round selector position
                setSelector = function (hsb, cal) {
                    $(cal).data('colpick').selector.css('backgroundColor', '#' + hsbToHex({h: hsb.h, s: 100, b: 100}));
                    $(cal).data('colpick').selectorIndic.css({
                        left: parseInt($(cal).data('colpick').height * hsb.s / 100, 10),
                        top: parseInt($(cal).data('colpick').height * (100 - hsb.b) / 100, 10)
                    });
                },
                //Set the hue selector position
                setHue = function (hsb, cal) {
                    $(cal).data('colpick').hue.css('top', parseInt($(cal).data('colpick').height - $(cal).data('colpick').height * hsb.h / 360, 10));
                },
                //Set current and new colors
                setCurrentColor = function (hsb, cal) {
                    $(cal).data('colpick').currentColor.css('backgroundColor', '#' + hsbToHex(hsb));
                },
                setNewColor = function (hsb, cal) {
                    $(cal).data('colpick').newColor.css('backgroundColor', '#' + hsbToHex(hsb));
                },
                //Called when the new color is changed
                change = function (ev) {
                    var cal = $(this).parent().parent(), col;
                    if (this.parentNode.className.indexOf('_hex') > 0) {
                        cal.data('colpick').color = col = hexToHsb(fixHex(this.value));
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                    } else if (this.parentNode.className.indexOf('_hsb') > 0) {
                        cal.data('colpick').color = col = fixHSB({
                            h: parseInt(cal.data('colpick').fields.eq(4).val(), 10),
                            s: parseInt(cal.data('colpick').fields.eq(5).val(), 10),
                            b: parseInt(cal.data('colpick').fields.eq(6).val(), 10)
                        });
                        fillRGBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                    } else {
                        cal.data('colpick').color = col = rgbToHsb(fixRGB({
                            r: parseInt(cal.data('colpick').fields.eq(1).val(), 10),
                            g: parseInt(cal.data('colpick').fields.eq(2).val(), 10),
                            b: parseInt(cal.data('colpick').fields.eq(3).val(), 10)
                        }));
                        fillHexFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                    }
                    setSelector(col, cal.get(0));
                    setHue(col, cal.get(0));
                    setNewColor(col, cal.get(0));
                    cal.data('colpick').onChange.apply(cal.parent(), [col, hsbToHex(col), hsbToRgb(col), cal.data('colpick').el, 0]);
                },
                //Change style on blur and on focus of inputs
                blur = function (ev) {
                    $(this).parent().removeClass('colpick_focus');
                },
                focus = function () {
                    $(this).parent().parent().data('colpick').fields.parent().removeClass('colpick_focus');
                    $(this).parent().addClass('colpick_focus');
                },
                //Increment/decrement arrows functions
                downIncrement = function (ev) {
                    ev.preventDefault ? ev.preventDefault() : ev.returnValue = false;
                    var field = $(this).parent().find('input').focus();
                    var current = {
                        el: $(this).parent().addClass('colpick_slider'),
                        max: this.parentNode.className.indexOf('_hsb_h') > 0 ? 360 : (this.parentNode.className.indexOf('_hsb') > 0 ? 100 : 255),
                        y: ev.pageY,
                        field: field,
                        val: parseInt(field.val(), 10),
                        preview: $(this).parent().parent().data('colpick').livePreview
                    };
                    $(document).mouseup(current, upIncrement);
                    $(document).mousemove(current, moveIncrement);
                },
                moveIncrement = function (ev) {
                    ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val - ev.pageY + ev.data.y, 10))));
                    if (ev.data.preview) {
                        change.apply(ev.data.field.get(0), [true]);
                    }
                    return false;
                },
                upIncrement = function (ev) {
                    change.apply(ev.data.field.get(0), [true]);
                    ev.data.el.removeClass('colpick_slider').find('input').focus();
                    $(document).off('mouseup', upIncrement);
                    $(document).off('mousemove', moveIncrement);
                    return false;
                },
                //Hue slider functions
                downHue = function (ev) {
                    ev.preventDefault ? ev.preventDefault() : ev.returnValue = false;
                    var current = {
                        cal: $(this).parent(),
                        y: $(this).offset().top
                    };
                    $(document).on('mouseup touchend', current, upHue);
                    $(document).on('mousemove touchmove', current, moveHue);

                    var pageY = ((ev.type == 'touchstart') ? ev.originalEvent.changedTouches[0].pageY : ev.pageY);
                    change.apply(
                            current.cal.data('colpick')
                            .fields.eq(4).val(parseInt(360 * (current.cal.data('colpick').height - (pageY - current.y)) / current.cal.data('colpick').height, 10))
                            .get(0),
                            [current.cal.data('colpick').livePreview]
                            );
                    return false;
                },
                moveHue = function (ev) {
                    var pageY = ((ev.type == 'touchmove') ? ev.originalEvent.changedTouches[0].pageY : ev.pageY);
                    change.apply(
                            ev.data.cal.data('colpick')
                            .fields.eq(4).val(parseInt(360 * (ev.data.cal.data('colpick').height - Math.max(0, Math.min(ev.data.cal.data('colpick').height, (pageY - ev.data.y)))) / ev.data.cal.data('colpick').height, 10))
                            .get(0),
                            [ev.data.preview]
                            );
                    return false;
                },
                upHue = function (ev) {
                    fillRGBFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                    fillHexFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                    $(document).off('mouseup touchend', upHue);
                    $(document).off('mousemove touchmove', moveHue);
                    return false;
                },
                //Color selector functions
                downSelector = function (ev) {
                    ev.preventDefault ? ev.preventDefault() : ev.returnValue = false;
                    var current = {
                        cal: $(this).parent(),
                        pos: $(this).offset()
                    };
                    current.preview = current.cal.data('colpick').livePreview;

                    $(document).on('mouseup touchend', current, upSelector);
                    $(document).on('mousemove touchmove', current, moveSelector);

                    var payeX, pageY;
                    if (ev.type == 'touchstart') {
                        pageX = ev.originalEvent.changedTouches[0].pageX,
                                pageY = ev.originalEvent.changedTouches[0].pageY;
                    } else {
                        pageX = ev.pageX;
                        pageY = ev.pageY;
                    }

                    change.apply(
                            current.cal.data('colpick').fields
                            .eq(6).val(parseInt(100 * (current.cal.data('colpick').height - (pageY - current.pos.top)) / current.cal.data('colpick').height, 10)).end()
                            .eq(5).val(parseInt(100 * (pageX - current.pos.left) / current.cal.data('colpick').height, 10))
                            .get(0),
                            [current.preview]
                            );
                    return false;
                },
                moveSelector = function (ev) {
                    var payeX, pageY;
                    if (ev.type == 'touchmove') {
                        pageX = ev.originalEvent.changedTouches[0].pageX,
                                pageY = ev.originalEvent.changedTouches[0].pageY;
                    } else {
                        pageX = ev.pageX;
                        pageY = ev.pageY;
                    }

                    change.apply(
                            ev.data.cal.data('colpick').fields
                            .eq(6).val(parseInt(100 * (ev.data.cal.data('colpick').height - Math.max(0, Math.min(ev.data.cal.data('colpick').height, (pageY - ev.data.pos.top)))) / ev.data.cal.data('colpick').height, 10)).end()
                            .eq(5).val(parseInt(100 * (Math.max(0, Math.min(ev.data.cal.data('colpick').height, (pageX - ev.data.pos.left)))) / ev.data.cal.data('colpick').height, 10))
                            .get(0),
                            [ev.data.preview]
                            );
                    return false;
                },
                upSelector = function (ev) {
                    fillRGBFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                    fillHexFields(ev.data.cal.data('colpick').color, ev.data.cal.get(0));
                    $(document).off('mouseup touchend', upSelector);
                    $(document).off('mousemove touchmove', moveSelector);
                    return false;
                },
                //Submit button
                clickSubmit = function (ev) {
                    var cal = $(this).parent();
                    var col = cal.data('colpick').color;
                    cal.data('colpick').origColor = col;
                    setCurrentColor(col, cal.get(0));
                    cal.data('colpick').onSubmit(col, hsbToHex(col), hsbToRgb(col), cal.data('colpick').el);
                },
                //Show/hide the color picker
                show = function (ev) {
                    // Prevent the trigger of any direct parent
                    ev.stopPropagation();
                    var cal = $('#' + $(this).data('colpickId'));
                    cal.data('colpick').onBeforeShow.apply(this, [cal.get(0)]);
                    var pos = $(this).offset();
                    var top = pos.top + this.offsetHeight;
                    var left = pos.left;
                    var viewPort = getViewport();
                    var calW = cal.width();
                    if (left + calW > viewPort.l + viewPort.w) {
                        left -= calW;
                    }
                    cal.css({left: left + 'px', top: top + 'px'});
                    if (cal.data('colpick').onShow.apply(this, [cal.get(0)]) != false) {
                        cal.show();
                    }
                    //Hide when user clicks outside
                    $('html').mousedown({cal: cal}, hide);
                    cal.mousedown(function (ev) {
                        ev.stopPropagation();
                    })
                },
                hide = function (ev) {
                    if (ev.data.cal.data('colpick').onHide.apply(this, [ev.data.cal.get(0)]) != false) {
                        ev.data.cal.hide();
                    }
                    $('html').off('mousedown', hide);
                },
                getViewport = function () {
                    var m = document.compatMode == 'CSS1Compat';
                    return {
                        l: window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
                        w: window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth)
                    };
                },
                //Fix the values if the user enters a negative or high value
                fixHSB = function (hsb) {
                    return {
                        h: Math.min(360, Math.max(0, hsb.h)),
                        s: Math.min(100, Math.max(0, hsb.s)),
                        b: Math.min(100, Math.max(0, hsb.b))
                    };
                },
                fixRGB = function (rgb) {
                    return {
                        r: Math.min(255, Math.max(0, rgb.r)),
                        g: Math.min(255, Math.max(0, rgb.g)),
                        b: Math.min(255, Math.max(0, rgb.b))
                    };
                },
                fixHex = function (hex) {
                    var len = 6 - hex.length;
                    if (len > 0) {
                        var o = [];
                        for (var i = 0; i < len; i++) {
                            o.push('0');
                        }
                        o.push(hex);
                        hex = o.join('');
                    }
                    return hex;
                },
                restoreOriginal = function () {
                    var cal = $(this).parent();
                    var col = cal.data('colpick').origColor;
                    cal.data('colpick').color = col;
                    fillRGBFields(col, cal.get(0));
                    fillHexFields(col, cal.get(0));
                    fillHSBFields(col, cal.get(0));
                    setSelector(col, cal.get(0));
                    setHue(col, cal.get(0));
                    setNewColor(col, cal.get(0));
                };
        return {
            init: function (opt) {
                opt = $.extend({}, defaults, opt || {});
                //Set color
                if (typeof opt.color == 'string') {
                    opt.color = hexToHsb(opt.color);
                } else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
                    opt.color = rgbToHsb(opt.color);
                } else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
                    opt.color = fixHSB(opt.color);
                } else {
                    return this;
                }

                //For each selected DOM element
                return this.each(function () {
                    //If the element does not have an ID
                    if (!$(this).data('colpickId')) {
                        var options = $.extend({}, opt);
                        options.origColor = opt.color;
                        //Generate and assign a random ID
                        var id = 'collorpicker_' + parseInt(Math.random() * 1000);
                        $(this).data('colpickId', id);
                        //Set the tpl's ID and get the HTML
                        var cal = $(tpl).attr('id', id);
                        //Add class according to layout
                        cal.addClass('colpick_' + options.layout + (options.submit ? '' : ' colpick_' + options.layout + '_ns'));
                        //Add class if the color scheme is not default
                        if (options.colorScheme != 'light') {
                            cal.addClass('colpick_' + options.colorScheme);
                        }
                        //Setup submit button
                        cal.find('div.colpick_submit').html(options.submitText).click(clickSubmit);
                        //Setup input fields
                        options.fields = cal.find('input').change(change).blur(blur).focus(focus);
                        cal.find('div.colpick_field_arrs').mousedown(downIncrement).end().find('div.colpick_current_color').click(restoreOriginal);
                        //Setup hue selector
                        options.selector = cal.find('div.colpick_color').on('mousedown touchstart', downSelector);
                        options.selectorIndic = options.selector.find('div.colpick_selector_outer');
                        //Store parts of the plugin
                        options.el = this;
                        options.hue = cal.find('div.colpick_hue_arrs');
                        huebar = options.hue.parent();
                        //Paint the hue bar
                        var UA = navigator.userAgent.toLowerCase();
                        var isIE = navigator.appName === 'Microsoft Internet Explorer';
                        var IEver = isIE ? parseFloat(UA.match(/msie ([0-9]{1,}[\.0-9]{0,})/)[1]) : 0;
                        var ngIE = (isIE && IEver < 10);
                        var stops = ['#ff0000', '#ff0080', '#ff00ff', '#8000ff', '#0000ff', '#0080ff', '#00ffff', '#00ff80', '#00ff00', '#80ff00', '#ffff00', '#ff8000', '#ff0000'];
                        if (ngIE) {
                            var i, div;
                            for (i = 0; i <= 11; i++) {
                                div = $('<div></div>').attr('style', 'height:8.333333%; filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=' + stops[i] + ', endColorstr=' + stops[i + 1] + '); -ms-filter: "progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=' + stops[i] + ', endColorstr=' + stops[i + 1] + ')";');
                                huebar.append(div);
                            }
                        } else {
                            stopList = stops.join(',');
                            huebar.attr('style', 'background:-webkit-linear-gradient(top,' + stopList + '); background: -o-linear-gradient(top,' + stopList + '); background: -ms-linear-gradient(top,' + stopList + '); background:-moz-linear-gradient(top,' + stopList + '); -webkit-linear-gradient(top,' + stopList + '); background:linear-gradient(to bottom,' + stopList + '); ');
                        }
                        cal.find('div.colpick_hue').on('mousedown touchstart', downHue);
                        options.newColor = cal.find('div.colpick_new_color');
                        options.currentColor = cal.find('div.colpick_current_color');
                        //Store options and fill with default color
                        cal.data('colpick', options);
                        fillRGBFields(options.color, cal.get(0));
                        fillHSBFields(options.color, cal.get(0));
                        fillHexFields(options.color, cal.get(0));
                        setHue(options.color, cal.get(0));
                        setSelector(options.color, cal.get(0));
                        setCurrentColor(options.color, cal.get(0));
                        setNewColor(options.color, cal.get(0));
                        //Append to body if flat=false, else show in place
                        if (options.flat) {
                            cal.appendTo(this).show();
                            cal.css({
                                position: 'relative',
                                display: 'block'
                            });
                        } else {
                            cal.appendTo(document.body);
                            $(this).on(options.showEvent, show);
                            cal.css({
                                position: 'absolute'
                            });
                        }
                    }
                });
            },
            //Shows the picker
            showPicker: function () {
                return this.each(function () {
                    if ($(this).data('colpickId')) {
                        show.apply(this);
                    }
                });
            },
            //Hides the picker
            hidePicker: function () {
                return this.each(function () {
                    if ($(this).data('colpickId')) {
                        $('#' + $(this).data('colpickId')).hide();
                    }
                });
            },
            //Sets a color as new and current (default)
            setColor: function (col, setCurrent) {
                setCurrent = (typeof setCurrent === "undefined") ? 1 : setCurrent;
                if (typeof col == 'string') {
                    col = hexToHsb(col);
                } else if (col.r != undefined && col.g != undefined && col.b != undefined) {
                    col = rgbToHsb(col);
                } else if (col.h != undefined && col.s != undefined && col.b != undefined) {
                    col = fixHSB(col);
                } else {
                    return this;
                }
                return this.each(function () {
                    if ($(this).data('colpickId')) {
                        var cal = $('#' + $(this).data('colpickId'));
                        cal.data('colpick').color = col;
                        cal.data('colpick').origColor = col;
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                        setHue(col, cal.get(0));
                        setSelector(col, cal.get(0));

                        setNewColor(col, cal.get(0));
                        cal.data('colpick').onChange.apply(cal.parent(), [col, hsbToHex(col), hsbToRgb(col), cal.data('colpick').el, 1]);
                        if (setCurrent) {
                            setCurrentColor(col, cal.get(0));
                        }
                    }
                });
            }
        };
    }();
    //Color space convertions
    var hexToRgb = function (hex) {
        var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
        return {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
    };
    var hexToHsb = function (hex) {
        return rgbToHsb(hexToRgb(hex));
    };
    var rgbToHsb = function (rgb) {
        var hsb = {h: 0, s: 0, b: 0};
        var min = Math.min(rgb.r, rgb.g, rgb.b);
        var max = Math.max(rgb.r, rgb.g, rgb.b);
        var delta = max - min;
        hsb.b = max;
        hsb.s = max != 0 ? 255 * delta / max : 0;
        if (hsb.s != 0) {
            if (rgb.r == max)
                hsb.h = (rgb.g - rgb.b) / delta;
            else if (rgb.g == max)
                hsb.h = 2 + (rgb.b - rgb.r) / delta;
            else
                hsb.h = 4 + (rgb.r - rgb.g) / delta;
        } else
            hsb.h = -1;
        hsb.h *= 60;
        if (hsb.h < 0)
            hsb.h += 360;
        hsb.s *= 100 / 255;
        hsb.b *= 100 / 255;
        return hsb;
    };
    var hsbToRgb = function (hsb) {
        var rgb = {};
        var h = hsb.h;
        var s = hsb.s * 255 / 100;
        var v = hsb.b * 255 / 100;
        if (s == 0) {
            rgb.r = rgb.g = rgb.b = v;
        } else {
            var t1 = v;
            var t2 = (255 - s) * v / 255;
            var t3 = (t1 - t2) * (h % 60) / 60;
            if (h == 360)
                h = 0;
            if (h < 60) {
                rgb.r = t1;
                rgb.b = t2;
                rgb.g = t2 + t3
            }
            else if (h < 120) {
                rgb.g = t1;
                rgb.b = t2;
                rgb.r = t1 - t3
            }
            else if (h < 180) {
                rgb.g = t1;
                rgb.r = t2;
                rgb.b = t2 + t3
            }
            else if (h < 240) {
                rgb.b = t1;
                rgb.r = t2;
                rgb.g = t1 - t3
            }
            else if (h < 300) {
                rgb.b = t1;
                rgb.g = t2;
                rgb.r = t2 + t3
            }
            else if (h < 360) {
                rgb.r = t1;
                rgb.g = t2;
                rgb.b = t1 - t3
            }
            else {
                rgb.r = 0;
                rgb.g = 0;
                rgb.b = 0
            }
        }
        return {r: Math.round(rgb.r), g: Math.round(rgb.g), b: Math.round(rgb.b)};
    };
    var rgbToHex = function (rgb) {
        var hex = [
            rgb.r.toString(16),
            rgb.g.toString(16),
            rgb.b.toString(16)
        ];
        $.each(hex, function (nr, val) {
            if (val.length == 1) {
                hex[nr] = '0' + val;
            }
        });
        return hex.join('');
    };
    var hsbToHex = function (hsb) {
        return rgbToHex(hsbToRgb(hsb));
    };
    $.fn.extend({
        colpick: colpick.init,
        colpickHide: colpick.hidePicker,
        colpickShow: colpick.showPicker,
        colpickSetColor: colpick.setColor
    });
    $.extend({
        colpick: {
            rgbToHex: rgbToHex,
            rgbToHsb: rgbToHsb,
            hsbToHex: hsbToHex,
            hsbToRgb: hsbToRgb,
            hexToHsb: hexToHsb,
            hexToRgb: hexToRgb
        }
    });
})(jQuery);

// Load twitter button async
window.twttr = (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0],
            t = window.twttr || {};
    if (d.getElementById(id))
        return t;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://platform.twitter.com/widgets.js";
    fjs.parentNode.insertBefore(js, fjs);

    t._e = [];
    t.ready = function (f) {
        t._e.push(f);
    };

    return t;
}(document, "script", "twitter-wjs"));