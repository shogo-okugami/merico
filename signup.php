<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

//post送信されていた場合
if (!empty($_POST)) {

  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $name = $_POST['name'];
  $pass = $_POST['pass'];
  $rePass = $_POST['pass_re'];

  //未入力チェック
  validRequired($email, 'email');
  validRequired($name, 'name');
  validRequired($pass, 'pass');
  validRequired($rePass, 'pass_re');

  if (empty($errMsg)) {

    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxlen($email, 'email');
    //email重複チェック
    validEmailDup($email);
    //名前の最大文字数チェック
    validMaxLen($name, 'name', 30);
    //パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    //パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    //パスワードの最小文字数チェック
    validMinLen($pass, 'pass');

    //パスワード（再入力）の最大文字数チェック
    validMaxLen($rePass, 'pass_re');
    //パスワード（再入力）の最小文字数チェック
    validMinLen($rePass, 'pass_re');
    //パスワードとパスワード再入力が合っているかチェック
    validMatch($pass, $rePass, 'pass_re');

    if (empty($errMsg)) {

      try {
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'INSERT INTO users (email,name,password,login_time,create_date) VALUES(:email,:name,:pass,:login_time,:create_date)';
        $data = array(
          ':email' => $email, ':name' => $name, ':pass' => password_hash($pass, PASSWORD_DEFAULT),
          ':login_time' => date('Y-m-d H:i:s'),
          ':create_date' => date('Y-m-d H:i:s')
        );
        //クエリ実行
        $stmt = execute($dbh, $sql, $data);

        //クエリ成功の場合
        if ($stmt) {
          //ログイン有効期限（デフォルトを１時間とする）
          $sesLimit = 60 * 60;
          //最終ログイン日時を現在日時に
          $_SESSION['login_date'] = time();
          $_SESSION['login_limit'] = $sesLimit;
          //ユーザーIDを格納
          $_SESSION['user_id'] = $dbh->lastInsertId();

          debug('セッション変数の中身：' . print_r($_SESSION, true));

          header("Location:mypage.php"); //マイページへ
        }
      } catch (PDOException $e) {
        error_log('エラー発生：' . $e->getMessage());
        $errMsg['common'] = MSG07;
      }
    }
  }
}
?>
<?php
$siteTitle = 'ユーザー登録';
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
              <div class="c-form__text">
                <div>Eメール</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="text" name="email" class="c-form__input" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['name'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>ユーザー名</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="text" name="name" class="c-form__input" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('name');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['pass'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>パスワード</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="password" name="pass" class="c-form__input" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('pass');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['pass_re'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>パスワード(確認)</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="password" name="pass_re" class="c-form__input" value="<?php if (!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('pass_re');
            ?>
          </div>
          <div class="c-form__item">
            <input type="submit" class="c-btn--submit" value="ユーザー登録">
          </div>
        </form>
      </section>
    </div>
  </main>
  <?php
  require('footer.php');
  ?>