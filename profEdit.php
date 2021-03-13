<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);

debug('取得したユーザー情報：' . print_r($dbFormData, true));

//トークンを格納
setToken();

//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  //トークン判定
  checkToken();

  //変数にユーザー情報を代入
  $name = $_POST['name'];
  $comment = $_POST['comment'];
  $email = $_POST['email'];
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  //画像をpostしていない（登録していない）がすでにDBに登録されている場合、DBのパスを入れる
  $pic = (empty($pic) && empty($_POST['imgdelete']) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

  //DBの情報と入力が異なる場合にバリデーションを行う
  if ($dbFormData['name'] !== $name) {
    //名前の最大文字数チェック
    validRequired($name, 'name');
    validMaxLen($name, 'name', 30);
  }
  if ($dbFormData['email'] !== $email) {
    //emailの最大文字数チェック
    validMaxlen($email, 'email');
    //emailの形式チェック
    validEmail($email, 'email');
    //emaiの未入力チェック
    validRequired($email, 'email');
    if (empty($errMsg['email'])) {
      //emailの形式チェック
      validEmailDup($email, 'email');
    }
  }
  if ($dbFormData['comment'] !== $comment) {
    validMaxLen($comment, 'comment', 500);
  }

  if (empty($errMsg)) {
    debug('バリデーションがOKです。');

    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'UPDATE users SET name = :user_name, email = :email, comment = :comment, pic = :pic WHERE id = :user_id';
      $data = array(':user_name' => $name, ':email' => $email, ':comment' => $comment, ':pic' => $pic, ':user_id' => $dbFormData['id']);
      //クエリ実行
      $stmt = execute($dbh, $sql, $data);

      //クエリ成功の場合
      if ($stmt) {
        $_SESSION['msg_success'] = SUC02;
        unset($_SESSION['token']);
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
      } else {
        header("Location:profEdit.php");
      }
    } catch (PDOException $e) {
      error_log('エラー発生：' . $e->getMessage());
      $errMsg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール編集';
require('head.php');
?>

<body>

  <?php
  require('header.php');
  ?>

  <main>
    <div class="u-bgColor--gray">
      <section class="p-resist">
        <form method="POST" class="c-form" autocomplete="off" enctype="multipart/form-data">
          <input type="hidden" name="token" value="<?php echo $token; ?>">
          <div class="c-form__msg">
            <?php
            if (!empty($errMsg['common'])) echo $errMsg['common'];
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['name'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>名前</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="text" name="name" class="c-form__input" value="<?php echo getFormData('name'); ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            if (!empty($errMsg['name'])) echo $errMsg['name'];
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['email'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>email</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="text" name="email" class="c-form__input" value="<?php echo getFormData('email'); ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            if (!empty($errMsg['email'])) echo $errMsg['email'];
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['comment'])) echo 'is-error'; ?>">
              <div class="c-form__text">コメント</div>
              <textarea name="comment" class="c-form__input js-count" cols="30" rows="10"><?php echo getFormData('comment'); ?></textarea>
              <p class="c-form__counter">
                <span class="js-count-view">
                  <?php
                  if (!empty($_POST['comment'])) {
                    echo mb_strlen($_POST['comment']);
                  } else if ($_POST && empty($_POST['comment']) && $dbFormData['comment']) {
                    echo '0';
                  } else if ($dbFormData['comment']) {
                    echo mb_strlen($dbFormData['comment']);
                  } else {
                    echo '0';
                  }
                  ?>
                </span>/255文字</p>
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            if (!empty($errMsg['comment'])) echo $errMsg['comment'];
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__heading <?php if (!empty($errMsg['pic'])) echo 'is-error'; ?>">
              <p>プロフィール画像を選択</p>
              <span class="triangle--large"></span>
              <input type="file" name="pic" class="c-form__file js-file-input">
              <div class="c-form__delete--profile js-delete">
                <i class="far fa-lg fa-trash-alt delete js-img-delete"><input type="checkbox" name="imgdelete"></i>
              </div>
            </label>
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">

            <div class="c-form__prev js-prev">
              <img src="<?php echo getFormData('pic'); ?>" alt="" class="js-prev-img">
            </div>
          </div>
          <div class="c-form__msg">
            <?php
            if (!empty($errMsg['pic'])) echo $errMsg['pic'];
            ?>
          </div>
          <div class="c-form__item">
            <input type="submit" class="c-btn--submit" value="登録する">
          </div>
        </form>
      </section>
    </div>
  </main>
  <script type="text/javascript" src="js/imageInput.js"></script>
  <script type="text/javascript" src="js/textCount.js"></script>
  <?php
  require('footer.php');
  ?>