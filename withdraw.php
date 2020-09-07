<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
try {
  //DBへ接続
  $dbh = dbConnect();
  if (!getMyProductAndBord($_SESSION['user_id'])) {
    //SQL文作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :user_id';
    $sql2 = 'UPDATE products SET delete_flg = 1 WHERE user_id = :user_id';
    $sql3 = 'UPDATE `like` SET delete_flg = 1 WHERE user_id = :user_id';
    //データ流し込み
    $data = array(':user_id' => $_SESSION['user_id']);
    //クエリ実行
    try {
      $dbh->beginTransaction();
      $stmt1 = execute($dbh, $sql1, $data);
      $stmt2 = execute($dbh, $sql2, $data);
      $stmt3 = execute($dbh, $sql3, $data);

      $dbh->commit();
      //セッション削除
      session_destroy();
      debug('セッション変数の中身：' . print_r($_SESSION, true));
      debug('トップページへ遷移します。');
      header("Location:index.php");
    } catch (Exception $e) {
      $dbh->rollBack();
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  } else {
    $_SESSION['msg_warning'] = MSG18;
    header("Location:mypage.php");
  }
} catch (PDOException $e) {
  error_log('エラー発生：' . $e->getMessage());
  $err_msg['common'] = MSG07;
}
