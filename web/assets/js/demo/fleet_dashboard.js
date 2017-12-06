$(function () {
    fuelCosts();
    serviceCosts();
    latestMeterReaadings();
});

function fuelCosts() {
    var vehicle_3 = [[1388534400000, 80], [1391212800000, 40], [1393632000000, 47], [1396310400000, 22], [1398902400000, 24]];
 
    var data = [
        { label: "Fuel Costs", data: vehicle_3 }
    ];
 
    $.plot($("#fuel-costs"), data, {
        series: {
            bars: {
                show: true,
                barWidth: 12*24*60*60*350,
                lineWidth: 0,
                order: 1,
                fillColor: {
                    colors: [{
                        opacity: 1
                    }, {
                        opacity: 0.8
                    }]
                }
            }
        },
        xaxis: {
            mode: "time",
            min: 1387497600000,
            max: 1400112000000,
            tickLength: 0,
            tickSize: [1, "month"],
            axisLabel: 'Month',
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 13,
            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            axisLabelPadding: 15
        },
        yaxis: {
            axisLabel: 'Value',
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 13,
            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            axisLabelPadding: 5
        },
        grid: {
            hoverable: true,
            borderWidth: 0
        },
        legend: {
            backgroundColor: "#fff",
            labelBoxBorderColor: "none"
        },
        colors: [successColor ]
    });

    var previous_point = null;
    var previous_label = null;

    $("#fuel-costs").on("plothover", function (event, pos, item) {
        if (item) {
            if ((previous_point != item.dataIndex) || (previous_label != item.series.label)) {
                previous_point = item.dataIndex;
                previous_label = item.series.label;
 
                $("#bar_tooltip").remove();
 
                var x = get_month_name(item.series.data[item.dataIndex][0]),
                    y = item.datapoint[1],
                    z = item.series.color;
 
                show_tooltip(item.pageX, item.pageY,
                    "<div style='text-align: center;'><b>" + item.series.label + "</b><br />" + x + ": " + y + "</div>",
                    z);
            }
        } else {
            $("#bar_tooltip").remove();
            previous_point = null;
            previous_label = null;
        }
    });

}

function serviceCosts() {
    var vehicle_1 = [[1388534400000, 120], [1391212800000, 70],  [1393632000000, 100], [1396310400000, 60], [1398902400000, 35]];
 
    var data = [
        { label: "Service Costs", data: vehicle_1 }
    ];
 
    $.plot($("#service-costs"), data, {
        series: {
            bars: {
                show: true,
                barWidth: 12*24*60*60*350,
                lineWidth: 0,
                order: 1,
                fillColor: {
                    colors: [{
                        opacity: 1
                    }, {
                        opacity: 0.8
                    }]
                }
            }
        },
        xaxis: {
            mode: "time",
            min: 1387497600000,
            max: 1400112000000,
            tickLength: 0,
            tickSize: [1, "month"],
            axisLabel: 'Month',
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 13,
            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            axisLabelPadding: 15
        },
        yaxis: {
            axisLabel: 'Value',
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 13,
            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            axisLabelPadding: 5
        },
        grid: {
            hoverable: true,
            borderWidth: 0
        },
        legend: {
            backgroundColor: "#fff",
            labelBoxBorderColor: "none"
        },
        colors: [infoColor ]
    });

    var previous_point = null;
    var previous_label = null;

    $("#service-costs").on("plothover", function (event, pos, item) {
        if (item) {
            if ((previous_point != item.dataIndex) || (previous_label != item.series.label)) {
                previous_point = item.dataIndex;
                previous_label = item.series.label;
 
                $("#bar_tooltip").remove();
 
                var x = get_month_name(item.series.data[item.dataIndex][0]),
                    y = item.datapoint[1],
                    z = item.series.color;
 
                show_tooltip(item.pageX, item.pageY,
                    "<div style='text-align: center;'><b>" + item.series.label + "</b><br />" + x + ": " + y + "</div>",
                    z);
            }
        } else {
            $("#bar_tooltip").remove();
            previous_point = null;
            previous_label = null;
        }
    });

}

function latestMeterReaadings() {
    var vehicle_1 = [[1388534400000, 120], [1391212800000, 70],  [1393632000000, 100], [1396310400000, 60], [1398902400000, 35]];
    var vehicle_2 = [[1388534400000, 90], [1391212800000, 60], [1393632000000, 30], [1396310400000, 73], [1398902400000, 30]];
    var vehicle_3 = [[1388534400000, 80], [1391212800000, 40], [1393632000000, 47], [1396310400000, 22], [1398902400000, 24]];
 
    var data = [
        { label: "Vehicle 1", data: vehicle_1 },
        { label: "Vehicle 2", data: vehicle_2 },
        { label: "Vehicle 3", data: vehicle_3 }
    ];
 
    $.plot($("#latest-meter-readings"), data, {
        series: {
            bars: {
                show: true,
                barWidth: 12*24*60*60*350,
                lineWidth: 0,
                order: 1,
                fillColor: {
                    colors: [{
                        opacity: 1
                    }, {
                        opacity: 0.8
                    }]
                }
            }
        },
        xaxis: {
            mode: "time",
            min: 1387497600000,
            max: 1400112000000,
            tickLength: 0,
            tickSize: [1, "month"],
            axisLabel: 'Month',
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 13,
            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            axisLabelPadding: 15
        },
        yaxis: {
            axisLabel: 'Value',
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 13,
            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            axisLabelPadding: 5
        },
        grid: {
            hoverable: true,
            borderWidth: 0
        },
        legend: {
            backgroundColor: "#fff",
            labelBoxBorderColor: "none"
        },
        colors: [infoColor , successColor , primaryColor ]
    });

    var previous_point = null;
    var previous_label = null;

    $("#latest-meter-readings").on("plothover", function (event, pos, item) {
        if (item) {
            if ((previous_point != item.dataIndex) || (previous_label != item.series.label)) {
                previous_point = item.dataIndex;
                previous_label = item.series.label;
 
                $("#bar_tooltip").remove();
 
                var x = get_month_name(item.series.data[item.dataIndex][0]),
                    y = item.datapoint[1],
                    z = item.series.color;
 
                show_tooltip(item.pageX, item.pageY,
                    "<div style='text-align: center;'><b>" + item.series.label + "</b><br />" + x + ": " + y + "</div>",
                    z);
            }
        } else {
            $("#bar_tooltip").remove();
            previous_point = null;
            previous_label = null;
        }
    });
}

function show_tooltip(x, y, contents, z) {
    $('<div id="bar_tooltip">' + contents + '</div>').css({
        top: y - 45,
        left: x - 28,
        'border-color': z,
    }).appendTo("body").fadeIn();
}

function get_month_name(month_timestamp) {
    var month_date = new Date(month_timestamp);
    var month_numeric = month_date.getMonth();
    var month_array = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var month_string = month_array[month_numeric];

    return month_string;
}