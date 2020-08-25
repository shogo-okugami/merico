<?php
require('function.php');
header("Content-Type: application/json; charset=UTF-8");

$bord_id = $_POST['bord_id'];
// DBへ接続
$dbh = dbConnect();
if (!empty($_POST['message'])) {

  //バリデーションチェック
  $msg = (isset($_POST['message'])) ? $_POST['message'] : '';
  //最大文字数チェック
  validMaxLen($msg, 'msg');
  //未入力チェック
  validRequired($msg, 'msg');

  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    // SQL文作成
    $sql = 'INSERT INTO message (bord_id, send_date,send_username, to_user_id, from_user_id, msg, create_date) VALUES (:b_id, :send_date,:send_username, :to_user_id, :from_user_id, :msg, :date)';
    $data = array(':b_id' => $bord_id, ':send_date' => date('Y-m-d H:i:s'), ':send_username' => $_POST['name'], ':to_user_id' => $_POST['to_user_id'], ':from_user_id' => $_POST['from_user_id'], ':msg' => $msg, ':date' => date('Y-m-d H:i:s'));
    // クエリ実行
    $stmt = execute($dbh, $sql, $data);
    $message_id = $dbh->lastInsertId();
    $viewData = getMsg($message_id);
    if ($viewData) {
      echo json_encode($viewData);
      exit;
    }
  }
}
