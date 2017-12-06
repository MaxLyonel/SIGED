// =========================================================================
// CONFIGURATION ROUTE
// =========================================================================

'use strict';
angular.module('blankonConfig', [])

    // Setup global settings
    .factory('settings', ['$rootScope', function($rootScope) {
        var baseURL = (window.location.href).replace(/^[^\/]+\/\/[^\/]+/,'').replace(/\/live-preview\/admin-seed-project\/.*$/,''), // Setting base url app
            settings = {
                baseURL                 : baseURL,
                pluginPath              : baseURL+'/assets/global/plugins/bower_components',
                pluginCommercialPath    : baseURL+'/assets/commercial/plugins',
                globalImagePath         : baseURL+'/img',
                adminImagePath          : baseURL+'/assets/admin/img',
                cssPath                 : baseURL+'/assets/admin/css',
                dataPath                : baseURL+'/live-preview/admin/angularjs/data'
        };
        $rootScope.settings = settings;
        return settings;
    }])

    // Configuration angular loading bar
    .config(function(cfpLoadingBarProvider) {
        cfpLoadingBarProvider.includeSpinner = true;
    })

    // Configuration event, debug and cache
    .config(['$ocLazyLoadProvider', function($ocLazyLoadProvider) {
        $ocLazyLoadProvider.config({
            events: true,
            debug: true,
            cache:false,
            cssFilesInsertBefore: 'ng_load_plugins_before',
            modules:[
                {
                    name: 'blankonApp.core.demo',
                    files: ['js/modules/core/demo.js']
                }
            ]
        });
    }])

    // Configuration ocLazyLoad with ui router
    .config(function($stateProvider, $locationProvider, $urlRouterProvider) {
        // Redirect any unmatched url
        $urlRouterProvider.otherwise('page-error-404');

        $stateProvider

            // =========================================================================
            // SIGN IN
            // =========================================================================
            .state('signin', {
                url: '/sign-in',
                templateUrl: 'views/sign/sign-in.html',
                data: {
                    pageTitle: 'SIGN IN'
                },
                controller: 'SigninCtrl',
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath, // Create variable css path
                            pluginPath = settings.pluginPath; // Create variable plugin path

                        // you can lazy load files for an existing module
                        return $ocLazyLoad.load(
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/sign.css'
                                    ]
                                },
                                {
                                    files: [
                                        pluginPath+'/jquery-validation/dist/jquery.validate.min.js'
                                    ]
                                },
                                {
                                    name: 'blankonApp.account.signin',
                                    files: [
                                        'js/modules/sign/signin.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // SIGN UP
            // =========================================================================
            .state('signUp', {
                url: '/sign-up',
                templateUrl: 'views/sign/sign-up.html',
                data: {
                    pageTitle: 'SIGN UP'
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath, // Create variable css path
                            pluginPath = settings.pluginPath; // Create variable plugin path

                        // you can lazy load files for an existing module
                        return $ocLazyLoad.load(
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/sign.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // LOST PASSWORD
            // =========================================================================
            .state('lostPassword', {
                url: '/lost-password',
                templateUrl: 'views/sign/lost-password.html',
                data: {
                    pageTitle: 'LOST PASSWORD'
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath; // Create variable css path

                        // you can lazy load files for an existing module
                        return $ocLazyLoad.load(
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/sign.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // LOCK SCREEN
            // =========================================================================
            .state('lockScreen', {
                url: '/lock-screen',
                templateUrl: 'views/sign/lock-screen.html',
                data: {
                    pageTitle: 'LOCK SCREEN'
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath; // Create variable css path

                        // you can lazy load files for an existing module
                        return $ocLazyLoad.load(
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/sign.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // DASHBOARD
            // =========================================================================
            .state('dashboard', {
                url: '/dashboard',
                templateUrl: 'views/dashboard.html',
                data: {
                    pageTitle: 'DASHBOARD',
                    pageHeader: {
                        icon: 'fa fa-home',
                        title: 'Dashboard',
                        subtitle: 'dashboard & statistics'
                    }
                }
            })

            // =========================================================================
            // PROFILE
            // =========================================================================
            .state('pageProfile', {
                url: '/page-profile',
                templateUrl: 'views/pages/page-profile.html',
                data: {
                    pageTitle: 'PROFILE',
                    pageHeader: {
                        icon: 'fa fa-male',
                        title: 'Profile',
                        subtitle: 'profile sample'
                    },
                    breadcrumbs: [
                        {title: 'Pages'},{title: 'Profile'}
                    ]
                }
            })

            // =========================================================================
            // ERROR 400
            // =========================================================================
            .state('pageError403', {
                url: '/page-error-403',
                templateUrl: 'views/pages/page-error-403.html',
                data: {
                    pageTitle: 'ERROR 403',
                    pageHeader: {
                        icon: 'fa fa-ban',
                        title: 'Error 403',
                        subtitle: 'access is denied'
                    },
                    breadcrumbs: [
                        {title: 'Pages'},{title: 'Error 403'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath; // Create variable css path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/error-page.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // ERROR 404
            // =========================================================================
            .state('pageError404', {
                url: '/page-error-404',
                templateUrl: 'views/pages/page-error-404.html',
                data: {
                    pageTitle: 'ERROR 404',
                    pageHeader: {
                        icon: 'fa fa-ban',
                        title: 'Error 404',
                        subtitle: 'page not found'
                    },
                    breadcrumbs: [
                        {title: 'Pages'},{title: 'Error 404'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath; // Create variable css path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/error-page.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // ERROR 500
            // =========================================================================
            .state('pageError500', {
                url: '/page-error-500',
                templateUrl: 'views/pages/page-error-500.html',
                data: {
                    pageTitle: 'ERROR 500',
                    pageHeader: {
                        icon: 'fa fa-ban',
                        title: 'Error 500',
                        subtitle: 'internal server error'
                    },
                    breadcrumbs: [
                        {title: 'Pages'},{title: 'Error 500'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath; // Create variable css path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/error-page.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // FORM ELEMENT
            // =========================================================================
            .state('formElement', {
                url: '/form-element',
                templateUrl: 'views/forms/form-element.html',
                data: {
                    pageTitle: 'FORM ELEMENT',
                    pageHeader: {
                        icon: 'fa fa-list-alt',
                        title: 'Form Elements',
                        subtitle: 'form elements and more'
                    },
                    breadcrumbs: [
                        {title: 'Forms'},{title: 'Form Elements'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/bootstrap-tagsinput/dist/bootstrap-tagsinput.css',
                                        pluginPath+'/jasny-bootstrap-fileinput/css/jasny-bootstrap-fileinput.min.css',
                                        pluginPath+'/chosen_v1.2.0/chosen.min.css'
                                    ]
                                },
                                {
                                    name: 'blankonApp.forms.element',
                                    files: [
                                        pluginPath+'/bootstrap-tagsinput/dist/bootstrap-tagsinput-angular.min.js',
                                        pluginPath+'/jasny-bootstrap-fileinput/js/jasny-bootstrap.fileinput.min.js',
                                        pluginPath+'/holderjs/holder.js',
                                        pluginPath+'/bootstrap-maxlength/bootstrap-maxlength.min.js',
                                        pluginPath+'/jquery-autosize/jquery.autosize.min.js',
                                        pluginPath+'/chosen_v1.2.0/chosen.jquery.min.js',
                                        'js/modules/forms/element.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // FORM ADVANCED
            // =========================================================================
            .state('formAdvanced', {
                url: '/form-advanced',
                templateUrl: 'views/forms/form-advanced.html',
                data: {
                    pageTitle: 'FORM ADVANCED',
                    pageHeader: {
                        icon: 'fa fa-list-alt',
                        title: 'Form Advanced',
                        subtitle: 'form advanced plugins'
                    },
                    breadcrumbs: [
                        {title: 'Forms'},{title: 'Form Advanced'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/dropzone/downloads/css/dropzone.css',
                                        pluginPath+'/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css',
                                        pluginPath+'/bootstrap-datepicker-vitalets/css/datepicker.css'
                                    ]
                                },
                                {
                                    name: 'blankonApp.forms.advanced',
                                    files: [
                                        pluginPath+'/dropzone/downloads/dropzone.min.js',
                                        pluginPath+'/bootstrap-switch/dist/js/bootstrap-switch.min.js',
                                        pluginPath+'/jquery.inputmask/dist/jquery.inputmask.bundle.min.js',
                                        pluginPath+'/bootstrap-datepicker-vitalets/js/bootstrap-datepicker.js',
                                        'js/modules/forms/advanced.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // FORM LAYOUT
            // =========================================================================
            .state('formLayout', {
                url: '/form-layout',
                templateUrl: 'views/forms/form-layout.html',
                data: {
                    pageTitle: 'FORM LAYOUT',
                    pageHeader: {
                        icon: 'fa fa-list-alt',
                        title: 'Form Layouts',
                        subtitle: 'variant form layouts'
                    },
                    breadcrumbs: [
                        {title: 'Forms'},{title: 'Form Layouts'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/bootstrap-fileupload/css/bootstrap-fileupload.min.css',
                                        pluginPath+'/chosen_v1.2.0/chosen.min.css'
                                    ]
                                },
                                {
                                    name: 'blankonApp.forms.layout',
                                    files: [
                                        pluginPath+'/bootstrap-fileupload/js/bootstrap-fileupload.min.js',
                                        pluginPath+'/chosen_v1.2.0/chosen.jquery.min.js',
                                        'js/modules/forms/layout.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // FORM VALIDATION
            // =========================================================================
            .state('formValidation', {
                url: '/form-validation',
                templateUrl: 'views/forms/form-validation.html',
                data: {
                    pageTitle: 'FORM VALIDATION',
                    pageHeader: {
                        icon: 'fa fa-warning',
                        title: 'Form Validations',
                        subtitle: 'form validation samples'
                    },
                    breadcrumbs: [
                        {title: 'Forms'},{title: 'Form Validations'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    name: 'blankonApp.forms.validation',
                                    files: [
                                        pluginPath+'/chosen_v1.2.0/chosen.jquery.min.js',
                                        pluginPath+'/jquery-mockjax/jquery.mockjax.js',
                                        pluginPath+'/jquery-validation/dist/jquery.validate.min.js',
                                        'js/modules/forms/validation.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // TABLE DEFAULT
            // =========================================================================
            .state('tableDefault', {
                url: '/table-default',
                templateUrl: 'views/tables/table-default.html',
                data: {
                    pageTitle: 'TABLE DEFAULT',
                    pageHeader: {
                        icon: 'fa fa-table',
                        title: 'Table',
                        subtitle: 'basic table samples'
                    },
                    breadcrumbs: [
                        {title: 'Tables'},{title: 'Table Default'}
                    ]
                },
                controller: 'TableDefaultCtrl',
                resolve: {
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    name: 'blankonApp.tables.default',
                                    files: [
                                        'js/modules/tables/default.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // TABLE COLOR
            // =========================================================================
            .state('tableColor', {
                url: '/table-color',
                templateUrl: 'views/tables/table-color.html',
                data: {
                    pageTitle: 'TABLE COLOR',
                    pageHeader: {
                        icon: 'fa fa-table',
                        title: 'Table Color',
                        subtitle: 'variant table colors'
                    },
                    breadcrumbs: [
                        {title: 'Tables'},{title: 'Table Color'}
                    ]
                },
                controller: 'TableColorCtrl',
                resolve: {
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    name: 'blankonApp.tables.color',
                                    files: [
                                        'js/modules/tables/color.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // TABLE DATATABLE
            // =========================================================================
            .state('tableDatatable', {
                url: '/table-datatable',
                templateUrl: 'views/tables/table-datatable.html',
                data: {
                    pageTitle: 'DATATABLE',
                    pageHeader: {
                        icon: 'fa fa-table',
                        title: 'Datatable',
                        subtitle: 'responsive datatable samples'
                    },
                    breadcrumbs: [
                        {title: 'Tables'},{title: 'Datatable'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/datatables/css/dataTables.bootstrap.css',
                                        pluginPath+'/datatables/css/datatables.responsive.css',
                                        pluginPath+'/fuelux/dist/css/fuelux.min.css'
                                    ]
                                },
                                {
                                    name: 'blankonApp.tables.datatable',
                                    files: [
                                        pluginPath+'/datatables/js/jquery.dataTables.min.js',
                                        pluginPath+'/datatables/js/dataTables.bootstrap.js',
                                        pluginPath+'/datatables/js/datatables.responsive.js',
                                        pluginPath+'/fuelux/dist/js/fuelux.min.js',
                                        'js/modules/tables/datatable.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS GRID SYSTEM
            // =========================================================================
            .state('componentGridSystem', {
                url: '/component-grid-system',
                templateUrl: 'views/components/component-grid-system.html',
                data: {
                    pageTitle: 'GRID SYSTEM',
                    pageHeader: {
                        icon: 'fa fa-columns',
                        title: 'Grid Layout',
                        subtitle: 'grid system support'
                    },
                    breadcrumbs: [
                        {title: 'Layout'},{title: 'Grid'}
                    ]
                }
            })

            // =========================================================================
            // COMPONENTS BUTTONS
            // =========================================================================
            .state('componentButtons', {
                url: '/component-buttons',
                templateUrl: 'views/components/component-buttons.html',
                data: {
                    pageTitle: 'BUTTONS',
                    pageHeader: {
                        icon: 'fa fa-square',
                        title: 'Buttons',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Buttons'}
                    ]
                }
            })

            // =========================================================================
            // COMPONENTS TYPOGRAPHY
            // =========================================================================
            .state('componentTypography', {
                url: '/component-typography',
                templateUrl: 'views/components/component-typography.html',
                data: {
                    pageTitle: 'TYPOGRAPHY',
                    pageHeader: {
                        icon: 'fa fa-text-height',
                        title: 'Typography',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Typography'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/google-code-prettify/bin/prettify.min.css'
                                    ]
                                },
                                {
                                    name: 'blankonApp.components.typography',
                                    files: [
                                        pluginPath+'/google-code-prettify/bin/prettify.min.js',
                                        'js/modules/components/typography.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS PANEL
            // =========================================================================
            .state('componentPanel', {
                url: '/component-panel',
                templateUrl: 'views/components/component-panel.html',
                data: {
                    pageTitle: 'PANELS',
                    pageHeader: {
                        icon: 'fa fa-list-alt',
                        title: 'Panel',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Panel'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/c3/c3.min.css'
                                    ]
                                },
                                {
                                    name: 'gridshore.c3js.chart',
                                    files: [
                                        pluginPath+'/d3/d3.min.js',
                                        pluginPath+'/c3/c3.min.js',
                                        pluginPath+'/c3-angular/c3js-directive.js'
                                    ]
                                },
                                {
                                    name: 'blankonApp.charts.c3js',
                                    files: [
                                        'js/modules/charts/c3js.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS ALERTS
            // =========================================================================
            .state('componentAlerts', {
                url: '/component-alerts',
                templateUrl: 'views/components/component-alerts.html',
                data: {
                    pageTitle: 'ALERTS',
                    pageHeader: {
                        icon: 'fa fa-info-circle',
                        title: 'Alerts',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Alerts'}
                    ]
                },
                controller: 'AlertCtrl',
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // you can lazy load files for an existing module
                            [
                                {
                                    name: 'ui.bootstrap',
                                    files: [
                                        pluginPath+'/angular-bootstrap/ui-bootstrap-tpls.min.js'
                                    ]
                                },
                                {
                                    name: 'ui.bootstrap.alert',
                                    files: [
                                        'js/modules/bootstrap/alert.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS MODALS
            // =========================================================================
            .state('componentModals', {
                url: '/component-modals',
                templateUrl: 'views/components/component-modals.html',
                data: {
                    pageTitle: 'MODALS',
                    pageHeader: {
                        icon: 'fa fa-circle-o-notch',
                        title: 'Modals',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Modals'}
                    ]
                },
                controller: 'AccordionCtrl',
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // you can lazy load files for an existing module
                            [
                                {
                                    name: 'ui.bootstrap',
                                    files: [
                                        pluginPath+'/angular-bootstrap/ui-bootstrap-tpls.min.js'
                                    ]
                                },
                                {
                                    name: 'ui.bootstrap.accordion',
                                    files: ['js/modules/bootstrap/accordion.js']
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS VIDEO
            // =========================================================================
            .state('componentVideo', {
                url: '/component-video',
                templateUrl: 'views/components/component-video.html',
                data: {
                    pageTitle: 'VIDEO',
                    pageHeader: {
                        icon: 'fa fa-video-camera',
                        title: 'Video',
                        subtitle: 'responsive embed'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Video'}
                    ]
                }
            })

            // =========================================================================
            // COMPONENTS TABS & ACCORDION
            // =========================================================================
            .state('componentTabsaccordion', {
                url: '/component-tabsaccordion',
                templateUrl: 'views/components/component-tabsaccordion.html',
                data: {
                    pageTitle: 'TABS & ACCORDION',
                    pageHeader: {
                        icon: 'fa fa-bars',
                        title: 'Tabs & Accordion',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Tabs & Accordion'}
                    ]
                },
                controller: 'AccordionCtrl',
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // you can lazy load files for an existing module
                            [
                                {
                                    name: 'ui.bootstrap',
                                    files: [
                                        pluginPath+'/angular-bootstrap/ui-bootstrap-tpls.min.js'
                                    ]
                                },
                                {
                                    name: 'ui.bootstrap.accordion',
                                    files: ['js/modules/bootstrap/accordion.js']
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS SLIDERS
            // =========================================================================
            .state('componentSliders', {
                url: '/component-sliders',
                templateUrl: 'views/components/component-sliders.html',
                data: {
                    pageTitle: 'SLIDERS',
                    pageHeader: {
                        icon: 'fa fa-sliders',
                        title: 'Sliders',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Sliders'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var pluginPath = settings.pluginPath; // Create variable plugin path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        pluginPath+'/ion.rangeSlider/css/ion.rangeSlider.css'
                                    ]
                                },
                                {
                                    name: 'blankonApp.components.slider',
                                    files: [
                                        pluginPath+'/ion.rangeSlider/js/ion.rangeSlider.min.js',
                                        'js/modules/pages/slider.js'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS ICON GLYPHICONS
            // =========================================================================
            .state('componentGlyphicons', {
                url: '/component-glyphicons',
                templateUrl: 'views/components/component-glyphicons.html',
                data: {
                    pageTitle: 'GLYPHICONS',
                    pageHeader: {
                        icon: 'fa fa-paw',
                        title: 'Glyphicons',
                        subtitle: 'icon components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Icons'},{title: 'Glyphicons'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath; // Create variable css path

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/glyphicons.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS ICON FONT AWESOME
            // =========================================================================
            .state('componentFontAwesome', {
                url: '/component-font-awesome',
                templateUrl: 'views/components/component-font-awesome.html',
                data: {
                    pageTitle: 'FONT AWESOME',
                    pageHeader: {
                        icon: 'fa fa-paw',
                        title: 'Font Awesome',
                        subtitle: 'icon components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Icons'},{title: 'Font Awesome'}
                    ]
                }
            })

            // =========================================================================
            // COMPONENTS SIMPLE LINE ICONS
            // =========================================================================
            .state('componentSimpleLineIcons', {
                url: '/component-simple-line-icons',
                templateUrl: 'views/components/component-simple-line-icons.html',
                data: {
                    pageTitle: 'SIMPLE LINE ICONS',
                    pageHeader: {
                        icon: 'fa fa-paw',
                        title: 'Simple Line Icons',
                        subtitle: 'icon components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Icons'},{title: 'Simple Line Icons'}
                    ]
                },
                resolve: {
                    deps: ['$ocLazyLoad', 'settings', function($ocLazyLoad, settings) {

                        var cssPath = settings.cssPath, // Create variable css path
                            pluginPath = settings.pluginPath;

                        return $ocLazyLoad.load( // You can lazy load files for an existing module
                            [
                                {
                                    insertBefore: '#load_css_before',
                                    files: [
                                        cssPath+'/pages/icon.css',
                                        pluginPath+'/simple-line-icons/css/simple-line-icons.css'
                                    ]
                                }
                            ]
                        );
                    }]
                }
            })

            // =========================================================================
            // COMPONENTS OTHER
            // =========================================================================
            .state('componentOther', {
                url: '/component-other',
                templateUrl: 'views/components/component-other.html',
                data: {
                    pageTitle: 'OTHER COMPONENT',
                    pageHeader: {
                        icon: 'fa fa-slack',
                        title: 'Other Component',
                        subtitle: 'general ui components'
                    },
                    breadcrumbs: [
                        {title: 'Components'},{title: 'Icons'},{title: 'Others'}
                    ]
                }
            });

    })

    // Init app run
    .run(["$rootScope", "settings", "$state", function($rootScope, settings, $state, $location) {
        $rootScope.$state = $state; // state to be accessed from view
        $rootScope.$location = $location; // location to be accessed from view
        $rootScope.settings = settings; // global settings to be accessed from view
    }]);