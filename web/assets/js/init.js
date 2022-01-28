(function($, window) {
    // generate a random number
    window.randNum = function()
    {
        return (Math.floor( Math.random()* (1+40-20) ) ) + 20;
    }
    
    // Function to generate sparklines
    function generateSparklines()
    {
        if ($('.sparkline').length)
        {
            $.each($('.sparkline'), function(k,v)
            {
                var size = { w: 150, h: 28 };
                var color = primaryColor;
                
                if ($(this).is('.danger')) color = dangerColor;
                if ($(this).is('.success')) color = successColor;
                if ($(this).is('.warning')) color = warningColor;
                if ($(this).is('.inverse')) color = inverseColor;
                if ($(this).is('.info')) color = infoColor;
                
                var data = [[1, 3+randNum()], [2, 5+randNum()], [3, 8+randNum()], [4, 11+randNum()],[5, 14+randNum()],[6, 17+randNum()],[7, 20+randNum()], [8, 15+randNum()], [9, 18+randNum()], [10, 22+randNum()]];
                $(v).sparkline(data, 
                { 
                    type: 'bar',
                    width: size.w,
                    height: size.h,
                    stackedBarColor: [color, color],
                    lineWidth: 2
                });
            });
        }

        if ($('.sparkline-line').length)
        {
            $.each($('.sparkline-line'), function(k,v)
            {
                var size = { w: 28, h: 28 };
                
                var data = [3+randNum(), 5+randNum(), 8+randNum(), 11+randNum()];
                $(v).sparkline(data, 
                { 
                    type: 'pie',
                    width: size.w,
                    height: size.h,
                    sliceColor: [dangerColor, warningColor, infoColor, successColor],
                });
            });
        }
    }

    // Initialize DateRangePicker where it is needed
    $('#reportrange').daterangepicker(
        {
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
            },
            startDate: moment().subtract('days', 29),
            endDate: moment()
        },
        function(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
    );
    // Easy pie charts
    if ($('.easy-pie').length)
    {
        $.each($('.easy-pie'), function(k,v)
        {
            if ($(this).is('.danger')) color = dangerColor;
            if ($(this).is('.success')) color = successColor;
            if ($(this).is('.warning')) color = warningColor;
            if ($(this).is('.inverse')) color = inverseColor;
            if ($(this).is('.info')) color = infoColor;

            $(this).easyPieChart({
                barColor: color,
                lineWidth: 5,
                trackColor: '#efefef',
                scaleColor: '#000',
                easing: 'easeOutBounce',
                onStep: function(from, to, percent) {
                    $(this.el).find('.percent').text(Math.round(percent));
                }
            });
            var chart = window.chart = $('.easy-pie').data('easyPieChart');
        });
    }


    $('#side-menu').metisMenu();

    // Initializing tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Initializing popovers
    $('[data-toggle="popover"]').popover();

    $('.trigger-sidebar').on( 'click', function () {
        if ($('.sidebar-right').length) {
            if ($('.sidebar-right').hasClass('open')) {
                $('.sidebar-right').removeClass('open');
                $('.navbar').removeClass('sidebar-open');
                $('body').removeClass('menu-hidden');
            } else {
                $('body').addClass('menu-hidden');
                $('.sidebar-right').addClass('open');
                $('.navbar').addClass('sidebar-open');
            }
        }
    });

    /* Bootstrap Switch */
    if (typeof $.fn.bootstrapSwitch != 'undefined' && $('.make-switch, .switcher').length)
        $('.make-switch:not(.has-switch), .switcher:not(.has-switch)').bootstrapSwitch();
    /* // Bootstrap Switch */

    $('.navbar-toggle').on('click', function () {
        if ($('#menu.hidden-xs').length)
            $('#menu').removeClass('hidden-xs');
        else
            $('#menu').addClass('hidden-xs');
    });
    
    //Loads the correct sidebar on window load
    $(window).bind("load", function() {
        if ($(this).width() < 768) {
            $('div.sidebar-collapse').addClass('collapse')
        } else {
            $('div.sidebar-collapse').removeClass('collapse')
        }
    });

    // Switch theme color
    var $styleSwitcher = $('#style_switcher');
    $('.switcher_toggle').on('click', function(e) {
        if(!$styleSwitcher.hasClass('switcher_open')) {
            $styleSwitcher.addClass('switcher_open')
        } else {
            $styleSwitcher.removeClass('switcher_open')
        }
        e.preventDefault();
    })

    $('.switch-style').on('click', function (event) {
        $('#style_switcher li').removeClass('style_active');
        $(this).addClass('style_active');
        var themeStyle = $( this ).attr( 'data-style' );
        $.cookie('themeStyle', themeStyle);
        if ( $.cookie('dev') == 'true' ) {
            $("#style_color").attr('href', url + 'less/'+themeStyle+'.less?' + time);
            event.preventDefault();
            less.modifyVars({
                '@navigationBgColor': $( this ).attr( 'data-bg-color' ),
                '@navigationLinkColor': $( this ).attr( 'data-link-color' ),
                '@navigationBorderColor': $( this ).attr( 'data-border-color' ),
            });
            less.refreshStyles();
            return false;
        } else {
            $("#style_color").attr('href', url + 'css/'+themeStyle+'.css');
            return false;
        }
    });

    //Collapses the sidebar on window resize
    $(window).bind("resize", function() {
        if ($(this).width() < 768) {
            $('div.sidebar-collapse').addClass('collapse')
        } else {
            $('div.sidebar-collapse').removeClass('collapse')
        }
    });



    // Adding niceScroll to HTML tag
    $( "html" ).niceScroll({cursorcolor: cursorColor, cursorborder: "none", horizrailenabled: false, zindex: 2000 });

    $('.has-nice-scroll').each(function () {
        $(this).niceScroll({
            horizrailenabled: false, 
            zindex: 2000,
            cursorborder: "none",
        });
    });

    // Stopping Dropdown menu from closing on click event
    $('.mega-menu .dropdown-menu').on('click', function (event) {
        event.stopPropagation();
    });

    generateSparklines();


    // removing panels
    $('[data-action^="close"]').on('click', function () {
      $(this).closest('.panel').hide();
    });

    // realoding panels
    $('[data-action^="reload"]').on('click', function () {
      $(this).closest(".panel").children('.panel-body').block({ 
        message: '<h2><i class="fa fa-spinner fa-spin"></i></h2>',
        css: { 
          border: 'none', 
          padding: '15px', 
          background: 'none',
        },
        overlayCSS: { backgroundColor: '#FFF' },
        timeout: 2000 
      });
    });
    // panle settings
    $('[data-action^="settings"]').on('click', function () {

    });
    // panle minimize
    $('[data-action^="minimize"]').on('click', function () {
      if ($(this).hasClass('active')){
        $(this).removeClass('active');
        $(this).closest(".panel").children('.panel-body').slideDown('fast'); 
      } else{
        $(this).addClass('active');
        $(this).closest(".panel").children('.panel-body').slideUp('fast'); 
      }
    });


    //Set the carousel options
    $('#quote-carousel').carousel({
        pause: true,
        interval: 4000,
    });
})(jQuery, window);