($(function () {
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
}))