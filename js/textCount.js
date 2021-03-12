($(function () {
  // テキストエリアカウント
  var $countUp = $('.js-count'),
    $countView = $('.js-count-view');
  $countUp.on('keyup', function (e) {
    $countView.html($(this).val().length);
  });
}))