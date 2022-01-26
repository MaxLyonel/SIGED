$(function () {
  var tour = new Tour({
    steps: [
      {
        element: "#fleet-car-title",
        title: "Title of your vehicle",
        content: "The name/number of vehicle, editable instantaniously!"
      },
      {
        element: "#fleet-car-details",
        title: "Details of your vehicle",
        content: "Information about your vehicle"
      },
      {
        element: "#fleet-car-assignment",
        title: "Assignment of vehicle",
        content: "Where this vehicle is assigned (eg: location of auto parc)"
      },
      {
        element: "#fleet-car-stats",
        title: "Vehicle statistics",
        content: "Information about fuel costs, servie costs, etc. of vehicle",
        placement: "top"
      },
      {
        element: "#fleet-car-comments-tasks",
        title: "Comments & tasks",
        content: "Comments and task of this vehicle",
        placement: "left"
      }
    ],
    container: 'body',
    backdrop: true,
    keyboard: true,
    storage: false
  });

  // Initialize the tour
  tour.init();

  // Start the tour
  tour.start();
});