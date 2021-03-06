<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザーページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//トークンを破棄
unsetToken();
//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$userId = (!empty($_GET['user_id'])) ? $_GET['user_id'] : '';
// DBからユーザーデータを取得
$sellerInfo = getUser($userId);
//DBから商品データを取得
$productData = getMyProduct($userId);

// パラメータに不正な値が入っているかチェック
if (empty($sellerInfo)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:home.php"); //トップページへ
}
debug('取得したユーザーデータ：' . print_r($userInfo, true));
?>
<?php
$siteTitle = 'ユーザープロフィール';
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>
  <main>
    <div class="u-bgColor--gray">
      <div class="c-container">
        <div class="p-profile">
          <div class="p-profile__img">
            <img src="<?php echo showProfImg($sellerInfo['pic']); ?>" alt="プロフィール画像">
          </div>
          <div class="p-profile__name">
            <?php if (!empty($sellerInfo['name'])) echo sanitize($sellerInfo['name']); ?>
          </div>
          <div class="p-profile__intro">
            <p class="p-profile__intro__text <?php if (empty($sellerInfo['comment'])) echo 'u-color--gray'; ?>">
              <?php echo (!empty($sellerInfo['comment'])) ? sanitize($sellerInfo['comment']) : 'コメントはありません。'; ?>
            </p>
          </div>
          <div class="p-profile__heading">
            <?php echo sanitize($sellerInfo['name']) . 'さんが出品している商品'; ?>
          </div>
          <div class="p-profile__list">
            <?php
            if (!empty($productData)) :
              foreach ($productData as $key => $val) :
            ?>
                <div class="c-panel">
                  <a href="productDetail.php?product_id=<?php echo sanitize($val['id']); ?>" class="u-extendLink"></a>
                  <div class="c-panel__img">
                    <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                    <p class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></p>
                  </div>
                  <div class="c-panel__body">
                    <p class="c-panel__title"><?php echo sanitize($val['name']); ?></p>
                  </div>
                </div>
              <?php
              endforeach;
            else :
              ?>
              <p>商品はありません。</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php
  require('footer.php');
  ?>