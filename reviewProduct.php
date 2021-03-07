<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「レビューページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================

$bordId = !empty($_GET['bord_id']) ? $_GET['bord_id'] : '';
// DBから掲示板とメッセージデータを取得
$bordData = getMsgsAndBord($bordId, false);
debug('取得したDBデータ：' . print_r($bordData, true));
// パラメータに不正な値が入っているかチェック
if (empty($bordData)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:mypage.php"); //マイページへ
}

//トークンを格納
setToken();

if (!empty($_POST)) {
  debug('POST送信があります。');

  //ログイン認証
  if (isLogin()) {

    //トークン判定
    checkToken();

    $rate = $_POST['rate'];
    $comment = $_POST['comment'];

    validSelect($rate, 'rate');
    validRequired($comment, 'comment');
    validMaxlen($comment, 'comment');

    if (empty($errMsg)) {
      debug('バリデーションOKです');

      try {
        // DBへ接続
        $dbh = dbConnect();

        //購入者側の送信
        if (!empty($_POST['buy_flg'])) {
          debug('購入者の評価です');
          $sql1 = 'INSERT INTO reviews (rate,comment,user_id,reviewer_id,product_id,create_date) VALUES (:rate,:comment,:user_id,:reviewer_id,:product_id,:date)';
          $data1 = array(':rate' => $rate, ':comment' => $comment, ':user_id' => $bordData[0]['seller_id'], ':reviewer_id' => $_SESSION['user_id'], ':product_id' => $bordData[0]['product_id'], ':date' => date('Y-m-d H:i:s'));
          //出品者が取引完了している場合
          if ($bordData[0]['sell_flg']) {
            $sql2 = 'UPDATE bord SET complete_flg = 1 ,buy_flg = 1 WHERE id = :bord_id';
            $data2 = array(':bord_id' => $bordId);

            //例外処理
            try {
              $dbh->beginTransaction();
              $stmt1 = execute($dbh, $sql1, $data1);
              $stmt2 = execute($dbh, $sql2, $data2);
              if ($stmt1 && $stmt2) {
                $dbh->commit();
                unset($_SESSION['token']);
                $_SESSION['msg_success'] = SUC03;
                header("Location:mypage.php");
              }
            } catch (PDOException $e) {
              $dbh->rollback();
              error_log('エラー発生：' . $e->getMessage());
              $errMsg['common'] = MSG07;
            }
          } else {
            $sql2 = 'UPDATE bord SET buy_flg = 1 WHERE id = :bord_id';
            $data2 = array(':bord_id' => $bordId);

            try {
              $dbh->beginTransaction();
              $stmt1 = execute($dbh, $sql1, $data1);
              $stmt2 = execute($dbh, $sql2, $data2);
              if ($stmt1 && $stmt2) {
                $dbh->commit();
                unset($_SESSION['token']);
                $_SESSION['msg_success'] = SUC03;
                header("Location:mypage.php");
              }
            } catch (PDOException $e) {
              $dbh->rollback();
              error_log('エラー発生：' . $e->getMessage());
              $errMsg['common'] = MSG07;
            }
          }
        }

        //出品者側の送信
        if (!empty($_POST['sell_flg'])) {
          debug('出品者の評価です');
          $sql1 = 'INSERT INTO reviews (rate,comment,user_id,reviewer_id,product_id,create_date) VALUES (:rate,:comment,:user_id,:reviewer_id,:product_id,:date)';
          $data1 = array(':rate' => $rate, ':comment' => $comment, ':user_id' => $bordData[0]['buyer_id'], ':reviewer_id' => $_SESSION['user_id'], ':product_id' => $bordData[0]['product_id'], ':date' => date('Y-m-d H:i:s'));
          //購入者が取引完了している場合
          if ($bordData[0]['buy_flg']) {
            $sql2 = 'UPDATE bord SET complete_flg = 1 ,sell_flg = 1 WHERE id = :bord_id';
            $data2 = array(':bord_id' => $bordId);

            //例外処理
            try {
              $dbh->beginTransaction();
              $stmt1 = execute($dbh, $sql1, $data1);
              $stmt2 = execute($dbh, $sql2, $data2);
              if ($stmt1 && $stmt2) {
                $dbh->commit();
                unset($_SESSION['token']);
                $_SESSION['msg_success'] = SUC03;
                header("Location:mypage.php");
              }
            } catch (PDOException $e) {
              $dbh->rollback();
              error_log('エラー発生：' . $e->getMessage());
              $errMsg['common'] = MSG07;
            }
          } else {
            $sql2 = 'UPDATE bord SET sell_flg = 1 WHERE id = :bord_id';
            $data2 = array(':bord_id' => $bordId);

            //例外処理
            try {
              $dbh->beginTransaction();
              $stmt1 = execute($dbh, $sql1, $data1);
              $stmt2 = execute($dbh, $sql2, $data2);
              if ($stmt1 && $stmt2) {
                $dbh->commit();
                unset($_SESSION['token']);
                $_SESSION['msg_success'] = SUC03;
                header("Location:mypage.php");
              }
            } catch (PDOException $e) {
              $dbh->rollback();
              error_log('エラー発生：' . $e->getMessage());
              $errMsg['common'] = MSG07;
            }
          }
        }
      } catch (PDOException $e) {
        error_log('エラー発生：' . $e->getMessage());
        $errMsg['common'] = MSG07;
      }
    }
  } else {
    $_SESSION['link'];
    getCurrentLink('bord_id', $bordId);
  }
}
?>
<?php
$siteTitle = '取引評価';
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
          <input type="hidden" name="rate" value="">
          <input type="hidden" name="<?php echo $_SESSION['user_id'] === $bordData[0]['buyer_id'] ? 'buy_flg' : 'sell_flg'; ?>" value="true">
          <input type="hidden" name="token" value="<?php echo $token; ?>">
          <div class="c-form__msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>

          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['rate'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>評価(星をクリックしてください)</div>
                <div class="c-badge--required">必須</div>
              </div>
              <div>
                <?php
                for ($i = 1; $i <= 5; $i++) {
                  echo '<span class="star u-ml15 js-star">★</span>';
                }
                ?>
              </div>
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('rate');
            ?>
          </div>

          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($errMsg['comment'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>詳細</div>
                <div class="c-badge--required">必須</div>
              </div>
              <textarea name="comment" class="c-form__input js-count" cols="30" rows="10"><?php echo getFormData('comment'); ?></textarea>
              <p class="c-form__counter">
                <span class="js-count-view">
                  <?php
                  if (!empty($_POST['comment'])) {
                    echo mb_strlen($_POST['comment']);
                  } else {
                    echo '0';
                  }
                  ?>
                </span>/255文字</p>
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('comment');
            ?>
          </div>

          <div class="c-form__item">
            <input type="submit" class="c-btn--submit" value="取引を完了する">
          </div>
        </form>
      </section>
    </div>
  </main>
  <script type="text/javascript" src="js/rateHandle.js"></script>
  <?php
  require('footer.php');
  ?>