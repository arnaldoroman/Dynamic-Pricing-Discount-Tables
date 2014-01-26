jQuery( document ).ready(function($) {
  $('.show_savings_bulk').hover(
  function () {
    $(this).next('.bulk-savings-table').addClass('visible');
  },
  function () {
    $(this).next('.bulk-savings-table').removeClass('visible');
  }
);
});