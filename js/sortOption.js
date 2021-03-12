$(function () {

  $('.js-sort-list').on('click',function(){

    //メニューを表示
    var sortMenu = $('.js-sort-menu');
    sortMenu.css('display','block');
    
    //ソートーリストの範囲外をクリックすればメニューを非表示にする
    $(document).on('click',function (event) {
      if (!$(event.target).closest('.js-sort-list').length) {
          sortMenu.css('display','none');
          }
      })
  })

})