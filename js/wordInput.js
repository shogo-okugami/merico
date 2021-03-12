($(function(){

  $('.js-search-button').on('click',function(){
    //検索ワードを取得
    var searchValue = $('.js-search-value').val();
    $('.js-search-value').val(searchValue);
    $('.js-search-form').trigger('submit');
  })
}))