<?php
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors', 'on');
//ログの出力ファイを設定
ini_set('error_log', 'php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = false;
/**
 * デバッグログ出力関数
 * 
 * @param string $str
 */
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ:' . $str);
  }
}
//================================
// セッション準備・セッション有効期限を延ばす
//================================
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart()
{
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：' . session_id());
  debug('セッション変数の中身：' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（確認）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', "255文字以内で入力してください");
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '20文字以内で入力してください');
define('MSG11', '画像を選択してください');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '選択してください');
define('MSG16', '不正なアクセスです。');
define('MSG17', '半角数字のみご利用いただけます');
define('MSG18', '取引中の商品があるため退会できません。取引を完了してください。');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを更新しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '商品を登録しました');
define('SUC06', '購入しました。相手と連絡を取りましょう。');


mb_internal_encoding('UTF-8');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//トークン格納用の変数
$token = '';

//================================
// バリデーション関数
//================================

/**
 * バリデーション関数（未入力チェック）
 * 
 * @param string $str
 * @param string $key
 */
function validRequired($str, $key)
{
  global $err_msg;
  if ($str === '') {
    if ($key === 'pic1') {
      $err_msg[$key] = MSG11;
    } else {
      $err_msg[$key] = MSG01;
    }
  }
}

/**
 * バリデーション関数（Email形式チェック）
 * 
 * @param string $str
 * @param string $key
 */
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
/**
 * バリデーション（Email重複チェック）
 * 
 * @param string $email
 */
function validEmailDup($email)
{
  global $err_msg;

  // DBへ接続
  $dbh = dbConnect();
  // SQL文作成
  $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
  $data = array(':email' => $email);
  // クエリ実行
  $stmt = execute($dbh, $sql, $data);
  // クエリ結果の値を取得
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!empty(array_shift($result))) {
    $err_msg['email'] = MSG08;
  }
}
/**
 * バリデーション（同値判定）
 * 
 * @param string $str1
 * @param string $str2
 * @param string $key
 */
function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
/**
 * バリデーション（最小文字数）
 * 
 * @param string $str
 * @param string $key
 * @param int $min
 */
function validMinlen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
/**
 * バリデーション（最大文字数）
 * 
 * @param string $str
 * @param string $key
 * @param int $max
 */
function validMaxlen($str, $key, $max = 255)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    if ($max === 20) {
      $err_msg[$key] = MSG10;
    } else {
      $err_msg[$key] = MSG06;
    }
  }
}
/**
 * バリデーション（半角入力）
 * 
 * @param string $str
 * @param string $key
 */
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
/**
 * パスワードバリデーション
 * 
 * @param string $str
 * @param string $str
 */
