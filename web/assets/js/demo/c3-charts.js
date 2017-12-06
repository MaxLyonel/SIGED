$(function () {
	// C3 Line Chart
	var lineChart = c3.generate({
	    bindto: '#c3-line-chart',
	    data: {
	      columns: [
	        ['data1', 30, 200, 100, 400, 150, 250],
	        ['data2', 50, 20, 10, 40, 15, 25]
	      ]
	    }
	});

	// C3 Area Chart
	var areaChart = c3.generate({
		bindto: '#c3-area-chart',
	    data: {
	        columns: [
	            ['data1', 300, 350, 300, 0, 0, 0],
	            ['data2', 130, 100, 140, 200, 150, 50]
	        ],
	        types: {
	            data1: 'area',
	            data2: 'area-spline'
	        }
	    }
	});

	// C3 Bar Chart
	var barChart = c3.generate({
		bindto: '#c3-bar-chart',
	    data: {
	        columns: [
	            ['data1', 30, 200, 100, 400, 150, 250],
	            ['data2', 130, 100, 140, 200, 150, 50]
	        ],
	        type: 'bar'
	    },
	    bar: {
	        width: {
	            ratio: 0.5 // this makes bar width 50% of length between ticks
	        }
	        // or
	        //width: 100 // this makes bar width 100px
	    }
	});

	// C3 Donut Chart
	var donutChart = c3.generate({
		bindto: '#c3-donut-chart',
	    data: {
	        columns: [
	            ['data1', 30],
	            ['data2', 120],
	        ],
	        type : 'donut',
	        onclick: function (d, i) { console.log("onclick", d, i); },
	        onmouseover: function (d, i) { console.log("onmouseover", d, i); },
	        onmouseout: function (d, i) { console.log("onmouseout", d, i); }
	    },
	    donut: {
	        title: "Iris Petal Width"
	    }
	});
})