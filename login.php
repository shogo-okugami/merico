<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require('auth.php');

//================================
// ログイン画面処理
//================================
// post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');

  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];

  $passSave = (!empty($_POST['pass_save'])) ? true : false;

  //emailの形式チェック
  validEmail($email, 'email');
  //emainの最大文字数チェック
  validMaxLen($email, 'email');

  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxlen($pass, 'pass');
  //パスワードの最小文字数チェック
  validMinlen($pass, 'pass');

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');


  if (empty($errMsg)) {
    debug('バリデーションOKです。');

    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      //クエリ実行
      $stmt = execute($dbh, $sql, $data);
      // クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      debug('クエリ結果の中身：' . print_r($result, true));

      //パスワード照合
      if (!empty($result) && password_verify($pass, array_shift($result))) {
        debug('パスワードがマッチしました。');
        debug('取得したURL：' . print_r($_SESSION['link'], true));
        //ログイン有効期限（デフォルトを1時間とする）
        $sesLimit = 60 * 60;
        //最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time();

        //ログイン保持にチェックがある場合
        if ($passSave) {
          debug('ログイン保持にチェックがあります。');
          //ログイン有効期限を30日にしてセット
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          debug('ログイン保持にチェックはありません。');
          //次回からログイン保持しないので、ログイン有効期限を1時間後にセット
          $_SESSION['login_limit'] = $sesLimit;
        }
        //ユーザーIDを格納
        $_SESSION['user_id'] = $result['id'];
        debug('セッション変数の中身：' . print_r($_SESSION, true));
        if ($_SESSION['link']) {
          $link = getSessionFlash('link');
          header("Location:$link");
        } else {
          header("Location:mypage.php");
        }
      } else {
        debug('パスワードがアンマッチです。');
        $errMsg['common'] = MSG09;
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
$siteTitle = 'ログイン';
require('head.php');
?>

<body>

  <?php
  require('header.php');
  ?>

  <main>
    <div class="u-bgColor--gray">
      <section class="c-container">

        <form action="" method="POST" class="c-form">
          <div class="c-form__msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['email'])) echo 'is-error'; ?>">
              <div class="c-form__text">Eメール</div>
              <input type="text" name="email" class="c-form__input" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['pass'])) echo 'is-error'; ?>">
              <div class="c-form__text">パスワード</div>
              <input type="password" name="pass" class="c-form__input" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('pass')
            ?>
          </div>
          <div class="c-form__item">
            <label>
              <input type="checkbox" name="pass_save">次回ログインを省略する
            </label>
          </div>
          <div class="c-form__item">
            <input type="submit" class="c-btn--submit" value="ログイン">
          </div>
        </form>
      </section>
    </div>
  </main>

  <?php
  require('footer.php');
  ?>