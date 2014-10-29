

jQuery(document).ready( function($) {
    
    
    /*!------------------------------------------------------
 * jQuery nearest v1.0.3
 * http://github.com/jjenzz/jQuery.nearest
 * ------------------------------------------------------
 * Copyright (c) 2012 J. Smith (@jjenzz)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
(function($, d) {
  $.fn.nearest = function(selector) {
    var self, nearest, el, s, p,
        hasQsa = d.querySelectorAll;

    function update(el) {
      nearest = nearest ? nearest.add(el) : $(el);
    }

    this.each(function() {
      self = this;

      $.each(selector.split(','), function() {
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
    
    
    
    /* Opens a new minus buttonwhen plus sign is clicked */
    /* Toogle function for more services */
    $( "#myonoffswitch" ).click(function() {
        $('.onoffswitch').hide();
        $('.secondary-shares').show();
        $('.onoffswitch2').show();
        /*$( ".mashsb-buttons a" ).toggleClass( 'float-left');*/
    }); 
    $( "#myonoffswitch2" ).click(function() {
        $('.onoffswitch').show();
        $('.secondary-shares').hide();         
    }); 

    /* Network sharer scripts */
    /* deactivate FB sharer when likeaftershare is enabled */
    if (typeof lashare_fb == "undefined" && typeof mashsb !== 'undefined') {
    $('.mashicon-facebook').click( function(e) {
        e.preventDefault();
        winWidth = 520;
        winHeight = 350;
        var winTop = (screen.height / 2) - (winHeight / 2);
	var winLeft = (screen.width / 2) - (winWidth / 2);
        var url = $(this).attr('href');
          //alert(fburl + ' singular: ' + mashsb.singular)      
            if (mashsb.singular === '1') {
                window.open('http://www.facebook.com/sharer.php?s=100&u=' + mashsb.share_url + '&p[title]=' + mashsb.title + '&p[summary]=' + mashsb.desc + '&p[images][0]=' + mashsb.image, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
            } else {
                window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
            }
    });
    }
    if (typeof mashsb !== 'undefined') {
        shareurl = mashsb.share_url;
        if(typeof mashsu !== 'undefined'){
        mashsu.shorturl != 0 ? shareurl = mashsu.shorturl : shareurl = mashsb.share_url;
        }
    $('.mashicon-twitter').click( function(e) {
        e.preventDefault();
        winWidth = 520;
        winHeight = 350;
        var winTop = (screen.height / 2) - (winHeight / 2);
	var winLeft = (screen.width / 2) - (winWidth / 2);
        var url = $(this).attr('href');
        if (mashsb.singular === '1') {
            window.open('https://twitter.com/intent/tweet?text=' + mashsb.title + ' ' + mashsb.hashtag + '&url=' + shareurl, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
        }else{
            window.open(url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
        }
        });
    }

    if (typeof mashsb !== 'undefined' && mashsb.subscribe === 'content'){
        /* Toogle container display:none */
        jQuery('.mashicon-subscribe').not('.trigger_active').nearest('.mashsb-toggle-container').hide();
        jQuery('.mashicon-subscribe').click( function() {
            var trig = jQuery(this);
            if ( trig.hasClass('trigger_active') ) {
                jQuery(trig).nearest('.mashsb-toggle-container').slideToggle('fast');
                trig.removeClass('trigger_active');
                //jQuery(".mashicon-subscribe").css({"padding-bottom":"10px"});
            } else {
                jQuery('.trigger_active').nearest('.mashsb-toggle-container').slideToggle('slow');
                jQuery('.trigger_active').removeClass('trigger_active');
                jQuery(trig).nearest('.mashsb-toggle-container').slideToggle('fast');
                trig.addClass('trigger_active');
                //jQuery(".mashicon-subscribe").css({"padding-bottom":"13px"});
            };
            return false;
        });
    } 
    
    if (typeof mashsb !== 'undefined' && mashsb.subscribe === 'link') {
        $('.mashicon-subscribe').click( function() {
        var href = mashsb.subscribe_url;
        $(this).attr("href", href);
        });
    };
    


/* Animate the shares
 * 
 */

// target = id of html element or var of previously selected html element where counting occurs
// startVal = the value you want to begin at
// endVal = the value you want to arrive at
// decimals = number of decimal places, default 0
// duration = duration of animation in seconds, default 2
// options = optional object of options (see below)

/* Start when mashsb defined. Todo: JS builder to avoid this code */
if (typeof mashsb !== 'undefined' && mashsb.animate_shares == 1 && $('#mashsbcount').length) {

function countUp(target, startVal, endVal, decimals, duration, options) {

    // make sure requestAnimationFrame and cancelAnimationFrame are defined
    // polyfill for browsers without native support
    // by Opera engineer Erik Möller
    var lastTime = 0;
    var vendors = ['webkit', 'moz', 'ms'];
    for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
        window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
        window.cancelAnimationFrame =
          window[vendors[x]+'CancelAnimationFrame'] || window[vendors[x]+'CancelRequestAnimationFrame'];
    }
    if (!window.requestAnimationFrame) {
        window.requestAnimationFrame = function(callback, element) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max(0, 16 - (currTime - lastTime));
            var id = window.setTimeout(function() { callback(currTime + timeToCall); },
              timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        }
    }
    if (!window.cancelAnimationFrame) {
        window.cancelAnimationFrame = function(id) {
            clearTimeout(id);
        }
    }

     // default options
    this.options = options || {
        useEasing : true, // toggle easing
        useGrouping : true, // 1,000,000 vs 1000000
        separator : ',', // character to use as a separator
        decimal : '.', // character to use as a decimal
    }
    if (this.options.separator == '') this.options.useGrouping = false;
    if (this.options.prefix == null) this.options.prefix = '';
    if (this.options.suffix == null) this.options.suffix = '';

    var self = this;

    this.d = (typeof target === 'string') ? document.getElementById(target) : target;
    //this.d = (typeof target === 'string') ? document.getElementsByClassName(target) : target; 
    this.startVal = Number(startVal);
    this.endVal = Number(endVal);
    this.countDown = (this.startVal > this.endVal) ? true : false;
    this.startTime = null;
    this.timestamp = null;
    this.remaining = null;
    this.frameVal = this.startVal;
    this.rAF = null;
    this.decimals = Math.max(0, decimals || 0);
    this.dec = Math.pow(10, this.decimals);
    this.duration = duration * 1000 || 2000;

    this.version = function () { return '1.2.0' }

    // Robert Penner's easeOutExpo
    this.easeOutExpo = function(t, b, c, d) {
        return c * (-Math.pow(2, -10 * t / d) + 1) * 1024 / 1023 + b;
    }
    this.count = function(timestamp) {

        if (self.startTime === null) self.startTime = timestamp;

        self.timestamp = timestamp;

        var progress = timestamp - self.startTime;
        self.remaining = self.duration - progress;

        // to ease or not to ease
        if (self.options.useEasing) {
            if (self.countDown) {
                var i = self.easeOutExpo(progress, 0, self.startVal - self.endVal, self.duration);
                self.frameVal = self.startVal - i;
            } else {
                self.frameVal = self.easeOutExpo(progress, self.startVal, self.endVal - self.startVal, self.duration);
            }
        } else {
            if (self.countDown) {
                var i = (self.startVal - self.endVal) * (progress / self.duration);
                self.frameVal = self.startVal - i;
            } else {
                self.frameVal = self.startVal + (self.endVal - self.startVal) * (progress / self.duration);
            }
        }

        // decimal
        self.frameVal = Math.round(self.frameVal*self.dec)/self.dec;

        // don't go past endVal since progress can exceed duration in the last frame
        if (self.countDown) {
            self.frameVal = (self.frameVal < self.endVal) ? self.endVal : self.frameVal;
        } else {
            self.frameVal = (self.frameVal > self.endVal) ? self.endVal : self.frameVal;
        }

        // format and print value
        self.d.innerHTML = self.formatNumber(self.frameVal.toFixed(self.decimals));

        // whether to continue
        if (progress < self.duration) {
            self.rAF = requestAnimationFrame(self.count);
        } else {
            if (self.callback != null) self.callback();
        }
    }
    this.start = function(callback) {
        self.callback = callback;
        // make sure values are valid
        if (!isNaN(self.endVal) && !isNaN(self.startVal)) {
            self.rAF = requestAnimationFrame(self.count);
        } else {
            console.log('countUp error: startVal or endVal is not a number');
            self.d.innerHTML = '--';
        }
        return false;
    }
    this.stop = function() {
        cancelAnimationFrame(self.rAF);
    }
    this.reset = function() {
        self.startTime = null;
        self.startVal = startVal;
        cancelAnimationFrame(self.rAF);
        self.d.innerHTML = self.formatNumber(self.startVal.toFixed(self.decimals));
    }
    this.resume = function() {
        self.startTime = null;
        self.duration = self.remaining;
        self.startVal = self.frameVal;
        requestAnimationFrame(self.count);
    }
    this.formatNumber = function(nStr) {
        nStr += '';
        var x, x1, x2, rgx;
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? self.options.decimal + x[1] : '';
        rgx = /(\d+)(\d{3})/;
        if (self.options.useGrouping) {
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + self.options.separator + '$2');
            }
        }
        return self.options.prefix + x1 + x2 + self.options.suffix;
    }

    // format startVal on initialization
    self.d.innerHTML = self.formatNumber(self.startVal.toFixed(self.decimals));
}

// Example:
// var numAnim = new countUp("SomeElementYouWantToAnimate", 0, 99.99, 2, 2.5);
// numAnim.start();
// with optional callback:
// numAnim.start(someMethodToCallOnComplete);

var options = {
  useEasing : true, 
  useGrouping : false, 
  separator : ',', 
  decimal : '.', 
  prefix : '', 
  suffix : '' 
};


var mashsbcounter = new countUp("mashsbcount", 0, mashsb.shares, 0, 2, options);
mashsbcounter.start(roundShares);
};


function roundShares(){
     if (typeof mashsb !== "undefined" && mashsb.round_shares == 1) {
             if (mashsb.shares > 1000000) {
                    //console.log("100000");
                    shares = Math.round((mashsb.shares / 1000000)*10)/10 + 'M';
                    return jQuery('.counts').text(shares);
                    
                }
                if (mashsb.shares > 1000) {
                    //console.log("1000");
                    shares = Math.round((mashsb.shares / 1000)*10)/10 + 'k';
                    //mashsb.shares = mashsb.shares / 1000 + 'k';
                    //console.log("k: " + shares);
                    return jQuery('.counts').text(shares);
                    
                 }  
     }
     jQuery('.counts').text(mashsb.shares);
}


});

