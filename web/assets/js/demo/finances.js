google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawWorldMap);
function drawWorldMap() {
	var data = google.visualization.arrayToDataTable([
		['Country', 'Sales this month'],
		['Germany', 200],
		['United States', 300],
		['Brazil', 400],
		['Canada', 500],
		['France', 600],
		['RU', 700]
	]);

	var options = {};

	var chart = new google.visualization.GeoChart(document.getElementById('finances-bar-chart'));
	chart.draw(data, options);
}

$(function() {
    var d1 = [[1262304000000, 6], [1264982400000, 3057], [1267401600000, 20434], [1270080000000, 31982], [1272672000000, 26602], [1275350400000, 27826], [1277942400000, 24302], [1280620800000, 24237], [1283299200000, 21004], [1285891200000, 12144], [1288569600000, 10577], [1291161600000, 10295]];
	var d2 = [[1262304000000, 5], [1264982400000, 200], [1267401600000, 1605], [1270080000000, 6129], [1272672000000, 11643], [1275350400000, 19055], [1277942400000, 30062], [1280620800000, 39197], [1283299200000, 37000], [1285891200000, 27000], [1288569600000, 21000], [1291161600000, 17000]];
 
	var data1 = [
	    { label: "Label 1", data: d1, points: { fillColor: successColor, size: 5 }, color: successColor },
	    { label: "Label 2", data: d2, points: { fillColor: dangerColor, size: 5 }, color: dangerColor }
	];

	$.plot($("#income-chart"), data1, {
	    xaxis: {
	        min: (new Date(2009, 12, 1)).getTime(),
	        max: (new Date(2010, 11, 1)).getTime(),
	        mode: "time",
	        tickSize: [1, "month"],
	        monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
	        tickLength: 0,
	        axisLabel: 'Month',
	        axisLabelUseCanvas: true,
	        axisLabelFontSizePixels: 12,
	        axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
	        axisLabelPadding: 5
	    },
	    yaxis: {
	        axisLabel: 'Amount',
	        axisLabelUseCanvas: true,
	        axisLabelFontSizePixels: 12,
	        axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
	        axisLabelPadding: 5
	    },
	    series: {
	        lines: {
	            show: true,
	            fill: true
	        },
	        points: {
	            show: false
	        },
	    },
	    grid: {
	        borderWidth: 1
	    },
	    legend: {
	    	show: false
	        // labelBoxBorderColor: "none",
	        // position: "right"
	    }
	});
});
