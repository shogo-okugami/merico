<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
$user_id = $_SESSION['user_id'];
//DBからユーザーデータを取得
$userInfo = getUser($user_id);
//DBから商品データを取得
if ((int)$userInfo['role']  === 2) {
  $productData = getMyProduct($user_id);
}
//DBから連絡掲示板データを取得
$bordData = getMyProductAndBord($user_id);
//DBからお気に入りデータを取得
$likeData = getMyLike($user_id);

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ';
require('head.php');
?>

<body>

  <?php
  require('header.php');
  ?>
  <p class="c-notice--success js-notice">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <p class="c-notice--warning js-warning">
    <?php echo getSessionFlash('msg_warning'); ?>
  </p>

  <div class="c-modal js-modal">
    <div class="c-modal__inner">
      <p class="c-modal__text">
        退会するとすべてのアカウント情報が削除されます。<br>
        本当に退会しますか？
      </p>
      <div class="c-modal__btn">
        <a class="c-btn--warning" href="withdraw.php">はい</a>
        <a class="c-btn--cancel js-modal-trigger">いいえ</a>
      </div>
    </div>
  </div>
  <main>
    <div class="u-bgColor--gray">
      <div class="p-mypage">
        <div class="p-mypage__sidebar">
          <ul class="p-mypage__menu">
            <?php //出品者のみ商品を登録できる
            if ((int)$userInfo['role'] === 2) {
            ?>
              <li><a href="resistProduct.php" class="p-mypage__link">商品を登録する</a></li>
            <?php } ?>
            <li><a href="profEdit.php" class="p-mypage__link">プロフィール編集</a></li>
            <li><a href="passEdit.php" class="p-mypage__link">パスワードを変更する</a></li>
            <li><a href="userReview.php" class="p-mypage__link">評価一覧</a></li>
            <li><a href="#" class="p-mypage__link js-modal-trigger">退会する</a></li>
          </ul>
        </div>
        <div class="p-mypage__main">
          <div class="p-mypage__contents">
            <div class="p-mypage__img">
              <img src="<?php echo showProfImg($userInfo['pic']); ?>" alt="プロフィール画像">
            </div>
            <div class="p-mypage__name">
              <?php if (!empty($userInfo['name'])) echo $userInfo['name']; ?>
            </div>
            <div class="p-mypage__intro">
              <p class="p-mypage__intro__text <?php if (empty($userInfo['comment'])) echo 'u-color--gray'; ?>">
                <?php echo (!empty($userInfo['comment'])) ? $userInfo['comment'] : 'プロフィールを入力しましょう。'; ?>
              </p>
            </div>

            <div class="p-mypage__tab">
              <?php //出品者の商品を表示
              if ((int)$userInfo['role'] === 2) {
              ?>
                <div class="p-mypage__tab__item js-tab">
                  出品中の商品
                </div>
              <?php
              }
              ?>
              <div class="p-mypage__tab__item js-tab">
                取引中の商品
              </div>
              <div class="p-mypage__tab__item js-tab">
                お気に入りの商品
              </div>
              <div class="p-mypage__tab__item js-tab">
                取引履歴
              </div>
            </div>
            <?php //出品者のみ商品を登録できる
            if ((int)$userInfo['role'] === 2) {
            ?>
              <div class="p-mypage__tab__contents js-tab-contents">
                <?php if (!empty($productData)) : ?>
                  <div class="p-mypage__list">
                    <?php foreach ($productData as $key => $val) : ?>
                      <div class="c-panel">
                        <a href="productDetail.php?product_id=<?php echo sanitize($val['id']); ?>" class="u-extendLink"></a>
                        <div class="c-panel__img">
                          <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                        </div>
                        <div class="c-panel__body">
                          <p class="c-panel__title"><?php echo sanitize($val['name']); ?> <span class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else : ?>
                  <p class="p-mypage__text">商品はありません。</p>
                <?php endif; ?>
              </div>
            <?php
            }
            ?>
            <div class="p-mypage__tab__contents js-tab-contents">
              <?php if (!empty($bordData)) : ?>

                <div class="p-mypage__list">
                  <?php foreach ($bordData as $key => $val) : ?>
                    <div class="c-panel">
                      <a href="bord.php?bord_id=<?php echo sanitize($val['bord']); ?>" class="u-extendLink"></a>
                      <div class="c-panel__img">
                        <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                      </div>
                      <div class="c-panel__body">
                        <p class="c-panel__title"><?php echo sanitize($val['name']); ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else : ?>
                <p class="p-mypage__text">取引中の商品はありません。</p>
              <?php endif; ?>

            </div>
            <div class="p-mypage__tab__contents js-tab-contents">
              <?php if (!empty($likeData)) : ?>
                <div class="p-mypage__list">
                  <?php foreach ($likeData as $key => $val) : ?>
                    <div class="c-panel">
                      <a href="productDetail.php?product_id=<?php echo sanitize($val['id']); ?>" class="u-extendLink"></a>
                      <div class="c-panel__img">
                        <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                      </div>
                      <div class="c-panel__body">
                        <p class="c-panel__title"><?php echo sanitize($val['name']); ?> <span class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else : ?>
                <p class="p-mypage__text">お気に入りの商品はありません。</p>
              <?php endif; ?>
            </div>
            <div class="p-mypage__tab__contents js-tab-contents">
              <?php if (!empty($preBordData)) : ?>

                <div class="p-mypage__list">
                  <?php foreach ($preBordData as $key => $val) : ?>
                    <div class="c-panel">
                      <a href="bord.php?bord_id=<?php echo sanitize($val['bord']); ?>" class="u-extendLink"></a>
                      <div class="c-panel__img">
                        <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                      </div>
                      <div class="c-panel__body">
                        <p class="c-panel__title"><?php echo sanitize($val['name']); ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else : ?>
                <p class="p-mypage__text">取引履歴はありません。</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
  </main>
  <?php
  require('footer.php');
  ?>