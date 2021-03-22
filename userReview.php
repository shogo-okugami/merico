<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「レビューリストページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');
//トークンを破棄
unSetToken();
// 画面表示用データ取得
//================================
$userId = $_SESSION['user_id'];
//DBからユーザーデータを取得
$userInfo = getUser($userId);
$rate = getAvgRate($userId);
$rate = (float)$rate['avg_rate'];
$viewData = getReviews($userId);
debug('評価' . print_r($viewData, true))
?>
<?php
$siteTitle = '評価一覧';
require('head.php');
?>

<?php
require('header.php');
?>

<body>
  <main>
    <div class="u-bgColor--gray">
      <div class="c-container">
        <div class="c-container__body">
          <div class="c-container__heading"><?php echo $userInfo['name']; ?>さんの評価
            <?php if (!isset($viewData[0])) : ?>
              <p class="c-container__text--passive">評価はまだありません。</p>
            <?php else : ?>
              <p><span class="star5_rating" data-rate="<?php echo setRate($rate); ?>"></span><span class="u-ml15"><?php echo $rate; ?></span></p>

            <?php endif; ?>
          </div>
          <div class="review">
            <?php if (isset($viewData)) {
              foreach ($viewData as $review) {
                $reviewer = getUser($review['reviewer_id']);
            ?>
                <div class="review__item">
                  <div class="p-msg__comment__img">
                    <img src="<?php echo showProfImg($reviewer['pic']); ?>">
                    <span class="review__info u-ml15"><?php echo sanitize($reviewer['name']); ?></span>
                  </div>
                  <div class="review__info"><span class="star5_rating" data-rate="<?php echo setRate($review['rate']); ?>"></span></div>
                  <div class="review__comment">
                    <?php echo nl2br(sanitize($review['comment'])); ?>
                  </div>
                  <div class="u-mt40"><span class="review__info"><?php echo date('Y/m/d', strtotime(sanitize($review['create_date']))); ?></span></div>
                </div>
            <?php }
            } ?>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php
  require('footer.php');
  ?>