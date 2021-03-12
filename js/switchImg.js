($(function () {
  //画像切り替え
  var $jsMainImg = $('.js-main-img'),
    $jsSubImg = $('.js-sub-img');
  $jsSubImg.hover(function () {
    var src = $(this).attr("src");
    $jsMainImg.attr("src", src);
  });
}))