$(function () {
  // テキストエリアカウント
  var $countUp = $('.js-count'),
    $countView = $('.js-count-view');
  $countUp.on('keyup', function (e) {
    $countView.html($(this).val().length);
  });

  function nl2br(str) {
    return str.replace(/\r?\n/g, '<br>');
  }

  scrollBottom(0);

  var PostedMessageId;

  $('.js-message-form').on('submit', (e) => {

    e.preventDefault();

    var message = $('.js-message-input').val();
    var bordId = $('.js-id').val();
    var userName = $('.js-user-name').val(),
      toUser = $('.js-to-id').val(),
      fromUser = $('.js-from-id').val();

    $.ajax({
      type: "POST",
      url: "ajaxInsertMessage.php",
      data: {
        bord_id: bordId,
        message: message,
        name: userName,
        to_user_id: toUser,
        from_user_id: fromUser
      },
      dataType: "text"
    }).done((data) => {
      console.log('Ajax Success');
      var messageInfo = JSON.parse(data);
      getMessage();
      if ($('.js-message-notice')) {
        $('.js-message-notice').remove();
      }
      $('.js-message-input').val('');
      $countView.text("0");
      PostedMessageId = messageInfo.id;

    }).fail(() => {
      console.log('Ajax Error');
    });

  });

  var lastMessageId = $('.js-last-id').val();

  function scrollBottom(speed) {
    if ($('.js-message').length) {
      var target = $('.js-message').last();
      var position = target.offset().top + $('.js-message-area').scrollTop();
      $('.js-message-area').animate({
        scrollTop: position
      }, speed, 'swing');
    }
  }

  function getMessage() {
    var bordId = $('.js-id').val();

    var userId = $('.js-from-id').val();
    var userImg = $('.js-user-img').val();
    var partnerImg = $('.js-partner-img').val();
    function showMessage(data) {
      var html;
      var text = nl2br(data[data.length - 1].msg);
      if (data[data.length - 1].from_user_id === userId) {
        html = `<div class="p-msg__comment--right js-message">
                    <div class="p-msg__text">
                      ${text}
                    </div>
                    <div class="p-msg__comment__img">
                      <img src="${userImg}">
                    </div>
                  </div>`
        return html;
      } else {
        html = `<div class="p-msg__comment--left js-message">
                  <div class="p-msg__comment__img">
                    <img src="${partnerImg}">
                  </div>
                  <div class="p-msg__text">
                    <span class="p-msg__triangle"></span>
                    ${data[data.length - 1].msg}
                  </div>
                </div>`
        return html;
      }
    };



    $.ajax({
      type: "POST",
      url: "ajaxGetMessage.php",
      data: { bord_id: bordId },
      dataType: "json"
    }).done((data) => {
      console.log("Ajax Success");

      if (lastMessageId !== data[data.length - 1].m_id) {

        var html = showMessage(data);
        $('.js-message-area').append(html);
        scrollBottom(500);
        lastMessageId = data[data.length - 1].m_id;

      } else {
        console.log("更新なし");
      }

    }).fail(() => {
      console.log("Ajax Error");
    });
    setTimeout(getMessage, 10000);
  }

  getMessage();

})


