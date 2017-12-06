$(function () {
  var tour = new Tour({
    steps: [
      {
        element: "#dashboard-tour-stats",
        title: "Stats Widgets #1",
        content: "Mini stats widgets to display important stats first hand to the user",
        placement: "bottom"
      },
      {
        element: "#dashboard-tour-visits",
        title: "Stats Widgets #2",
        content: "Website Visits chart",
        placement: "top"
      },
      {
        element: "#dashboard-tour-earnings",
        title: "Stats Widgets #3",
        content: "Earnings stats",
        placement: "left"
      },
      {
        element: "#dashboard-tour-demographic",
        title: "Stats Widgets #4",
        content: "Website visits shown on a map",
        placement: "top"
      },
      {
        element: "#dashboard-tour-weather",
        title: "Weather Widget",
        content: "Weather information widget",
        placement: "right"
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