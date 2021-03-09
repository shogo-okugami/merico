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
if (isset($_POST['subcategory']) && isset($_SESSION['user_id']) && isLogin()) {
  debug('POST送信があります。');
  $subCategoryId = (int)$_POST['subcategory'];
  debug('カテゴリーID：' . $subCategoryId);

  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    $sql = 'SELECT * FROM category_follows WHERE sub_category_id = :sub_category_id AND user_id = :user_id';
    $data = array(':user_id' => $_SESSION['user_id'], ':sub_category_id' => $subCategoryId);
    // クエリ実行
    $stmt = execute($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug($resultCount);
    // レコードが１件でもある場合
    if (!empty($resultCount)) {
      // レコードを削除する
      $sql = 'DELETE FROM category_follows WHERE sub_category_id = :sub_category_id AND user_id = :user_id';
      $data = array(':user_id' => $_SESSION['user_id'], ':sub_category_id' => $subCategoryId);
      // クエリ実行
      $stmt = execute($dbh, $sql, $data);
    } else {
      // レコードを挿入する
      $sql = 'INSERT INTO category_follows (sub_category_id, user_id, create_date) VALUES (:sub_category_id, :user_id, :date)';
      $data = array(':user_id' => $_SESSION['user_id'], ':sub_category_id' => $subCategoryId, ':date' => date('Y-m-d H:i:s'));
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