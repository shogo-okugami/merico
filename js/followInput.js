$(function () {

  //フォロー登録・解除

  $('.js-follow').on('click', function () {
    //thisを変数に格納
    var that = $(this);
    //サブカテゴリーのIDを取得
    var subCategoryId = that.data('subcategory');
    //ajax通信
    $.ajax({
      type: "POST",
      url: "ajaxFollow.php",
      data: { subcategory: subCategoryId }
    }).done(function () {
      console.log('Ajax Success');
      //フォロー判定クラスをトグルする
      that.toggleClass('is-followed');
      //フォローの状態に応じてテキストを変更
      that.hasClass('is-followed') ? that.text('フォロー中') : that.text('フォローする');
    }).fail(function () {
      console.log('Ajax Error');
    });
  });
})