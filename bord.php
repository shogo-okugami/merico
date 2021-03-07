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
$bordId = (!empty($_GET['bord_id'])) ? (int)$_GET['bord_id'] : '';
// DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($bordId);
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
//最新の掲示板データを格納
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
  $partnerImg = $partnerUserInfo['pic'];
}
// DBから自分のユーザー情報を取得
$myUserInfo = getUser($_SESSION['user_id']);
$myImg = $myUserInfo['pic'];

debug('取得したユーザデータ：' . print_r($partnerUserInfo, true));
// 自分のユーザー情報が取れたかチェック
if (empty($myUserInfo)) {
  error_log('エラー発生:自分のユーザー情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
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
          <?php
          //ユーザーと購入者のIDが一致している場合
          if ($_SESSION['user_id'] === $viewData[0]['buyer_id']) {
            //購入者（ユーザー）が取引を完了していない場合
            if (!$viewData[0]['buy_flg']) {
          ?>
              <div><a href="reviewProduct.php?bord_id=<?php echo sanitize($bordId); ?>" class="c-btn--primary c-form__input">取引を完了する</a></div>
            <?php
              //完了している場合
            } else {
            ?>
              <div class="p-product__state--prohibit">取引を完了する</div>
              <?php
              //相手が取引を完了していない場合
              if (!$viewData[0]['complete_flg']) {
              ?>
                <div class="p-product__msg">相手が取引を完了していません。しばらくお待ちください。</div>
          <?php }
            }
          } ?>

          <?php
          //ユーザーと出品者のIDが一致している場合
          if ($_SESSION['user_id'] === $viewData[0]['seller_id']) {
            //出品者（ユーザー）が取引を完了していない場合
            if (!$viewData[0]['sell_flg']) {
          ?>
              <div><a href="reviewProduct.php?bord_id=<?php echo sanitize($bordId); ?>" class="c-btn--primary c-form__input">取引を完了する</a></div>
            <?php
              //完了している場合
            } else {
            ?>
              <div class="p-product__state--prohibit">取引を完了する</div>
              <?php
              //相手が取引を完了していない場合
              if (!$viewData[0]['complete_flg']) {
              ?>
                <div class="p-product__msg">相手が取引を完了していません。しばらくお待ちください。</div>
          <?php }
            }
          } ?>
          <?php
          //購入者と出品者が取引を完了している場合
          if ($viewData[0]['complete_flg']) {
          ?>
            <div class="p-product__msg">取引が完了しました。mericoをご利用いただきありがとうございました。</div>
          <?php } ?>
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
                    <img src="<?php echo showProfImg($partnerImg); ?>">
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
                    <img src="<?php echo showProfImg($myImg); ?>">
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
            <input type="hidden" name="bord_id" value="<?php echo $bordId; ?>" class="js-id">
            <input type="hidden" name="send_user_name" value="<?php echo sanitize($myUserInfo['name']); ?>" class="js-user-name">
            <input type="hidden" name="to_user_id" value="<?php echo $partnerUserId; ?>" class="js-to-id">
            <input type="hidden" name="from_user_id" value="<?php echo $_SESSION['user_id']; ?>" class="js-from-id">
            <input type="hidden" name="last_id" value="<?php echo isset($lastViewData) ? $lastViewData['m_id'] : ''; ?>" class="js-last-id">
            <input type="hidden" name="img" value="<?php echo showProfImg($myImg); ?>" class="js-user-img">
            <input type="hidden" name="partner-img" value="<?php echo showProfImg($partnerImg); ?>" class="js-partner-img">
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