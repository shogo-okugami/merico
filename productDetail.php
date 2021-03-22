<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「商品詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$productId = (!empty($_GET['product_id'])) ? (int)$_GET['product_id'] : '';
// DBから商品データを取得
$viewData = showProduct($productId);
// パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
//DBからユーザー情報を取得
$sellerInfo = getUser($viewData['user_id']);
$sellerId = (int)$sellerInfo['id'];
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();
//トークンを格納
setToken();
debug('取得した商品データ：' . print_r($viewData, true));
debug('取得したユーザーデータ：' . print_r($sellerInfo, true));
debug('カテゴリデータ：' . print_r($dbCategoryData, true));
//POST送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');

  //ログイン済みの場合購入できる
  if (isLogin()) {

    //トークン判定
    checkToken();

    if (empty($errMsg)) {
      try {
        //DBへ接続
        $dbh = dbConnect();
        //購入の場合
        if (!empty($_POST['submit'])) {
          //SQL文作成
          $sql1 = 'INSERT INTO bord (seller_id, buyer_id, product_id, create_date) VALUES (:seller_id, :buyer_id, :product_id, :date)';
          $sql2 = 'UPDATE products SET search_flg = 1 WHERE id = :product_id';
          $data1 = array(':seller_id' => $viewData['user_id'], ':buyer_id' => $_SESSION['user_id'], ':product_id' => $productId, ':date' => date('Y-m-d H:i:s'));
          $data2 = array(':product_id' => $productId);
          //クエリ実行
          try {
            $dbh->beginTransaction();
            $stmt2 = execute($dbh, $sql2, $data2);
            $stmt1 = execute($dbh, $sql1, $data1);
            //クエリ成功の場合
            if ($stmt1 && $stmt2) {
              $_SESSION['msg_success'] = SUC06;
              $bord_id = $dbh->lastInsertId();
              $dbh->commit();
              header("Location:bord.php?bord_id=" . $bord_id); //連絡掲示板へ
            }
          } catch (Exception $e) {
            $dbh->rollBack();
            error_log('エラー発生：' . $e->getMessage());
            $err_msg['common'] = MSG07;
          }
        }
        //削除の場合
        if (!empty($_POST['delete'])) {
          //SQL文作成
          $sql = 'UPDATE products SET delete_flg = 1 WHERE user_id = :user_id AND id = :product_id';
          $data = array(':user_id' => $viewData['user_id'], ':product_id' => $productId);
          //クエリ実行
          $stmt = execute($dbh, $sql, $data);
          if ($stmt) {
            debug('マイページへ遷移します。');
            header("Location:mypage.php");
          }
        }
      } catch (PDOException $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  } else {
    $_SESSION['link'];
    getCurrentLink('product_id', $productId);
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = sanitize($viewData['name']);
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>
  <div class="c-modal js-modal">
    <div class="c-modal__inner">
      <p class="c-modal__text">
        この商品を削除します。<Br>
        本当によろしいですか？
      </p>
      <div class="c-modal__btn">
        <a class="c-btn--warning">
          <form action="" method="POST">
            <input name="delete" type="submit" value="はい">
          </form>
        </a>
        <a class="c-btn--cancel js-modal-trigger">いいえ</a>
      </div>
    </div>
  </div>
  <main>
    <div class="p-product">
      <div class="p-product__info">
        <div class="p-product__view">
          <div class="p-product__image">
            <img class="js-main-img" src="<?php echo sanitize($viewData['pic1']); ?>" alt="メイン画像；<?php echo sanitize($viewData['name']); ?>">
          </div>
          <div class="p-product__images">
            <p>商品画像</p>
            <?php if (!empty($viewData['pic1'])) : ?>
              <div class="p-product__image--sub">
                <img class="js-sub-img" src="<?php echo sanitize($viewData['pic1']); ?>" alt="画像1；<?php echo sanitize($viewData['name']) ?>">
              </div>
            <?php endif; ?>
            <?php if (!empty($viewData['pic2'])) : ?>
              <div class="p-product__image--sub">
                <img class="js-sub-img" src="<?php echo sanitize($viewData['pic2']); ?>" alt="画像2；<?php echo sanitize($viewData['name']) ?>">
              </div>
            <?php endif; ?>
            <?php if (!empty($viewData['pic3'])) : ?>
              <div class="p-product__image--sub">
                <img class="js-sub-img" src="<?php echo sanitize($viewData['pic3']); ?>" alt="画像3；<?php echo sanitize($viewData['name']) ?>">
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="p-product__desc">
          <div class="p-product__editor">
            <?php if ($_SESSION['user_id'] === $viewData['user_id'] && !$viewData['search_flg']) : ?>
              <i class="far fa-edit fa-2x edit">
                <a href="resistProduct.php?product_id=<?php echo $viewData['id']; ?>" class="u-extendLink"></a>
              </i>
            <?php endif; ?>
            <?php if (!$viewData['search_flg'] && $_SESSION['user_id'] === $viewData['user_id']) : ?>
              <i class="far fa-trash-alt fa-2x js-modal-trigger"></i>
            <?php endif; ?>
          </div>
          <div>
            <span class="c-badge--primary"><?php echo sanitize($viewData['category']); ?></span>
          </div>
          <div class="p-product__name">
            <?php echo sanitize($viewData['name']); ?>
          </div>

          <div class="p-product__price">
            ¥<?php echo sanitize((number_format($viewData['price']))); ?>
          </div>

          <div>
            <?php if ($_SESSION['user_id'] !== $viewData['user_id'] && (int)$_SESSION['role'] === 1) : ?>
              <?php if (!$viewData['search_flg']) : ?>
                <form action="" method="post">
                  <input type="hidden" name="token" value="<?php echo $token; ?>">
                  <input type="submit" class="c-btn--submit c-btn--primary" value="購入する" name="submit">
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <?php if (!$viewData['search_flg'] && $_SESSION['user_id']) : ?>
            <div class="p-product__fav">
              <i class="fas fa-heart fa-2x fav js-like <?php if (isfavorite($_SESSION['user_id'], $viewData['id'])) {
                                                          echo 'is-active';
                                                        } ?>" aria-hidden="true" data-productid="<?php echo sanitize($viewData['id']); ?>"></i>
              <span>お気に入りに登録する</span>
            </div>
          <?php endif; ?>
          <div class="p-product__seller">
            <a href="userDetail.php?user_id=<?php echo sanitize($sellerId); ?>" class="u-extendLink"><?php echo sanitize($sellerInfo['name']); ?>さんが出品しました</a>
          </div>
        </div>
      </div>
      <div class="p-product__comment">
        <p class="p-product__comment__heading">商品説明</p>
        <p class="p-product__comment__inner"><?php echo nl2br(sanitize($viewData['comment'])); ?></p>
      </div>
    </div>
  </main>
  <script type="text/javascript" src="js/likeInput.js"></script>
  <script type="text/javascript" src="js/switchImg.js"></script>
  <script type="text/javascript" src="js/modal.js"></script>
  <?php
  require('footer.php');
  ?>