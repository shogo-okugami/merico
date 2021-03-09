<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「カテゴリーフォローページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$dbCategoryData = getCategory();
$dbSubCategoryData = [];
foreach ($dbCategoryData as $key => $val) {
  $dbSubCategoryData += $val['sub_categories'];
}
$followedCategories = getFollowedCategories($_SESSION['user_id']);
debug('カテゴリーデータ：' . print_r($dbSubCategoryData, true));
debug('フォロー中' . print_r($followedCategories, true));
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'フォロー設定';
require('head.php');
?>

<body>

  <?php
  require('header.php');
  ?>

  <main>
    <div class="u-bgColor--gray">
      <section class="p-follow">
        <div class="p-follow__body">
          <ul>
            <?php
            foreach ($dbSubCategoryData as $id => $val) {
              $followed = in_array($id, $followedCategories);
            ?>
              <li class="p-follow__item">
                <p><?php echo $val['name']; ?></p><button data-subcategory="<?php echo $id; ?>" class="p-follow__btn <?php echo $followed ? 'is-followed' : ''; ?> js-follow"><?php echo $followed ? 'フォロー中' : 'フォローする' ?></button>
              </li>
            <?php
            }
            ?>
          </ul>
        </div>
      </section>
    </div>
  </main>
  <script type="text/javascript" src="js/followInput.js"></script>
  <?php
  require('footer.php');
  ?>