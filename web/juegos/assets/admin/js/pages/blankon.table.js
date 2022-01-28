'use strict';
var BlankonTable = function () {

    // =========================================================================
    // SETTINGS APP
    // =========================================================================
    var globalPluginsPath = BlankonApp.handleBaseURL()+'/assets/global/plugins/bower_components';

    return {

        // =========================================================================
        // CONSTRUCTOR APP
        // =========================================================================
        init: function () {
            BlankonTable.datatable();
        },

        // =========================================================================
        // DATATABLE
        // =========================================================================
        datatable: function () {
            var responsiveHelperAjax = undefined;
            var responsiveHelperDom = undefined;
            var breakpointDefinition = {
                tablet: 1024,
                phone : 480
            };

            var tableAjax = $('#datatable-ajax');
            var tableDom = $('#datatable-dom');

            // Using AJAX
            tableAjax.dataTable({
                autoWidth      : false,
                ajax           : globalPluginsPath+'/datatables/datatable-sample.json',
                preDrawCallback: function () {
                    // Initialize the responsive datatables helper once.
                    if (!responsiveHelperAjax) {
                        responsiveHelperAjax = new ResponsiveDatatablesHelper(tableAjax, breakpointDefinition);
                    }
                },
                rowCallback    : function (nRow) {
                    responsiveHelperAjax.createExpandIcon(nRow);
                },
                drawCallback   : function (oSettings) {
                    responsiveHelperAjax.respond();
                }
            });

            // Using DOM
            // Remove arrow datatable
            $.extend( true, $.fn.dataTable.defaults, {
                "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0, 1, 2, 5 ] } ]
            } );
            tableDom.dataTable({
                autoWidth        : true,
                preDrawCallback: function () {
                    // Initialize the responsive datatables helper once.
                    if (!responsiveHelperDom) {
                        responsiveHelperDom = new ResponsiveDatatablesHelper(tableDom, breakpointDefinition);
                    }
                },
                rowCallback    : function (nRow) {
                    responsiveHelperDom.createExpandIcon(nRow);
                },
                drawCallback   : function (oSettings) {
                    responsiveHelperDom.respond();
                }
            });

            // Repeater

            var delays = ['300', '600', '900', '1200'];
            var products = [
                {
                    "codeProduct": "#101",
                    "name": "Canon EOS Rebel",
                    "available": "5",
                    "price": "US $349.95",
                    "itemCondition": "Manufacturer",
                    "sold": "5",
                    "review": "253 people",
                    "ThumbnailAltText": "Canon EOS Rebel",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/1.jpg",
                    "type": "electronics, camera"
                },
                {
                    "codeProduct": "#102",
                    "name": "Samsung Galaxy S III",
                    "available": "25",
                    "price": "US $197.42",
                    "itemCondition": "New other",
                    "sold": "23",
                    "review": "563 people",
                    "ThumbnailAltText": "Samsung Galaxy S III",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/2.jpg",
                    "type": "electronics, mobile, gadget"
                },
                {
                    "codeProduct": "#103",
                    "name": "Samsung 32' LED",
                    "available": "231",
                    "price": "US $199.99",
                    "itemCondition": "New",
                    "sold": "67",
                    "review": "342 people",
                    "ThumbnailAltText": "Samsung 32' LED",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/3.jpg",
                    "type": "electronics, tv"
                },
                {
                    "codeProduct": "#104",
                    "name": "IOTA - Love Come Wicked",
                    "available": "200",
                    "price": "US $19.99",
                    "itemCondition": "Used",
                    "sold": "45",
                    "review": "333 people",
                    "ThumbnailAltText": "IOTA - Love Come Wicked",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/4.jpg",
                    "type": "music"
                },
                {
                    "codeProduct": "#105",
                    "name": "Jimmy Van Eaton",
                    "available": "567",
                    "price": "US $11.50",
                    "itemCondition": "Used",
                    "sold": "67",
                    "review": "102 people",
                    "ThumbnailAltText": "Jimmy Van Eaton",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/5.jpg",
                    "type": "music"
                },
                {
                    "codeProduct": "#106",
                    "name": "Sexy Fashion Women's",
                    "available": "458",
                    "price": "US $6.39",
                    "itemCondition": "New with tags",
                    "sold": "234",
                    "review": "642 people",
                    "ThumbnailAltText": "Sexy Fashion Women's",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/6.jpg",
                    "type": "fashion"
                },
                {
                    "codeProduct": "#107",
                    "name": "Korean Fashion Women's",
                    "available": "843",
                    "price": "US $7.99",
                    "itemCondition": "New with tags",
                    "sold": "543",
                    "review": "643 people",
                    "ThumbnailAltText": "Korean Fashion Women's",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/7.jpg",
                    "type": "fashion, korean"
                },
                {
                    "codeProduct": "#108",
                    "name": "Fashion Women Loose",
                    "available": "290",
                    "price": "US $7.58",
                    "itemCondition": "New with tags",
                    "sold": "312",
                    "review": "<input type='checkbox' name='vehicle' value='Bike'>",
                    "ThumbnailAltText": "Fashion Women Loose",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/8.jpg",
                    "type": "fashion"
                },
                {
                    "codeProduct": "#109",
                    "name": "10 Seeds Miracle Fruits",
                    "available": "340",
                    "price": "US $15.99",
                    "itemCondition": "New with tags",
                    "sold": "290",
                    "review": "110 people",
                    "ThumbnailAltText": "10 Seeds Miracle Fruits",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/9.jpg",
                    "type": "home_garden"
                },
                {
                    "codeProduct": "#110",
                    "name": "10 Seeds Triphasia",
                    "available": "563",
                    "price": "US $9.99",
                    "itemCondition": "New",
                    "sold": "342",
                    "review": "876 people",
                    "ThumbnailAltText": "10 Seeds Triphasia",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/10.jpg",
                    "type": "home_garden"
                },
                {
                    "codeProduct": "#111",
                    "name": "Nike Men's Mercurial",
                    "available": "742",
                    "price": "US $29.99",
                    "itemCondition": "New without box",
                    "sold": "732",
                    "review": "653 people",
                    "ThumbnailAltText": "Nike Men's Mercurial",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/11.jpg",
                    "type": "sport, all"
                },
                {
                    "codeProduct": "#112",
                    "name": "CR7 Jersey Real Madrid",
                    "available": "345",
                    "price": "US $24.99",
                    "itemCondition": "New",
                    "sold": "300",
                    "review": "456 people",
                    "ThumbnailAltText": "CR7 Jersey Real Madrid",
                    "ThumbnailImage": "/blankon-fullpack-admin-theme/img/media/shop/12.jpg",
                    "type": "sport, jersey"
                }
            ];
            var dataSource;

            dataSource = function(options, callback){
                var items = filtering(options,products);

                var resp = {
                    count: items.length,
                    items: [],
                    page: options.pageIndex,
                    pages: Math.ceil(items.length/(options.pageSize || 50))
                };
                var i, items, l;

                i = options.pageIndex * (options.pageSize || 50);
                l = i + (options.pageSize || 50);
                l = (l <= resp.count) ? l : resp.count;
                resp.start = i + 1;
                resp.end = l;

                if(options.view==='list' || options.view==='thumbnail'){
                    if(options.view==='list'){
                        resp.columns = columns;
                        for(i; i<l; i++){
                            resp.items.push(items[i]);
                            alert(items[i]);
                        }
                    }else{
                        for(i; i<l; i++){
                            resp.items.push({
                                name: items[i].name,
                                src: items[i].ThumbnailImage
                            });
                        }
                    }

                    setTimeout(function(){
                        callback(resp);
                    }, delays[Math.floor(Math.random() * 4)]);
                }
            };

                      

            // REPEATER
            $('#repeaterIllustration').repeater({
                dataSource: dataSource
            });

            $('#myRepeater').repeater({
                dataSource: dataSource
            });

            $('#myRepeaterList').repeater({
                dataSource: dataSource
            });

            $('#myRepeaterThumbnail').repeater({
                dataSource: dataSource,
                thumbnail_template: '<div class="thumbnail repeater-thumbnail" style="background: {{color}};"><img height="75" src="{{src}}" width="65"><span>{{name}}</span></div>'
            });

        }

    };

}();

// Call main app init
BlankonTable.init();