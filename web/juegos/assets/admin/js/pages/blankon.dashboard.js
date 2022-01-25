var BlankonDashboard = function () {

    return {

        // =========================================================================
        // CONSTRUCTOR APP
        // =========================================================================
        init: function () {
            BlankonDashboard.counterOverview();
            BlankonDashboard.callModal1();
            //BlankonDashboard.weatherIcons();
            BlankonDashboard.gritterNotification();
            BlankonDashboard.visitorChart();
            BlankonDashboard.realtimeStatusChart();
            BlankonDashboard.countNumber();
            //BlankonDashboard.dropzone();
        },

        // =========================================================================
        // COUNTER OVERVIEW
        // =========================================================================
        counterOverview: function () {
            if($('.counter').length){
                $('.counter').counterUp({
                    delay: 10,
                    time: 4000
                });
            }
        },

        // =========================================================================
        // CALL MODAL FIRST
        // =========================================================================
        callModal1: function () {
            $('#modal-bootstrap-tour').modal(
                {
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                }
            );
        },

        // =========================================================================
        // CALL MODAL SECOND
        // =========================================================================
        callModal2: function () {
            $('#modal-bootstrap-tour-new-features').modal(
                {
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                }
            );
        },

        // =========================================================================
        // INITIALIZE THE TOUR
        // =========================================================================
        handleTour: function () {
            // Instance the tour
            var tour = new Tour({
                name: "tour",
                steps: [
                    {
                        element: "#tour-1",
                        title: "Bienvenidos",
                        content: "Sitio Web de información estadistica y reportes del Ministerio de Educación - Bolivia",
                        placement: "bottom"
                    },
                    {
                        element: "#tour-2",
                        title: "Botón para barra lateral de navegación",
                        content: "Haga clic para minimizar o maximizar en menú lateral de navegación.",
                        placement: "bottom"
                    },
                    {
                        element: "#tour-3",
                        title: "Barra lateral de navegación",
                        content: "Cabecera - Información referencial del usuario.",
                        placement: "right"
                    },
                    {
                        element: "#tour-4",
                        title: "Barra lateral de navegación",
                        content: "Menú principal en la barra lateral de navegación.",
                        placement: "right"
                    },
                    {
                        element: "#tour-5",
                        title: "Barra lateral de navegación",
                        content: "Herramientas adicionales en la barra lateral de navegación",
                        placement: "top"
                    },
                    {
                        element: "#tour-6",
                        title: "Contenido cabecera",
                        content: "Título, subtítulo y gestión educativa de la información desplegada.",
                        placement: "bottom"
                    },                    
                    {
                        element: "#tour-7",
                        title: "Contenido de la página",
                        content: "Información estadística según nivel de desagregación territorial",
                        placement: "top"
                    },
                    {
                        element: "#tour-8",
                        title: "Contenido de la página",
                        content: "Sub Nivel de Desagregación Territorial",
                        placement: "top"
                    },
                    {
                        element: "#tour-9",
                        title: "Estadística",
                        content: "Descripción del cruce de información",
                        placement: "top"
                    },
                    {
                        element: "#tour-10",
                        title: "Estadística",
                        content: "Enlace para desplegar la representación gráfica",
                        placement: "top"
                    },
                    {
                        element: "#tour-11",
                        title: "Estadística",
                        content: "Contenido del cruce de información",
                        placement: "top"
                    },
                    {
                        element: "#tour-12",
                        title: "Sub nivel de desagregación territorial",
                        content: "Descripción del sub nivel de desagregación territorial",
                        placement: "top"
                    },
                    {
                        element: "#tour-13",
                        title: "Sub nivel de desagregación territorial",
                        content: "Enlace para descarga en formato PDF, los diferentes cruces de información por sub nivel de desagregación territorial",
                        placement: "left"
                    },
                    {
                        element: "#tour-14",
                        title: "Sub nivel de desagregación territorial",
                        content: "Enlace para descarga en formato EXCEL, los diferentes cruces de información por sub nivel de desagregación territorial",
                        placement: "left"
                    },
                    {
                        element: "#tour-15",
                        title: "Sub nivel de desagregación territorial",
                        content: "Contenido del sub nivel de desagregación territorial",
                        placement: "left"
                    }                    
                ],
                container: "body",
                next: 0,
                prev: 0,
                keyboard: true,
                storage: false,
                debug: false,
                backdrop: false,
                backdropContainer: 'body',
                backdropPadding: 0,
                redirect: true,
                orphan: false,
                duration: false,
                delay: false,
                basePath: "",
                template: "<div class='popover tour'>" +
                "<div class='arrow'></div>" +
                "<h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div>" +
                "<div class='popover-navigation'>" +
                "<button class='btn btn-primary btn-sm' data-role='prev'>« Ant.</button>" +
                "<span data-role='separator'></span>" +
                "<button class='btn btn-primary btn-sm' data-role='next'>Sig. »</button>" +
                "<span data-role='separator'></span>" +
                "<button class='btn btn-danger btn-sm' data-role='end'>Terminar</button>" +
                "</div>" +
                "</div>" +
                "</div>",
                afterGetState: function (key, value) {},
                afterSetState: function (key, value) {},
                afterRemoveState: function (key, value) {},
                onStart: function (tour) {},
                onEnd: function (tour) {
                    //$('#modal-bootstrap-tour-end').modal(
                    //    {
                    //        show: true
                    //    }
                    //);
                    //$('#modal-bootstrap-tour-end').on('hide.bs.modal', function () {
                    //    BlankonDashboard.sessionTimeout();
                    //});
                },
                onShow: function (tour) {},
                onShown: function (tour) {},
                onHide: function (tour) {},
                onHidden: function (tour) {},
                onNext: function (tour) {},
                onPrev: function (tour) {},
                onPause: function (tour, duration) {},
                onResume: function (tour, duration) {},
                onRedirectError: function (tour) {}
            });

            // Initialize the tour
            tour.init();

            // Start the tour
            tour.start();
        },

        // =========================================================================
        // WEATHER ICONS
        // =========================================================================
        //weatherIcons: function () {
        //    var icons = new Skycons({"color": "white"},{"resizeClear": true}),
        //        list  = [
        //            "clear-day", "clear-night", "partly-cloudy-day",
        //            "partly-cloudy-night", "cloudy", "rain", "sleet", "snow", "wind",
        //            "fog"
        //        ],
        //        i;

        //    for(i = list.length; i--; )
        //        icons.set(list[i], list[i]);

        //    icons.play();
        //},

        // =========================================================================
        // GRITTER NOTIFICATION
        // =========================================================================
        gritterNotification: function () {
            // display marketing alert only once
            if($('#wrapper').css('opacity')) {
                if (!$.cookie('intro')) {

                    // Gritter notification intro 1
                    setTimeout(function () {
                        var unique_id = $.gritter.add({
                            // (string | mandatory) the heading of the notification
                            title: 'Welcome to Blankon',
                            // (string | mandatory) the text inside the notification
                            text: 'Blankon is a theme fullpack admin template powered by Twitter bootstrap 3 front-end framework.',
                            // (string | optional) the image to display on the left
                            image: BlankonApp.handleBaseURL()+'/assets/global/img/icon/64/contact.png',
                            // (bool | optional) if you want it to fade out on its own or just sit there
                            sticky: false,
                            // (int | optional) the time you want it to be alive for before fading out
                            time: ''
                        });

                        // You can have it return a unique id, this can be used to manually remove it later using
                        setTimeout(function () {
                            $.gritter.remove(unique_id, {
                                fade: true,
                                speed: 'slow'
                            });
                        }, 12000);
                    }, 5000);

                    // Gritter notification intro 2
                    setTimeout(function () {
                        $.gritter.add({
                            // (string | mandatory) the heading of the notification
                            title: 'Playing sounds',
                            // (string | mandatory) the text inside the notification
                            text: 'Blankon made for playing small sounds, will help you with this task. Please make your sound system is active',
                            // (string | optional) the image to display on the left
                            image: BlankonApp.handleBaseURL()+'/assets/global/img/icon/64/sound.png',
                            // (bool | optional) if you want it to fade out on its own or just sit there
                            sticky: true,
                            // (int | optional) the time you want it to be alive for before fading out
                            time: ''
                        });
                    }, 8000);

                    // Set cookie intro
                    $.cookie('intro',1, {expires: 1});
                }
            }
        },

        // =========================================================================
        // VISITOR CHART & SERVER STATUS
        // =========================================================================
        visitorChart: function () {
            if($('#visitor-chart').length){
                $.plot("#visitor-chart", [{
                    label: "New Visitor",
                    color: "rgba(0, 177, 225, 0.35)",
                    data: [
                        ["Jan", 450],
                        ["Feb", 532],
                        ["Mar", 367],
                        ["Apr", 245],
                        ["May", 674],
                        ["Jun", 897],
                        ["Jul", 745]
                    ]
                }, {
                    label: "Old Visitor",
                    color: "rgba(233, 87, 63, 0.36)",
                    data: [
                        ["Jan", 362],
                        ["Feb", 452],
                        ["Mar", 653],
                        ["Apr", 756],
                        ["May", 670],
                        ["Jun", 352],
                        ["Jul", 243]
                    ]
                }], {
                    series: {
                        lines: { show: false },
                        splines: {
                            show: true,
                            tension: 0.4,
                            lineWidth: 2,
                            fill: 0.5
                        },
                        points: {
                            show: true,
                            radius: 4
                        }
                    },
                    grid: {
                        borderColor: "transparent",
                        borderWidth: 0,
                        hoverable: true,
                        backgroundColor: "transparent"
                    },
                    tooltip: true,
                    tooltipOpts: { content: "%x : %y" + " People" },
                    xaxis: {
                        tickColor: "transparent",
                        mode: "categories"
                    },
                    yaxis: { tickColor: "transparent" },
                    shadowSize: 0
                });
            }
        },

        // =========================================================================
        // REAL TIME STATUS
        // =========================================================================
        realtimeStatusChart: function () {
            if($('#realtime-status-chart').length){
                var data = [], totalPoints = 50;

                function getRandomData() {

                    if (data.length > 0)
                        data = data.slice(1);

                    // Do a random walk
                    while (data.length < totalPoints) {

                        var prev = data.length > 0 ? data[data.length - 1] : 50,
                            y = prev + Math.random() * 10 - 5;

                        if (y < 0) {
                            y = 0;
                        } else if (y > 100) {
                            y = 100;
                        }
                        data.push(y);
                    }

                    // Zip the generated y values with the x values
                    var res = [];
                    for (var i = 0; i < data.length; ++i) {
                        res.push([i, data[i]])
                    }
                    return res;
                }


                // Set up the control widget
                var updateInterval = 1000;

                var plot4 = $.plot("#realtime-status-chart", [ getRandomData() ], {
                    colors: ["#F6BB42"],
                    series: {
                        lines: {
                            fill: true,
                            lineWidth: 0
                        },
                        shadowSize: 0	// Drawing is faster without shadows
                    },
                    grid: {
                        borderColor: '#ddd',
                        borderWidth: 1,
                        labelMargin: 10
                    },
                    xaxis: {
                        color: '#eee'
                    },
                    yaxis: {
                        min: 0,
                        max: 100,
                        color: '#eee'
                    }
                });

                function update() {

                    plot4.setData([getRandomData()]);

                    // Since the axes don't change, we don't need to call plot.setupGrid()
                    plot4.draw();
                    setTimeout(update, updateInterval);
                }

                update();
            }
        },

        // =========================================================================
        // DEMO COUNT NUMBER
        // =========================================================================
        countNumber: function () {
            $.fn.digits = function(){
                return this.each(function(){
                    $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") );
                })
            };
            function counter($selector){
                $({countNum: $('.counter-' + $selector).text()}).animate({countNum: $('.counter-' + $selector).data('counter')}, {
                    duration: 8000,
                    easing:'linear',
                    step: function() {
                        $('.counter-' + $selector).text(Math.floor(this.countNum)).digits();
                    },
                    complete: function() {
                        $('.counter-' + $selector).text(this.countNum).digits();
                    }
                });
            }
            // Check if wrapper design is opacity 1
            if($('#wrapper').css('opacity')) {
                counter('visit');
                counter('unique');
                counter('page');
            }
        },

        // =========================================================================
        // SESSION TIMEOUT
        // =========================================================================
        sessionTimeout: function () {
            if($('.demo-dashboard-session').length){
                $.sessionTimeout({
                    title: 'JUST DEMO Your session is about to expire!',
                    logoutButton: 'Logout',
                    keepAliveButton: 'Stay Connected',
                    countdownMessage: 'Your session will be redirecting in {timer} seconds.',
                    countdownBar: true,
                    keepAliveUrl: '#',
                    logoutUrl: 'page-signin.html',
                    redirUrl: 'page-lock-screen.html',
                    ignoreUserActivity: true,
                    warnAfter: 50000,
                    redirAfter: 65000
                });
            }
        },

        // =========================================================================
        // DROPZONE UPLOAD
        // =========================================================================
        //dropzone: function () {
        //    Dropzone.options.myDropzone = {
        //        init: function() {
        //            this.on("addedfile", function(file) {
                        // Create the remove button
        //                var removeButton = Dropzone.createElement("<button class='btn btn-sm btn-block btn-danger'>Remove file</button>");

                        // Capture the Dropzone instance as closure.
        //                var _this = this;

                        // Listen to the click event
        //                removeButton.addEventListener("click", function(e) {
                            // Make sure the button click doesn't submit the form:
        //                    e.preventDefault();
        //                    e.stopPropagation();

                            // Remove the file preview.
        //                    _this.removeFile(file);
                            // If you want to the delete the file on the server as well,
                            // you can do the AJAX request here.
        //                });

                        // Add the button to the file preview element.
        //                file.previewElement.appendChild(removeButton);
        //            });
        //        }
        //    }
        //}

    };

}();

// Call main app init
BlankonDashboard.init();