function validPass($str, $key)
{
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//半角数字チェック
function validNumber($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}
/**
 * 商品登録時のカテゴリー選択を判定します
 * 
 * @param mixed $str
 * @param string $key
 */
function validSelect($str, $key)
{
  if (!preg_match("/^[1-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
/**
 * エラーメッセージを表示します
 * 
 * @param $key mixed
 * 
 * @return array
 */
function getErrMsg($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}
/**
 * セッションの$keyの値を取得して返します
 * 
 * @param string $key
 * 
 * @return mixed
 */
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
//================================
// ログイン認証
//================================
/**
 * ユーザーがログインしているか判定します
 * 
 * @return bool
 */
function isLogin()
{
  //ログインしている場合
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです。');
    //現在日時が最終ログイン日時+有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      debug('ログイン有効期限オーバーです。');
      //セッションを削除する
      session_destroy();
      return false;
    } else {
      debug('ログイン有効期限内です。');
      return true;
    }
  } else {
    debug('未ログインユーザーです。');
    return false;
  }
}
//================================
// データベース
//================================

/**
 * DB接続関数
 * 
 * DB操作時にDBに接続します
 * 接続に失敗した場合は例外処理を行います
 * 
 *@return object
 */

function dbConnect()
{
  //DBへの接続準備
  $dsn = 'mysql:dbname=merico;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    //SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  //PDOオブジェクト生成（DBへ接続）
  try {
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
  } catch (PDOException $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

/**
 * SQL実行関数
 * 
 * @param  $dbh PDOオブジェクト
 * @param string $sql 実行するSQL
 * @param array $parrams_array バインドするパラメータ
 * 
 * @return object
 */
function execute(PDO $dbh, string $sql,  array ...$params_array)
{
  $stmt = $dbh->prepare($sql);

  // ----- パラメータを一つの配列に統合 ----- //
  $integrated_params = array();
  $index = 1;

  foreach ($params_array as $params) {

    // $paramsがスカラまたはnullの時は配列に変換
    if (is_scalar($params) || $params === null) {
      $params = array($params);
    }

    foreach ($params as $param_id => $value) {
      // 数値添字のときは疑問符パラメータとみなす
      if (gettype($param_id) == 'integer') {
        $integrated_params[$index] = $value; // 疑問符パラメータ
      } else {
        $integrated_params[$param_id] = $value; // 名前付きパラメータ
      }
      $index++;
    }
  }

  // ----- データ型に応じてバインド ----- //
  foreach ($integrated_params as $param_id => $value) {
    switch (gettype($value)) {
      case 'boolean':
        $param_type = PDO::PARAM_BOOL;
        break;

      case 'integer':
        $param_type = PDO::PARAM_INT;
        break;

      case 'double':
        $param_type = PDO::PARAM_STR;
        break;

      case 'string':
        $param_type = PDO::PARAM_STR;
        break;

      case 'NULL':
        $param_type = PDO::PARAM_NULL;
        break;

      default:
        $param_type = PDO::PARAM_STR;
    }

    $stmt->bindValue($param_id, $value, $param_type);
  }

  $stmt->execute();

  return $stmt;
}
/**
 * ユーザIDに合致するユーザー情報を取得します
 * 
 * @param int $user_id
 * 
 * @return array
 */
function getUser(int $user_id)
{
  debug('ユーザー情報を取得します。');

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM users WHERE id = :user_id AND delete_flg = 0';
  $data = array(':user_id' => $user_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  //クエリ結果のデータを１レコード返却
  if ($stmt) {
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    return false;
  }
}
/**
 * ユーザーID,商品IDに合致する商品情報を取得します
 * 
 * @param int $user_id
 * @param int $product_id
 * 
 * @return array
 */
function getProduct(int $user_id, int $product_id)
{
  debug('商品情報を取得します。');
  debug('ユーザーID：' . $user_id);
  debug('商品ID：' . $product_id);

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM products WHERE user_id = :user_id AND id = :product_id AND delete_flg = 0';
  $data = array(':user_id' => $user_id, ':product_id' => $product_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    //クエリ結果のデータを１レコード返却
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    return false;
  }
}
/**
 * ホーム画面で表示する商品の一覧を取得します
 * 
 * @param int $currentMinNum レコードの取得開始位置
 * @param int $category 
 * @param int $sort 
 * @param string $word
 * 
 * @return array
 */
function getProductList($currentMinNum = 1, int $category, int $sort, string $word, int $span = 20)
{
  debug('商品情報を取得します。');

  //DBへ接続
  $dbh = dbConnect();
  //件数用のSQL文作成
  $sql = 'SELECT * FROM products WHERE search_flg = 0 AND delete_flg = 0';
  $where = array();
  $data = array();
  if (!empty($category)) {
    array_push($where, "category_id = :category_id");
    $data += array(':category_id' => $category);
  }
  if (!empty($sort)) {
    switch ($sort) {
      case 1:
        $order = " ORDER BY create_date desc";
        break;
      case 2:
        $order = " ORDER BY price ASC";
        break;
    }
  }
  if (!empty($word)) {
    $word = '%' . preg_replace('/(?=[!_%])/', '!', $word) . '%';
    array_push($where, "name LIKE :word");
    $data += array(':word' => $word);
  }
  if (!empty($where)) {
    $whereSql = implode(' AND ', $where);
    $sql .= ' AND ' . $whereSql;
  }
  if ($order) {
    $sql .= $order;
  }
  debug('バインドパラメータ：' . print_r($data, true));
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);
  $rst['total'] = $stmt->rowCount(); //総レコード数
  $rst['total_page'] = ceil($rst['total'] / $span); //総ページ数
  if (!$stmt) {
    return false;
  }

  //ページング用のSQL文作成
  $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;

  debug('SQL：' . $sql);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    //クエリ結果のデータを全レコード格納
    $rst['data'] = $stmt->fetchAll();
    return $rst;
  } else {
    return false;
  }
}
/**
 * 商品IDに合致する商品情報を取得
 * 
 * @param int $product_id
 * 
 * @return array
 */
function showProduct(int $product_id)
{
  debug('商品情報を取得します。');
  debug('商品ID：' . $product_id);

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT p.id, p.name, p.comment, p.pic1, p.pic2, p.pic3, p.price, p.category_id, p.user_id, p.search_flg, p.create_date, c.name AS category 
            FROM products AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :product_id AND p.delete_flg = 0';
  $data = array(':product_id' => $product_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    //クエリ結果のデータを１レコード取得
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    return false;
  }
}
/**
 * ユーザーIDに合致する商品情報を取得
 * 
 * @param int $user_id
 * 
 * @return array
 */
function getMyProduct(int $user_id)
{
  debug('商品情報を取得します。');
  debug('ユーザーID：' . $user_id);

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM products WHERE user_id = :user_id AND delete_flg = 0';
  $data = array(':user_id' => $user_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    return $stmt->fetchAll();
  } else {
    return false;
  }
}
/**
 * 掲示板情報とそのメッセージ情報を取得
 * 
 * 掲示板IDに合致する掲示板情報とその掲示板のメッセージを取得
 * $all_flgがfalseの場合はメッセージは最新の一件を取得
 * 
 * @param int $id
 * @param bool $all_flg
 * 
 * @return array
 */
function getMsgsAndBord(int $id, bool $all_flg = true)
{
  debug('掲示板情報を取得します。');
  debug('掲示板ID：' . $id);

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  if ($all_flg) {
    $sql = 'SELECT m.id AS m_id, product_id, m.bord_id, send_username, send_date, to_user_id, from_user_id, seller_id, buyer_id, msg, b.create_date, buy_flg, sell_flg,complete_flg FROM bord AS b LEFT JOIN message AS m ON b.id = m.bord_id WHERE b.id = :id AND b.delete_flg = 0 ORDER BY send_date ASC';
  } else {
    $sql = 'SELECT m.id AS m_id, product_id, m.bord_id, send_username, send_date, to_user_id, from_user_id, seller_id, buyer_id, msg, b.create_date, buy_flg, sell_flg,complete_flg FROM bord AS b LEFT JOIN message AS m ON b.id = m.bord_id WHERE b.id = :id AND b.delete_flg = 0 ORDER BY send_date DESC LIMIT 1';
  }
  $data = array(':id' => $id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    //クエリ結果の全データを返却
    return $stmt->fetchAll();
  } else {
    return false;
  }
}
/**
 * ユーザーが登録している商品情報を取得
 * 
 * ユーザーIDに合致するユーザーの商品情報を取得
 * $flgがtrueの場合は取引が完了している商品は取得しません
 * 
 * @param int $user_id
 * @param bool $flg 
 * 
 * @return array
 */
function getMyProductAndBord(int $user_id, bool $flg = false)
{
  debug('自分の掲示板情報を取得します。');

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  if ($flg) {
    $flg = 1;
  } else {
    $flg = 0;
  }
  $sql = "SELECT p.pic1, p.name, b.complete_flg, b.id AS bord FROM products AS p INNER JOIN bord AS b ON p.id = b.product_id WHERE p.delete_flg = 0 AND b.complete_flg = {$flg} AND (b.seller_id = :user_id OR b.buyer_id = :user_id)  ORDER BY b.create_date ASC";
  $data = array(':user_id' => $user_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);
  if ($stmt) {
    //クエリ結果の全データを取得
    return $stmt->fetchAll();
  } else {
    return false;
  }
}

function getMyMsgsAndBord(int $user_id)
{
  debug('自分の掲示板情報を取得します。');

  //DBへ接続
  $dbh = dbConnect();
  //掲示板レコード取得
  //SQL文作成
  $sql = 'SELECT * FROM bord AS b WHERE b.seller_id = :id OR b.buyer_id = :id AND b.delete_flg = 0';
  $data = array(':id' => $user_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);
  $rst = $stmt->fetchAll();
  if (!empty($rst)) {
    foreach ($rst as $key => $val) {
      //SQL文作成
      $sql = 'SELECT * FROM message WHERE bord_id = :id AND to_user_id = :user_id AND delete_flg = 0 ORDER BY send_date DESC';
      $data = array(':id' => $val['id'], ':user_id' => $user_id);
      //クエリ実行
      $stmt = execute($dbh, $sql, $data);
      $rst[$key]['msg'] = $stmt->fetchAll();
    }
  }

  if ($stmt) {
    //クエリ結果の全データを返却
    return $rst;
  } else {
    return false;
  }
}
/**
 * メッセージidに合致するメッセージを取得します。
 * 
 * @param int $message_id
 * 
 * @return array 
 */
function getMsg(int $message_id)
{
  $dbh = dbConnect();
  $sql = 'SELECT * FROM message WHERE id = :message_id';
  $data = array(':message_id' => $message_id);
  $stmt = execute($dbh, $sql, $data);
  if ($stmt) {
    //クエリ結果のデータを１レコード返却
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    return false;
  }
}
/**
 * カテゴリー情報をDBから取得します
 * 
 * @return array
 */
function getCategory()
{
  debug('カテゴリー情報を取得します。');

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM category';
  $data = array();
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    //クエリ結果の全データを返却
    return $stmt->fetchAll();
  } else {
    return false;
  }
}
/**
 *ソート順をDBから取得します
 * 
 * @return array 
 */
function getSort()
{
  debug('ソート情報を取得します。');

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM sort';
  $data = array();
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    //クエリ結果の全データを返却
    return $stmt->fetchAll();
  } else {
    return false;
  }
}
function isfavorite(int $user_id, int $product_id)
{
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID；' . $user_id);
  debug('商品ID；' . $product_id);

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM `like` WHERE product_id = :product_id AND user_id = :user_id';
  $data = array(':user_id' => $user_id, ':product_id' => $product_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt->rowCount()) {
    debug('お気に入りです。');
    return true;
  } else {
    debug('お気に入りではありません。');
  }
}
function getMyLike(int $user_id)
{
  debug('自分のお気に入り情報を取得します。');
  debug('ユーザーID：' . $user_id);

  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'SELECT * FROM `like` AS l LEFT JOIN products AS p ON l.product_id = p.id WHERE l.user_id = :user_id AND p.search_flg = 0 AND l.delete_flg = 0 AND p.delete_flg = 0';
  $data = array(':user_id' => $user_id);
  //クエリ実行
  $stmt = execute($dbh, $sql, $data);

  if ($stmt) {
    return $stmt->fetchAll();
  } else {
    return false;
  }
}
//================================
// その他
//================================
/**
 * サニタイズ関数
 * 
 * @param string $str
 * 
 * @return mixed
 */
function sanitize(string $str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}
/**
 * フォームに入力された値を保持します
 * 
 * @param string $str
 * @param bool $flg
 * 
 * @return mixed
 */
function getFormData(string $str, bool $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  //ユーザーデータがある場合
  if (!empty($dbFormData)) {
    //フォームのエラーがある場合
    if (!empty($err_msg[$str])) {
      //POSTにデータがある場合
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        //ない場合はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else {
      //POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}
/**
 * 画像処理関数
 * 
 * @param array $file
 * @param string $key
 * 
 * @return string $path
 */
function uploadImg(array $file, string $key)
{
  debug('画像アップロード処理開始');
  debug('FILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      //バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE: //ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:  //php.ini定義の最大サイズが超過した場合
          UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッションを変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：' . $path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}
/**
 * ページネーション作成関数
 * 
 * @param int $currentPageNum
 * @param int $totalPageNum
 * @param string $param
 * @param int $pageColNum
 */
function pagination($currentPageNum, $totalPageNum, $param = '', $pageColNum = 5)
{
  //現在のページが総ページ数と同じで、かつ、総ページ数が表示項目以上なら、左にリンク４個表示
  if ($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
    //現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個表示
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
    //現在のページが2の場合は左にリンク１個、右にリンク３個表示
  } elseif ($currentPageNum == 2 && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    //現在のページが1の場合は左に何も表示しない。右に５個表示
  } elseif ($currentPageNum == 1 && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
    //総ページ数が表示項目数より少ない場合は、総ページ数をループのMAX、ループのMinを１に設定
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    //それ以外は左に２個表示。
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  //getパラメータを付与
  if (is_array($param)) {
    //ページネーションの各リンクに遷移するとpageパラメータが重複するので配列から削除します
    if (isset($param['page'])) {
      unset($param['page']);
    }
    $param = '&' . http_build_query($param);
  }

  echo '<div class="c-pagination">';
  echo '<ul class="c-pagination__list">';
  if ($currentPageNum != 1) {
    echo '<li class="c-pagination__item"><a href="?page=1' . $param . '" class="c-pagination__link">&lt;</a></li>';
  }
  for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
    echo '<li><a href="?page=' . $i . $param . '"class="c-pagination__link';
    if ($currentPageNum == $i) {
      echo ' is-active';
    }
    echo '">' . $i . '</a></li>';
  }
  if ($currentPageNum != $maxPageNum && $maxPageNum > 1) {
    echo '<li class="c-pagination__item"><a href="?page=' . $totalPageNum . $param . '" class="c-pagination__link">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
}
/**
 * プロフィール画像表示関数
 * 
 * @param int $user_id
 * 
 * @return string
 */
function showProfImg(int $user_id)
{
  $userInfo = getuser($user_id);
  if (!empty($userInfo['pic'])) {
    return sanitize($userInfo['pic']);
  } else {
    //画像を登録してない場合初期画像を表示
    return "img/sample-profile.png";
  }
};
/**
 * URLとそのパラメータをセッションに保存します
 * 
 * ログインが必要な画面（商品購入等）でログインしていない場合に実行します
 * 
 * @param string $param
 * @param string $value
 */
function getCurrentLink(string $param, string $value)
{
  $link = basename($_SERVER['PHP_SELF']);
  debug('取得したURL：' . print_r($link, true));
  $link .= "?{$param}={$value}";
  $_SESSION['link'] = $link;
  header("Location:login.php");
}
/**
 * CSRF対策のワンタイムトークン生成関数
 */
function generateToken()
{
  $bytes = openssl_random_pseudo_bytes(16);
  return bin2hex($bytes);
}
/**
 * トークン生成実行関数
 * 
 * POSTがない場合(初回アクセス)はトークンを作成し、セッションに格納します
 * POSTがある場合はinput要素の値の$tokenを生成します
 */
function setToken()
{
  global $token;
  if (!isset($_POST['token'])) {
    $token = generateToken();
    $_SESSION['token'] = $token;
  } else {
    $token = generateToken();
  }
}
/**
 * トークン判定関数
 * 
 * POSTとSESSIONのトークンがない場合、または一致しない場合エラーメッセージを格納します
 * SESSIONにsetToken関数で生成したトークンを格納します
 */
function checkToken()
{
  global $err_msg;
  global $token;
  if (!isset($_SESSION['token']) || !isset($_POST['token']) || $_SESSION['token'] !== $_POST['token']) {
    $err_msg['common'] = MSG16;
  }
  $_SESSION['token'] = $token;
}
