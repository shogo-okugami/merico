$(function () {

  var $categoryList = $('.js-category-list');
  $('.js-select-heading').on('click', function () {
    //サブカテゴリーリストが表示されていなければメニューを表示
    if (!$('.js-subcategory-list').hasClass('is-show')) {
      $categoryList.slideDown();
    }
    //カテゴリーリストの範囲外をクリックすればメニューを非表示にする
    $(document).on('click', function (event) {
      if (!$(event.target).closest('.js-select-form').length) {
        $categoryList.slideUp();
      }
    })
  })

  //input[name=category]の値を取得
  var categoryInput = $('.js-category-value').val();
  //$('.js-category-option').eq(categoryInput).css('display', 'none');
  $('.js-category-option').on('click', function () {

    $categoryList.css('display', 'none');

    //クリックされたカテゴリーのIDを取得
    var categoryId = $(this).data('category');

    //カテゴリーIDに属するサブカテゴリーのリストを表示
    var index = categoryId - 1;

    $('.js-subcategory-list').eq(index).css('display', 'block').addClass('is-show');
  })

  //input[name=sub_category]の値を取得
  var subCategoryInput = $('.js-subcategory-value').val();

  $('.js-subcategory-option').on('click', function () {
    //クリックされたサブカテゴリーのIDを取得
    var subCategoryId = $(this).data('subcategory');
    //非表示のサブカテゴリーを表示
    //var prevInput = $('.js-subcategory-value').val();
    //$(`.js-subcategory-option[data-subcategory="${prevInput}"]`).css('display', 'block');
    //サブカテゴリーのIDを対応するinputにセット
    $('.js-subcategory-value').val(subCategoryId);

    //クリックされたカテゴリーのIDを取得
    var categoryId = $(this).data('category');
    $('.js-category-value').val(categoryId);

    //クリックされたカテゴリーを非表示にする
    //$(this).css('display', 'none');

    //サブカテゴリーのリストを非表示にする
    $(this).parent('.js-subcategory-list').removeClass('is-show').slideUp();

    //サブカテゴリーの名前をリストの先頭にセット
    var name = $(this).text();
    $('.js-select-heading').text(name);
  })
})