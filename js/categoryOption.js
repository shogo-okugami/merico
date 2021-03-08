$(function () {

  $('.js-list').on('click',function(){

    //thisを変数に格納
    var that = $(this);
    //ボーダーを消す
    that.css('border-bottom','none');
    //クリックされたカテゴリーリストのメニューを表示する
    var category = that.data('category');
    var index = category  - 1;
    $('.js-menu').eq(index).slideDown(500);
    //カテゴリーリストの範囲外をクリックすればメニューを非表示にする
    $(document).on('click',function (event) {
      if (!$(event.target).closest('.js-list').length) {
          $('.js-menu').slideUp();
          that.css('border-bottom','1px solid #a0a0a0');
          }
      })
  })

})