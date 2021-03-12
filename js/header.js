($(function () {
  //ヘッダー制御
  var $window = $(window),
    $body = $('body'),
    $header = $('.js-header'),
    headerHeight = $header.innerHeight();

  $window.on('scroll', function () {
    var headerPosition = $header.offset().top;

    if ($window.scrollTop() > headerPosition) {
      $header.addClass('is-fixed');
      $body.css({ 'padding-top': headerHeight });
    }
  });
}))