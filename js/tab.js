($(function () {
  //タブ表示・切り替え
  $('.js-tab').eq(0).addClass('is-active');
  $('.js-tab-contents').eq(0).addClass('is-show');

  $('.js-tab').on('click', function () {
    $(this).siblings().removeClass('is-active');
    $(this).addClass('is-active');
    var index = $(this).index();
    $('.js-tab-contents').siblings().removeClass('is-show');
    $('.js-tab-contents').eq(index).addClass('is-show');

  });
}))