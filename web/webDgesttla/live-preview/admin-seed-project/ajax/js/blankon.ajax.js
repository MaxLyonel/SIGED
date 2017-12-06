var BlankonAjax = function () {

    // Setting variable
    var default_content='',
        lasturl='';

    return {

        // =========================================================================
        // CONSTRUCTOR APP
        // =========================================================================
        init: function () {
            BlankonAjax.actionAJAX();
            BlankonAjax.handleCallbackAJAX();
        },

        // =========================================================================
        // ACTION AJAX
        // =========================================================================
        actionAJAX: function () {
            BlankonAjax.checkURL();
            $('#sign-wrapper a').click(function (){
                BlankonAjax.checkURL(this.hash);
            });
            $('#sidebar-left ul.sidebar-menu li a').click(function (event){

                // Check submenu
                if($(this).closest('li').hasClass('submenu')){
                    $(this).append('<span class="selected"></span>');
                    $(this).closest('li').addClass('active').siblings().removeClass('active').find('.selected').remove();
                    $(this).closest('li').siblings().find('ul').find('li').removeClass('active');
                }else{
                    // Close another submenu
                    $(this).closest('li').siblings().find('ul').hide();
                }

                BlankonAjax.checkURL(this.hash);
            });
            //filling in the default content
            default_content = $('#body-content-ajax').html();
        },

        // =========================================================================
        // CHECK URL
        // =========================================================================
        checkURL: function (hash) {
            if(!hash) hash = window.location.hash;
            if(hash != lasturl)
            {
                lasturl=hash;
                // FIX - if we've used the history buttons to return to the homepage,
                // fill the pageContent with the default_content
                if(hash==""){
                    $('#body-content-ajax').html(default_content);
                }else{
                    BlankonAjax.loadPage(hash);
                }
            }
        },

        // =========================================================================
        // SET ACTIVE MENU
        // =========================================================================
        activeMenu: function (url) {
            // Get current url
            // Select an a element that has the matching href and apply a class of 'active'.
            $('#sidebar-left .sidebar-menu a[href="'+url+'"]')
                .append('<span class="selected"></span>')
                .closest('li')
                .addClass('active')
                .siblings()
                .removeClass('active')
                .find('.selected')
                .remove();

            // Submenu
            $('#sidebar-left .sidebar-menu a[href="'+url+'"]')
                .append('<span class="selected"></span>')
                .closest('.submenu')
                .addClass('active')
                .siblings()
                .removeClass('active')
                .find('.selected')
                .remove();
        },

        // =========================================================================
        // DYNAMIC LINK CSS & JS
        // =========================================================================
        dynamicResources: function (url) {

            // Setting global path
            var pluginPath           = BlankonApp.handleBaseURL()+'/assets/global/plugins/bower_components/',
                pluginCommercialPath = BlankonApp.handleBaseURL()+'/assets/commercial/plugins/',
                jsPath               = BlankonApp.handleBaseURL()+'/assets/admin/js/pages/',
                cssPath              = BlankonApp.handleBaseURL()+'/assets/admin/css/pages/',
                cssCommercialPath    = BlankonApp.handleBaseURL()+'/assets/commercial/plugins/';

            // Dashboard page
            if(url == 'dashboard'){
                // Get css files
                $.getCSS({href:pluginPath+'dropzone/downloads/css/dropzone.css', rel:'stylesheet'});
                $.getCSS({href:pluginPath+'jquery.gritter/css/jquery.gritter.css', rel:'stylesheet'});

                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'bootstrap-session-timeout/dist/bootstrap-session-timeout.min.js'),
                    $.getScript(pluginPath+'flot/jquery.flot.pack.js'),
                    $.getScript(pluginPath+'dropzone/downloads/dropzone.min.js'),
                    $.getScript(pluginPath+'jquery.gritter/js/jquery.gritter.min.js'),
                    $.getScript(pluginPath+'skycons-html5/skycons.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                        $.getScript(jsPath+'blankon.dashboard.js');
                    });
            }

            // Error 403, 404, 500 page
            if(url == 'error/error-403' || url == 'error/error-404' || url == 'error/error-405'){
                // Get css files
                $.getCSS({href:cssPath+'error-page.css', rel:'stylesheet'});

                // Get js level scripts
                $.getScript(jsPath+'blankon.layout.js');
            }

            // Form element page
            if(url == 'forms/form-element'){
                // Get css files
                $.getCSS({href:pluginPath+'bootstrap-tagsinput/dist/bootstrap-tagsinput.css', rel:'stylesheet'});
                $.getCSS({href:pluginPath+'jasny-bootstrap-fileinput/css/jasny-bootstrap-fileinput.min.css', rel:'stylesheet'});
                $.getCSS({href:pluginPath+'chosen_v1.2.0/chosen.min.css', rel:'stylesheet'});

                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js'),
                    $.getScript(pluginPath+'jasny-bootstrap-fileinput/js/jasny-bootstrap.fileinput.min.js'),
                    $.getScript(pluginPath+'holderjs/holder.js'),
                    $.getScript(pluginPath+'bootstrap-maxlength/bootstrap-maxlength.min.js'),
                    $.getScript(pluginPath+'jquery-autosize/jquery.autosize.min.js'),
                    $.getScript(pluginPath+'chosen_v1.2.0/chosen.jquery.min.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                        $.getScript(jsPath+'blankon.form.element.js');
                    });
            }

            // Form advanced page
            if(url == 'forms/form-advanced'){
                // Get css files
                $.getCSS({href:pluginPath+'dropzone/downloads/css/dropzone.css', rel:'stylesheet'});
                $.getCSS({href:pluginPath+'bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css', rel:'stylesheet'});
                $.getCSS({href:pluginPath+'bootstrap-datepicker-vitalets/css/datepicker.css', rel:'stylesheet'});

                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'dropzone/downloads/dropzone.min.js'),
                    $.getScript(pluginPath+'bootstrap-switch/dist/js/bootstrap-switch.min.js'),
                    $.getScript(pluginPath+'jquery.inputmask/dist/jquery.inputmask.bundle.min.js'),
                    $.getScript(pluginPath+'bootstrap-datepicker-vitalets/js/bootstrap-datepicker.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                        $.getScript(jsPath+'blankon.form.advanced.js');
                    });
            }

            // Form layout page
            if(url == 'forms/form-layout'){
                // Get css files
                $.getCSS({href:pluginPath+'bootstrap-fileupload/css/bootstrap-fileupload.min.css', rel:'stylesheet'});
                $.getCSS({href:pluginPath+'chosen_v1.2.0/chosen.min.css', rel:'stylesheet'});

                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'bootstrap-fileupload/js/bootstrap-fileupload.min.js'),
                    $.getScript(pluginPath+'chosen_v1.2.0/chosen.jquery.min.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                        $.getScript(jsPath+'blankon.form.layout.js');
                    });
            }

            // Form validation page
            if(url == 'forms/form-validation'){
                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'chosen_v1.2.0/chosen.jquery.min.js'),
                    $.getScript(pluginPath+'jquery-mockjax/jquery.mockjax.js'),
                    $.getScript(pluginPath+'jquery-validation/dist/jquery.validate.min.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                        $.getScript(jsPath+'blankon.form.validation.js');
                    });
            }

            // Component typography page
            if(url == 'components/component-typography'){
                // Get css files
                $.getCSS({href:pluginPath+'google-code-prettify/bin/prettify.min.css', rel:'stylesheet'});

                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'google-code-prettify/bin/prettify.min.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                    $.getScript(jsPath+'blankon.typography.js');
                    });
            }

            // Component sliders page
            if(url == 'components/component-sliders'){
                // Get css files
                $.getCSS({href:pluginPath+'ion.rangeSlider/css/ion.rangeSlider.css', rel:'stylesheet'});

                // Get js level plugins
                $.when(
                    $.getScript(pluginPath+'ion.rangeSlider/js/ion.rangeSlider.min.js'),
                    $.Deferred(function( deferred ){
                        $( deferred.resolve );
                    })
                ).done(function(){
                        // Get js level scripts
                        $.getScript(jsPath+'blankon.slider.js');
                    });
            }

            // Component glyphicons icon page
            if(url == 'components/component-glyphicons'){
                // Get css files
                $.getCSS({href:cssPath+'glyphicons.css', rel:'stylesheet'});
            }

            // Component simple line icon page
            if(url == 'components/component-simple-line-icons'){
                // Get css files
                $.getCSS({href:pluginPath+'simple-line-icons/css/simple-line-icons.css', rel:'stylesheet'});
                $.getCSS({href:cssPath+'icon.css', rel:'stylesheet'});
            }

        },

        // =========================================================================
        // LOAD PAGE
        // =========================================================================
        loadPage : function (url) {

            // Set active menu on sidebar left
            BlankonAjax.activeMenu(url);

            // Remove hashtag URL
            url=url.replace('#','');

            $.ajax({
                type: "POST",
                url: "php/load_page.php",
                cache: true,
                data: {
                    page : url
                },
                dataType: "html",
                beforeSend: function () {
                    $('#page-content').block({
                        message: '<h1><img src="../../../../assets/global/img/loader/general/2.gif" alt="Please wait..." /> Please wait...</h1>',
                        centerY: false,
                        css: {
                            top: '265px',
                            width: '15%'
                        }
                    });
                },
                success: function(msg){
                    // Check dynamic css
                    BlankonAjax.dynamicResources(url);
                    if(parseInt(msg)!=0)
                    {
                        $('#body-content-ajax').html(msg);
                        $('.footer-content').show();
                    }
                    $('#page-content').unblock();
                }
            });
        },

        // =========================================================================
        // HANDLE TRIGGER AFTER AJAX ACTION
        // =========================================================================
        handleCallbackAJAX : function () {
            $(document).ajaxComplete(function() {
                BlankonAjax.handlePanelScroll();
                BlankonAjax.handleTooltip();
                BlankonAjax.handlePopover();
                BlankonAjax.handlePanelToolAction();
            });
        },

        // =========================================================================
        // PANEL NICESCROLL
        // =========================================================================
        handlePanelScroll: function () {
            if($('.panel-scrollable').length){
                $('.panel-scrollable .panel-body').niceScroll({
                    cursorwidth: '10px',
                    cursorborder: '0px'
                });
            }
        },

        // =========================================================================
        // TOOLTIP
        // =========================================================================
        handleTooltip: function () {
            if($('[data-toggle=tooltip]').length){
                $('[data-toggle=tooltip]').tooltip({
                    animation: 'fade'
                });
            }
        },

        // =========================================================================
        // POPOVER
        // =========================================================================
        handlePopover: function () {
            if($('[data-toggle=popover]').length){
                $('[data-toggle=popover]').popover();
            }
        },

        // =========================================================================
        // PANEL TOOL ACTION
        // =========================================================================
        handlePanelToolAction: function () {
            // Collapse panel
            $('[data-action=collapse]').on('click', function(e){
                var targetCollapse = $(this).parents('.panel').find('.panel-body'),
                    targetCollapse2 = $(this).parents('.panel').find('.panel-sub-heading'),
                    targetCollapse3 = $(this).parents('.panel').find('.panel-footer')
                if((targetCollapse.is(':visible'))) {
                    $(this).find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
                    targetCollapse.slideUp();
                    targetCollapse2.slideUp();
                    targetCollapse3.slideUp();
                }else{
                    $(this).find('i').removeClass('fa-angle-down').addClass('fa-angle-up');
                    targetCollapse.slideDown();
                    targetCollapse2.slideDown();
                    targetCollapse3.slideDown();
                }
                e.stopImmediatePropagation();
            });

            // Remove panel
            $('[data-action=remove]').on('click', function(){
                $(this).parents('.panel').fadeOut();
                // Remove backdrop element panel full size
                if($('body').find('.panel-fullsize').length)
                {
                    $('body').find('.panel-fullsize-backdrop').remove();
                }
            });

            // Refresh panel
            $('[data-action=refresh]').on('click', function(){
                var targetElement = $(this).parents('.panel').find('.panel-body');
                targetElement.append('<div class="indicator"><span class="spinner"></span></div>');
                setInterval(function(){
                    $.getJSON(globalDataPath+'/reload-sample.json', function(json) {
                        $.each(json, function() {
                            // Retrieving data from json...
                            console.log('Retrieving data from json...');
                        });
                        targetElement.find('.indicator').hide();
                    });
                },5000);
            });

            // Expand panel
            $('[data-action=expand]').on('click', function(){
                if($(this).parents(".panel").hasClass('panel-fullsize'))
                {
                    $('body').find('.panel-fullsize-backdrop').remove();
                    $(this).data('bs.tooltip').options.title = 'Expand';
                    $(this).find('i').removeClass('fa-compress').addClass('fa-expand');
                    $(this).parents(".panel").removeClass('panel-fullsize');
                }
                else
                {
                    $('body').append('<div class="panel-fullsize-backdrop"></div>');
                    $(this).data('bs.tooltip').options.title = 'Minimize';
                    $(this).find('i').removeClass('fa-expand').addClass('fa-compress');
                    $(this).parents(".panel").addClass('panel-fullsize');
                }
            });

            // Search panel
            $('[data-action=search]').on('click', function(){
                $(this).parents('.panel').find('.panel-search').toggle(100);
                return false;
            });

        }

    };

}();

// Call main app init
BlankonAjax.init();