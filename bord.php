<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「連絡掲示板ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
$partnerUserId = '';
$partnerUserInfo = '';
$myUserInfo = '';
$productInfo = '';
// 画面表示用データ取得
//================================
// GETパラメータを取得
$bord_id = (!empty($_GET['bord_id'])) ? (int)$_GET['bord_id'] : '';
// DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($bord_id);
debug('取得したDBデータ：' . print_r($viewData, true));
// パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:mypage.php"); //マイページへ
}
//商品情報を取得
if ($viewData[0]['product_id']) {
  $productInfo = showProduct((int)$viewData[0]['product_id']);
  debug('取得したDBデータ：' . print_r($productInfo, true));
  // 商品情報が入っているかチェック
  if (empty($productInfo)) {
    error_log('エラー発生:商品情報が取得できませんでした');
    header("Location:mypage.php"); //マイページへ
  }
}
//最新のデータを格納
$lastViewData = end($viewData);
// viewDataから相手のユーザーIDを取り出す
$dealUserIds[] = $viewData[0]['seller_id'];
$dealUserIds[] = $viewData[0]['buyer_id'];
if (($key = array_search($_SESSION['user_id'], $dealUserIds)) !== false) {
  unset($dealUserIds[$key]);
}
$partnerUserId = array_shift($dealUserIds);
debug('取得した相手のユーザーID：' . $partnerUserId);
// DBから取引相手のユーザー情報を取得
if (isset($partnerUserId)) {
  $partnerUserInfo = getUser($partnerUserId);
}
// DBから自分のユーザー情報を取得
$myUserInfo = getUser($_SESSION['user_id']);
debug('取得したユーザデータ：' . print_r($partnerUserInfo, true));
// 自分のユーザー情報が取れたかチェック
if (empty($myUserInfo)) {
  error_log('エラー発生:自分のユーザー情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
}

//トークンを格納
setToken();

// post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');

  //ログイン認証
  if (isLogin()) {
    
      // DBへ接続
      $dbh = dbConnect();

      if (!empty($_POST['buy_flg'])) {
        if ($viewData[0]['sell_flg']) {
          $sql = 'UPDATE bord SET complete_flg = 1 ,buy_flg = 1 WHERE id = :bord_id';
          $data = array(':bord_id' => $bord_id);
          $stmt = execute($dbh, $sql, $data);
          if ($stmt) {
            header("Location:mypage.php");
          }
        } else {
          $sql = 'UPDATE bord SET buy_flg = 1 WHERE id = :bord_id';
          $data = array(':bord_id' => $bord_id);
          $stmt = execute($dbh, $sql, $data);
          if ($stmt) {
            header("Location:mypage.php");
          }
        }
      }

      if (!empty($_POST['sell_flg'])) {
        if ($viewData[0]['buy_flg']) {
          $sql = 'UPDATE bord SET complete_flg = 1 ,sell_flg = 1 WHERE id = :bord_id';
          $data = array(':bord_id' => $bord_id);
          $stmt = execute($dbh, $sql, $data);
          if ($stmt) {
            header("Location:mypage.php");
          }
        } else {
          $sql = 'UPDATE bord SET sell_flg = 1 WHERE id = :bord_id';
          $data = array(':bord_id' => $bord_id);
          $stmt = execute($dbh, $sql, $data);
          if ($stmt) {
            header("Location:mypage.php");
          }
        }
      }
   
  } else {
    $_SESSION['link'];
    getCurrentLink('bord_id', $bord_id);
  }
}

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '連絡掲示板';
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>
  <p class="c-notice--success js-notice">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <main>
    <div class="p-bord">
      <div class="p-product">
        <?php if ($productInfo) { ?>
          <div class="p-product__img">
            <img src="<?php echo sanitize($productInfo['pic1']); ?>">
          </div>
          <div class="p-product__info">
            <ul>
              <li>取引商品：<?php echo sanitize($productInfo['name']); ?></li>
              <li>取引金額：<span>¥<?php echo number_format(sanitize($productInfo['price'])); ?></span></li>
              <li class="u-mb20">取引開始日：<span><?php echo date('Y/m/d', strtotime(sanitize($viewData[0]['create_date']))); ?></span></li>
              <li><a class="c-btn--submit" href="productDetail.php?product_id=<?php echo sanitize($productInfo['id']); ?>">商品情報を見る</a></li>
            </ul>

          </div>
        <?php } ?>
        <form action="" method="POST">

          <?php if ($_SESSION['user_id'] === $viewData[0]['buyer_id']) : ?>
            <?php if (!$viewData[0]['buy_flg']) : ?>
              <input type="submit" name="buy_flg" value="取引を完了する" class="c-btn--primary c-form__input">
            <?php else : ?>
              <div class="p-product__state--prohibit">取引を完了する</div>
              <?php if (!$viewData[0]['complete_flg']) : ?>
                <div class="p-product__msg">相手が取引を完了していません。しばらくお待ちください。</div>
              <?php endif; ?>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($_SESSION['user_id'] === $viewData[0]['seller_id']) : ?>
            <?php if (!$viewData[0]['sell_flg']) : ?>
              <input type="submit" name="sell_flg" value="取引を完了する" class="c-btn--primary c-btn--submit">
            <?php else : ?>
              <div class="p-product__state--prohibit">取引を完了する</div>
              <?php if (!$viewData[0]['complete_flg']) : ?>
                <div class="p-product__msg">相手が取引を完了していません。しばらくお待ちください。</div>
              <?php endif; ?>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($viewData[0]['complete_flg']) : ?>
            <div class="p-product__msg">取引が完了しました。mericoをご利用いただきありがとうございました。</div>
          <?php endif; ?>
        </form>
      </div>
      <div class="p-msg">
        <div class="p-msg__area js-message-area">
          <?php
          if (isset($viewData[0]['msg'])) {
            foreach ($viewData as $key => $val) {
              if (!empty($val['from_user_id']) && $val['from_user_id'] == $partnerUserId) {
          ?>
                <div class="p-msg__comment--left js-message">
                  <div class="p-msg__comment__img">
                    <img src="<?php echo showProfImg($partnerUserId); ?>">
                  </div>
                  <div class="p-msg__text">
                    <span class="p-msg__triangle"></span>
                    <?php echo nl2br(sanitize($val['msg'])); ?>
                  </div>
                </div>
              <?php
              } else {
              ?>
                <div class="p-msg__comment--right js-message">
                  <div class="p-msg__text">
                    <?php echo nl2br(sanitize($val['msg'])); ?>
                  </div>
                  <div class="p-msg__comment__img">
                    <img src="<?php echo showProfImg($myUserInfo['id']); ?>">
                  </div>
                </div>

            <?php
              }
            }
          } else {
            ?>
            <p class="p-msg__comment__none js-message-notice">メッセージ投稿はまだありません。</p>
          <?php
          }
          ?>

        </div>
  
        <div class="p-msg__send">
          <form action="" method="POST" class="js-message-form">
            <input type="hidden" name="bord_id" value="<?php echo $bord_id; ?>" class="js-id">
            <input type="hidden" name="send_user_name" value="<?php echo sanitize($myUserInfo['name']); ?>" class="js-user-name">
            <input type="hidden" name="to_user_id" value="<?php echo $partnerUserId; ?>" class="js-to-id">
            <input type="hidden" name="from_user_id" value="<?php echo $_SESSION['user_id']; ?>" class="js-from-id">
            <input type="hidden" name="last_id" value="<?php echo isset($lastViewData) ? $lastViewData['m_id']: ''; ?>" class="js-last-id">
            <input type="hidden" name="img" value="<?php echo showProfImg($myUserInfo['id']); ?>" class="js-user-img">
            <input type="hidden" name="partner-img" value="<?php echo showProfImg($partnerUserInfo['id']); ?>" class="js-partner-img">
            <textarea name="msg" cols="30" rows="3" class="js-count js-message-input"></textarea>
            <p class="c-form__counter u-mb35"><span class="js-count-view">0</span>/255文字</p>
            <input type="submit" name="submit" value="送信" class="c-form__input c-btn--submit js-message-submit">
          </form>
        </div>
      </div>
    </div>
  </main>
  <script type="text/javascript" src="js/message.js"></script>
  <?php
  require('footer.php');
  ?>