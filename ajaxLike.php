<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「  Ajax  ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// Ajax処理
//================================

// postがあり、ユーザーIDがあり、ログインしている場合
if (isset($_POST['productid']) && isset($_SESSION['user_id']) && isLogin()) {
  debug('POST送信があります。');
  $productId = (int)$_POST['productid'];
  debug('商品ID：' . $productId);

  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    $sql = 'SELECT * FROM `like` WHERE product_id = :product_id AND user_id = :user_id';
    $data = array(':user_id' => $_SESSION['user_id'], ':product_id' => $productId);
    // クエリ実行
    $stmt = execute($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug($resultCount);
    // レコードが１件でもある場合
    if (!empty($resultCount)) {
      // レコードを削除する
      $sql = 'DELETE FROM `like` WHERE product_id = :product_id AND user_id = :user_id';
      $data = array(':user_id' => $_SESSION['user_id'], ':product_id' => $productId);
      // クエリ実行
      $stmt = execute($dbh, $sql, $data);
    } else {
      // レコードを挿入する
      $sql = 'INSERT INTO `like` (product_id, user_id, create_date) VALUES (:product_id, :user_id, :date)';
      $data = array(':user_id' => $_SESSION['user_id'], ':product_id' => $productId, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = execute($dbh, $sql, $data);
    }
  } catch (PDOException $e) {
    error_log('エラー発生：' . $e->getMessage());
    $errMsg['common'] = MSG07;
  }
}
debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
exit;
