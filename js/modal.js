($(function () {
  //モーダル表示
  var $body = $('body');
  $('.js-modal-trigger').on('click', function () {
    $('.js-modal').toggleClass('is-show');
    if ($('.js-modal').hasClass('is-show')) {
      fixScroll($body);
    } else {
      $('.js-overlay').remove();
      $body.css({ 'overflow': 'scroll', 'height': '100%' });
    }
    return false;
  });
  //スクロール固定関数
  function fixScroll() {
    $body.append('<div class="c-overlay js-overlay"></div>');
    $body.css({ 'overflow': 'hidden', 'height': '100%' });
  };
}))