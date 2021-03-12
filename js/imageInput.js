$(function () {

  var imgList = document.images;

  if (imgList.length > 0) {
    for (var i = 0; i < imgList.length; i++) {
      if (imgList[i].width > 0) {
        $(imgList[i]).parent().siblings('.c-form__heading').children('.js-delete').show();
      }
    }
  }

  $('.js-file-input').on('change', function (e) {
    var file = this.files[0],            // 2. files配列にファイルが入っています
      $img = $(this).parents('.c-form__item').find('.js-prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
      fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト
    var $input = $(this);
    // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
    fileReader.onload = function (event) {
      // 読み込んだデータをimgに設定
      $img.attr('src', event.target.result).show();
      console.log("画像が更新されました。");
      $input.siblings('.js-delete').show();
    };
    // 6. 画像読み込み
    fileReader.readAsDataURL(file);
  });

  $('.js-delete').on('click', function () {
    $(this).hide();
    $(this).siblings('.js-file-input').val('');
    var $prevImg = $(this).parent().siblings('.js-prev').children('.js-prev-img');
    $prevImg.attr('src', '').hide();

  });
})