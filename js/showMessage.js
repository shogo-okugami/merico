($(function () {
  //メッセージ表示
  var $notice = $('.js-notice'),
    $noticeMsg = $notice.text();
  if ($noticeMsg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
    $notice.show();
    setTimeout(function () {
      $notice.fadeOut('slow');
    }, 8000);
  }

  var $warning = $('.js-warning'),
    $warningMsg = $warning.text();
  if ($warningMsg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
    $warning.show();
    setTimeout(function () {
      $warning.fadeOut('slow');
    }, 8000);
  }
}))