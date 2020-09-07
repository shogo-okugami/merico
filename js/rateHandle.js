$(function () {

  
  var $star = $('.js-star'),
    currentIndex = 0;

  $star.on('click', function () {

    //inputに評価の値を設定
    var index = $(this).index() + 1;
    $('input[name="rate"]').val(index);
    
    //クリックした星（評価の値）が前にクリックしたときよりも低い場合
    if (index < currentIndex) {
      //クリックした星よりあとの星の色をグレーにする
      $star.eq(index - 1).nextAll().removeClass('is-active');
    }
    //クリックした星とその前の星の色をイエローにする
    $(this).addClass('is-active');
    $(this).prevAll().addClass('is-active');
    //クリックした評価の値を現在の値にする
    currentIndex = index;
  })
})