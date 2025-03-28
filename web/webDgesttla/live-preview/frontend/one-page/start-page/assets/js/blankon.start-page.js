var BlankonStartPage = function () {

    // =========================================================================
    // SETTINGS APP
    // =========================================================================
    var globalPluginsPath = '../../../../assets/global/plugins/bower_components';

    // IE mode
    var isIE8 = false;
    var isIE9 = false;
    var isIE10 = false;

    return {

        // =========================================================================
        // CONSTRUCTOR APP
        // =========================================================================
        init: function () {
            BlankonStartPage.handleIE();
            BlankonStartPage.handleSound();
            BlankonStartPage.handleVersions();
            BlankonStartPage.handleSpecial();
            BlankonStartPage.handleRevolution();
            BlankonStartPage.handleBackToTop();
            BlankonStartPage.handlePanelScroll();
            BlankonStartPage.handleAnimationScroll();
            BlankonStartPage.handleCopyrightYear();
        },

        // =========================================================================
        // IE SUPPORT
        // =========================================================================
        handleIE: function () {

            // initializes main settings for IE
            isIE8 = !! navigator.userAgent.match(/MSIE 8.0/);
            isIE9 = !! navigator.userAgent.match(/MSIE 9.0/);
            isIE10 = !! navigator.userAgent.match(/MSIE 10.0/);

            if (isIE10) {
                $('html').addClass('ie10'); // detect IE10 version
            }

            if (isIE10 || isIE9 || isIE8) {
                $('html').addClass('ie'); // detect IE8, IE9, IE10 version
            }
        },

        // =========================================================================
        // SOUNDS
        // =========================================================================
        handleSound: function () {
            if($('.page-sound').length){
                ion.sound({
                    sounds: [
                        {name: "bell_ring", volume: 0.6},
                        {name: "cd_tray", volume: 0.6}
                    ],
                    path: globalPluginsPath+'/ionsound/sounds/',
                    preload: true
                });

            }
        },

        // =========================================================================
        // BLANKON VERSIONS
        // =========================================================================
        handleVersions: function () {
            (function($, window, document, undefined) {
                'use strict';

                // init cubeportfolio
                $('#js-grid-masonry-versions').cubeportfolio({
                    layoutMode: 'grid',
                    defaultFilter: '*',
                    animationType: 'quicksand',
                    gapHorizontal: 35,
                    gapVertical: 25,
                    gridAdjustment: 'responsive',
                    mediaQueries: [{
                        width: 1500,
                        cols: 5
                    }, {
                        width: 1100,
                        cols: 4
                    }, {
                        width: 800,
                        cols: 3
                    }, {
                        width: 480,
                        cols: 2,
                        options: {
                            caption: ''
                        }
                    }, {
                        width: 320,
                        cols: 1,
                        options: {
                            caption: ''
                        }
                    }],
                    caption: 'zoom',
                    displayType: 'fadeIn',
                    displayTypeSpeed: 100,

                    // lightbox
                    lightboxDelegate: '.cbp-lightbox',
                    lightboxGallery: true,
                    lightboxTitleSrc: 'data-title',
                    lightboxCounter: '<div class="cbp-popup-lightbox-counter">{{current}} of {{total}}</div>',

                    // singlePage popup
                    singlePageDelegate: '.cbp-singlePage',
                    singlePageDeeplinking: true,
                    singlePageStickyNavigation: true,
                    singlePageCounter: '<div class="cbp-popup-singlePage-counter">{{current}} of {{total}}</div>',
                    singlePageCallback: function(url, element) {
                        // to update singlePage content use the following method: this.updateSinglePage(yourContent)
                        var t = this;

                        $.ajax({
                            url: url,
                            type: 'GET',
                            dataType: 'html',
                            timeout: 30000
                        })
                            .done(function(result) {
                                t.updateSinglePage(result);
                            })
                            .fail(function() {
                                t.updateSinglePage('AJAX Error! Please refresh the page!');
                            });
                    }
                });
            })(jQuery, window, document);
        },

        // =========================================================================
        // BLANKON SPECIAL
        // =========================================================================
        handleSpecial: function () {
            (function($, window, document, undefined) {
                'use strict';

                // init cubeportfolio
                $('#js-grid-masonry-special').cubeportfolio({
                    layoutMode: 'grid',
                    defaultFilter: '*',
                    animationType: 'quicksand',
                    gapHorizontal: 35,
                    gapVertical: 25,
                    gridAdjustment: 'responsive',
                    mediaQueries: [{
                        width: 1500,
                        cols: 5
                    }, {
                        width: 1100,
                        cols: 4
                    }, {
                        width: 800,
                        cols: 3
                    }, {
                        width: 480,
                        cols: 2,
                        options: {
                            caption: ''
                        }
                    }, {
                        width: 320,
                        cols: 1,
                        options: {
                            caption: ''
                        }
                    }],
                    caption: 'zoom',
                    displayType: 'fadeIn',
                    displayTypeSpeed: 100,

                    // lightbox
                    lightboxDelegate: '.cbp-lightbox',
                    lightboxGallery: true,
                    lightboxTitleSrc: 'data-title',
                    lightboxCounter: '<div class="cbp-popup-lightbox-counter">{{current}} of {{total}}</div>',

                    // singlePage popup
                    singlePageDelegate: '.cbp-singlePage',
                    singlePageDeeplinking: true,
                    singlePageStickyNavigation: true,
                    singlePageCounter: '<div class="cbp-popup-singlePage-counter">{{current}} of {{total}}</div>',
                    singlePageCallback: function(url, element) {
                        // to update singlePage content use the following method: this.updateSinglePage(yourContent)
                        var t = this;

                        $.ajax({
                            url: url,
                            type: 'GET',
                            dataType: 'html',
                            timeout: 30000
                        })
                            .done(function(result) {
                                t.updateSinglePage(result);
                            })
                            .fail(function() {
                                t.updateSinglePage('AJAX Error! Please refresh the page!');
                            });
                    }
                });
            })(jQuery, window, document);
        },

        // =========================================================================
        // BLANKON SLIDER REVOLUTION
        // =========================================================================
        handleRevolution: function () {
            (function($, window, document, undefined) {
                'use strict';

                // init cubeportfolio
                $('#js-grid-masonry-revolution').cubeportfolio({
                    layoutMode: 'grid',
                    defaultFilter: '*',
                    animationType: 'quicksand',
                    gapHorizontal: 35,
                    gapVertical: 25,
                    gridAdjustment: 'responsive',
                    mediaQueries: [{
                        width: 1500,
                        cols: 5
                    }, {
                        width: 1100,
                        cols: 4
                    }, {
                        width: 800,
                        cols: 3
                    }, {
                        width: 480,
                        cols: 2,
                        options: {
                            caption: ''
                        }
                    }, {
                        width: 320,
                        cols: 1,
                        options: {
                            caption: ''
                        }
                    }],
                    caption: 'zoom',
                    displayType: 'fadeIn',
                    displayTypeSpeed: 100,

                    // lightbox
                    lightboxDelegate: '.cbp-lightbox',
                    lightboxGallery: true,
                    lightboxTitleSrc: 'data-title',
                    lightboxCounter: '<div class="cbp-popup-lightbox-counter">{{current}} of {{total}}</div>',

                    // singlePage popup
                    singlePageDelegate: '.cbp-singlePage',
                    singlePageDeeplinking: true,
                    singlePageStickyNavigation: true,
                    singlePageCounter: '<div class="cbp-popup-singlePage-counter">{{current}} of {{total}}</div>',
                    singlePageCallback: function(url, element) {
                        // to update singlePage content use the following method: this.updateSinglePage(yourContent)
                        var t = this;

                        $.ajax({
                            url: url,
                            type: 'GET',
                            dataType: 'html',
                            timeout: 30000
                        })
                            .done(function(result) {
                                t.updateSinglePage(result);
                            })
                            .fail(function() {
                                t.updateSinglePage('AJAX Error! Please refresh the page!');
                            });
                    }
                });
            })(jQuery, window, document);
        },

        // =========================================================================
        // BACK TOP
        // =========================================================================
        handleBackToTop: function () {
            // hide #back-top first
            $("#back-top").hide();

            // fade in #back-top
            $(window).scroll(function () {
                if ($(this).scrollTop() > 773) {
                    $('#back-top').fadeIn();
                } else {
                    $('#back-top').fadeOut();
                }
            });

            // scroll body to 0px on click
            $('#back-top').click(function () {
                // Add sound
                ion.sound.play("cd_tray");
                $('body,html').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
            $('#to-reasons').click(function () {
                // Add effect animate on body or HTML
                $('body,html').animate({
                    scrollTop: $(this).parents('[data-target="height-content"]').height()
                }, 2000);
                return false;
            });
        },

        // =========================================================================
        // NICESCROLL PANEL
        // =========================================================================
        handlePanelScroll: function () {
            $('.panel-scrollable .panel-body').niceScroll({
                cursorcolor: '#81b71a',
                cursorwidth: '5px',
                cursorborder: '0px'
            });
        },

        // =========================================================================
        // ANIMATIONS ON SCROLL
        // =========================================================================
        handleAnimationScroll: function () {
            var waypointClass = '#wrapper [class*="fade-in-effect"]';
            var waypointSummary = '#summary p';
            var animationClass = 'fadeIn';
            var delayTime;

            // Fix opacity landing issue for IE8 and IE9
            if (isIE8 || isIE9) { // ie8 & ie9
                $(waypointClass).css({opacity: '1'});
            }else{
                $(waypointClass).css({opacity: '0'});
            }

            $(waypointClass).waypoint(function() {
                    delayTime += 100;
                    $(this).delay(delayTime).queue(function(next){
                        $(this).toggleClass('animated');
                        $(this).toggleClass(animationClass);
                        delayTime = 0;
                        next();
                    });
                },
                {
                    offset: '73%',
                    triggerOnce: true
                });

            $(waypointSummary).waypoint(function() {
                    delayTime += 100;
                    $(this).delay(delayTime).queue(function(next){
                        $(this).toggleClass('animated');
                        $(this).toggleClass('tada');
                        delayTime = 0;
                        next();
                        // Add effect sound
                        if($('.page-sound').length){
                            ion.sound.play("bell_ring");
                        }
                    });
                },
                {
                    offset: '90%',
                    triggerOnce: true
                });

            //jQuery for page scrolling feature - requires jQuery Easing plugin
            $(function() {
                $('.page-scroll').bind('click', function(event) {
                    var $anchor = $(this);
                    $('html, body').stop().animate({
                        scrollTop: $($anchor.attr('href')).offset().top
                    }, 1500, 'easeInOutExpo');
                    event.preventDefault();
                });
            });
        },

        // =========================================================================
        // COPYRIGHT YEAR
        // =========================================================================
        handleCopyrightYear : function () {
            if($('#copyright-year').length){
                var today = new Date();
                $('#copyright-year').text(today.getFullYear());
            }
        }

    };

}();

// Call main app init
BlankonStartPage.init();