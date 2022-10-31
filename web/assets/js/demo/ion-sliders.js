$(function () {
	$("#example_1").ionRangeSlider({
	    min: 0,
	    max: 5000,
	    type: 'double',
	    prefix: "$",
	    maxPostfix: "+",
	    prettify: false,
	    hasGrid: true
	});

	$("#example_2").ionRangeSlider({
	    min: 1000,
	    max: 100000,
	    from: 30000,
	    to: 90000,
	    type: 'double',
	    step: 500,
	    postfix: " €",
	    hasGrid: true
	});

	$("#example_3").ionRangeSlider({
	    min: 0,
	    max: 10,
	    type: 'single',
	    step: 0.1,
	    postfix: " carats",
	    prettify: false,
	    hasGrid: true
	});

	$("#example_4").ionRangeSlider({
	    min: -50,
	    max: 50,
	    from: 0,
	    postfix: "°",
	    prettify: false,
	    hasGrid: true
	});

	$("#example_5").ionRangeSlider({
	    values: [
	        "January", "February", "March",
	        "April", "May", "June",
	        "July", "August", "September",
	        "October", "November", "December"
	    ],
	    type: 'single',
	    hasGrid: true
	});

	$("#example_6").ionRangeSlider({
	    min: 10000,
	    max: 100000,
	    step: 1000,
	    postfix: " miles",
	    from: 55000,
	    hideMinMax: false,
	    hideFromTo: true
	});

	$("#example_7").ionRangeSlider({
	    min: 10000,
	    max: 100000,
	    step: 100,
	    postfix: " km",
	    from: 55000,
	    hideMinMax: true,
	    hideFromTo: false
	});

	$("#example_8").ionRangeSlider({
	    min: 1000000,
	    max: 100000000,
	    type: "double",
	    postfix: " pounds",
	    step: 10000,
	    from: 25000000,
	    to: 35000000,
	    onChange: function(obj) {
	        console.log(obj);
	    },
	    onLoad: function(obj) {
	        console.log(obj);
	    }
	});
});