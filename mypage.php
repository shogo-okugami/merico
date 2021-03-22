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
//トークンを格納
setToken();
// 画面表示用データ取得
//================================
$userId = $_SESSION['user_id'];
//DBからユーザーデータを取得
$userInfo = getUser($userId);
//DBから商品データを取得
if ((int)$userInfo['role']  === 2) {
  $productData = getMyProduct($userId);
}
//DBから連絡掲示板データを取得
$bordData = getMyProductAndBord($userId);
//DBからお気に入りデータを取得
$likeData = getMyLike($userId);

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
    <form action="withdraw.php" method="post" class="c-modal__inner">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
      <p class="c-modal__text">
        退会するとすべてのアカウント情報が削除されます。<br>
        本当に退会しますか？
      </p>
      <div class="c-modal__btn">
        <input type="submit" value="はい" class="c-btn--warning">
        <a class="c-btn--cancel js-modal-trigger">いいえ</a>
      </div>
    </form>
  </div>
  <main>
    <div class="u-bgColor--gray">
      <div class="p-mypage">
        <div class="p-mypage__sidebar">
          <ul class="p-mypage__menu">
            <?php //出品者のみ商品を登録できる
            if ((int)$userInfo['role'] === 2) {
            ?>
              <li class="p-mypage__link"><a href="resistProduct.php">商品登録</a></li>
            <?php } ?>
            <li class="p-mypage__link"><a href="profEdit.php">プロフィール編集</a></li>
            <?php //一般ユーザーのみ設定できる 
            if ((int)$userInfo['role'] === 1) {
            ?>
              <li class="p-mypage__link"><a href="followCategory.php">フォロー設定</a></li>
            <?php } ?>
            <li class="p-mypage__link"><a href="passEdit.php">パスワード変更</a></li>
            <li class="p-mypage__link"><a href="userReview.php">評価一覧</a></li>
            <li class="p-mypage__link"><a href="logout.php">ログアウト</a></li>
            <li class="p-mypage__link"><a href="#" class="js-modal-trigger">退会</a></li>
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
                          <p class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></p>
                        </div>
                        <div class="c-panel__body">
                          <p class="c-panel__title"><?php echo mb_strimwidth(sanitize($val['name']), 0, 25, '...'); ?></p>
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
                        <p class="c-panel__title"><?php echo mb_strimwidth(sanitize($val['name']), 0, 25, '...'); ?></p>
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
                        <p class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></p>
                      </div>
                      <div class="c-panel__body">
                        <p class="c-panel__title"><?php echo mb_strimwidth(sanitize($val['name']), 0, 25, '...'); ?></p>
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
                        <p class="c-panel__title"><?php echo mb_strimwidth(sanitize($val['name']), 0, 25, '...'); ?></p>
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
  <script type="text/javascript" src="js/showMessage.js"></script>
  <script type="text/javascript" src="js/tab.js"></script>
  <script type="text/javascript" src="js/modal.js"></script>
  <?php
  require('footer.php');
  ?>