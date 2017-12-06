$(function () {
  var tour = new Tour({
    steps: [
      {
        element: "#page-title",
        title: "Title of your page",
        content: "The Page title/heading",
        placement: "bottom"
      },
      {
        element: "#page-breadcrumb",
        title: "Page breadcrumb example",
        content: "Page breadcrumb example",
        placement: "top"
      },
      {
        element: "#page-content",
        title: "Grid options",
        content: "See how aspects of the Bootstrap grid system work across multiple devices with a handy table.",
        placement: "top"
      },
      {
        element: "#page-panel",
        title: "Mobile & desktop",
        content: "Don't want your columns to simply stack in smaller devices? Use the extra small and medium device grid classes by adding .col-xs-* .col-md-* to your columns. See the example below for a better idea of how it all works.",
        placement: "top"
      },
      {
        element: "#page-nested",
        title: "Column ordering",
        content: "Example of ordering columns with bootstrap",
        placement: "top"
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