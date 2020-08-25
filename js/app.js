$(function () {
  var $window = $(window),
    $body = $('body'),
    $header = $('.js-header'),
    headerHeight = $header.innerHeight();

    $window.on('scroll', function () {
      var headerPosition = $header.offset().top;
  
      if ($window.scrollTop() > headerPosition) {
        $header.addClass('is-fixed');
        $body.css({ 'padding-top': headerHeight });
      }
    });

  function fixScroll() {
    $body.append('<div class="c-overlay js-overlay"></div>');
    $body.css({ 'overflow': 'hidden', 'height': '100%' });
  };

  //モーダル表示
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

  //画像切り替え
  var $jsMainImg = $('.js-main-img'),
    $jsSubImg = $('.js-sub-img');
  $jsSubImg.hover(function () {
    var src = $(this).attr("src");
    $jsMainImg.attr("src", src);
  });

  //お気に入り登録・解除
  var $like,
    likeId;
  $like = $('.js-like') || null,
    likeId = $like.data('productid') || null;
  if (likeId !== undefined && likeId !== null) {
    $like.on('click', function () {
      var $this = $(this);
      $.ajax({
        type: "POST",
        url: "ajaxLike.php",
        data: { productid: likeId }
      }).done(function (data) {
        console.log('Ajax Success');
        $this.toggleClass('is-active');
      }).fail(function (msg) {
        console.log('Ajax Error');
      });
    });
  }
  //タブ表示・切り替え
  $('.js-tab').on('click', function () {
    $(this).siblings().removeClass('is-active');
    $(this).addClass('is-active');
    var index = $(this).index();
    $('.js-tab-contents').siblings().removeClass('is-show');
    $('.js-tab-contents').eq(index).addClass('is-show');

  });

  var imglist = document.images;
  console.log(imglist);
  if (imglist.length > 0) {
    for (var i = 0; i < imglist.length; i++) {
      if (imglist[i].width > 0) {
        $(imglist[i]).parent().siblings('.c-form__heading').children('.js-delete').show();
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
  // テキストエリアカウント
  var $countUp = $('.js-count'),
    $countView = $('.js-count-view');
  $countUp.on('keyup', function (e) {
    $countView.html($(this).val().length);
  });

  $('.js-select-form').on('click', function (e) {
    $(this).children('.js-select-list').stop(true, false).slideToggle().addClass('is-active');
  });

  var categoryIndex = $('.js-selected-value[name="category"]').val();
  var sortIndex = $('.js-selected-value[name="sort"]').val();

  var $selectOption = $('.js-select-option');
  console.log(sortIndex);
  $('.js-category-option').eq(categoryIndex).hide();
  $('.js-sort-option').eq(sortIndex).hide();

  $('.js-select-option').on('click', function (e) {
    var data = $(this).data();
    data = data.category || data.sort;
    var currentindex = $(this).index();
    console.log(currentindex);
    $(this).addClass('is-active');
    $(this).parent().siblings('.js-selected-value').val(data);
    $(this).parent().siblings('.js-select-heading').text($(this).text());

    if ($(this).hasClass('js-category-option') && currentindex !== categoryIndex) {
      $('.js-category-option').eq(categoryIndex).show();
      $('.js-category-option').eq(currentindex).hide();
      categoryIndex = currentindex;
    } else {
      $('.js-sort-option').eq(sortIndex).show();
      $('.js-sort-option').eq(currentindex).hide();
      sortIndex = currentindex;
    }
    $(this).parent('.js-select-list').slideUp();
    e.stopPropagation();
  });

  //if ($('js-select-list').hasClass('is-active')) {
  $(document).click(function (event) {
    if (!$(event.target).closest('.js-select-form').length) {
      if (!$(event.target).closest('.c-btn--search').length) {
        $('.js-select-list').slideUp();
        $('.js-select-list').removeClass('is-active');
        if ($('.js-select-list').hasClass('is-active')) {
          $('.js-select-list').hasClass('is-active').children('.js-selected-value').val(0);
          $('.js-select-heading').text('カテゴリーを選択');
        }
      }
    }
  })

  $('.js-search-button').on('click', function () {
    $('.js-search-form').submit();
  });

  $('.js-search-value').on('change', function () {
    var searchValue = $(this).val();
    $('.js-word-value').val(searchValue);
  })

})