<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：' . print_r($userData, true));

//トークンを格納
setToken();

//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //トークン判定
  checkToken();

  //変数にユーザー情報を代入
  $passOld = $_POST['pass_old'];
  $passNew = $_POST['pass_new'];
  $rePassNew = $_POST['pass_new_re'];

  //未入力チェック
  validRequired($passOld, 'pass_old');
  validRequired($passNew, 'pass_new');
  validRequired($rePassNew, 'pass_new_re');

  if (empty($errMsg)) {
    debug('未入力チェックOK。');

    //古いパスワードのチェック
    validPass($passOld, 'pass_old');
    //新しいパスワードのチェック
    validPass($passNew, 'pass_new');

    //古いパスワードとDBパスワードを照合
    if (!password_verify($passOld, $userData['password'])) {
      $errMsg['pass_old'] = MSG12;
    }

    //新しいパスワードと古いパスワードが同じかチェック
    if ($passOld === $passNew) {
      $errMsg['pass_new'] = MSG13;
    }
    //パスワードとパスワード再入力が合っているかチェック
    validMatch($passNew, $rePassNew, 'pass_new_re');

    if (empty($errMsg)) {
      debug('バリデーションOK。');

      try {
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($passNew, PASSWORD_DEFAULT));
        //クエリ実行
        $stmt = execute($dbh, $sql, $data);

        //クエリ成功の場合
        if ($stmt) {
          $_SESSION['msg_success'] = SUC01;
          debug('マイページへ遷移します。');
          header("Location:mypage.php");
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
$siteTitle = 'パスワード変更';
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>
  <main>
    <div class="u-bgColor--gray">
      <section class="c-container">
        <form action="" method="POST" class="c-form" autocomplete="off">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
          <div class="c-form__msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['email'])) echo 'is-error'; ?>">
              <div class="c-form__text">古いパスワード</div>
              <input type="password" name="pass_old" class="c-form__input" value="<?php if (!empty($_POST['pass_old'])) echo $_POST['pass_old']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('pass_old');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['pass_new'])) echo 'is-error'; ?>">
              <div class="c-form__text">新しいパスワード</div>
              <input type="password" name="pass_new" class="c-form__input" value="<?php if (!empty($_POST['pass_new'])) echo $_POST['pass_new']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('pass_new');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['pass_new_re'])) echo 'is-error'; ?>">
              <div class="c-form__text">新しいパスワード（再入力）</div>
              <input type="password" name="pass_new_re" class="c-form__input" value="<?php if (!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re']; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('pass_new_re');
            ?>
          </div>
          <div class="c-form__item">
            <input type="submit" class="c-btn--submit" value="変更する">
          </div>
        </form>
      </section>
    </div>
  </main>
  <?php
  require('footer.php');
  ?>