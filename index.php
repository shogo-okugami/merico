<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「  商品検索  ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================

// GETパラメータを取得
//----------------------------------
// カレントページ
$currentPageNum = (!empty($_GET['page'])) ? $_GET['page'] : 1; //デフォルトは１ページ
//カテゴリー
$category = (!empty($_GET['category'])) ? $_GET['category'] : 0;
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : 0;
//検索ワード
$word = (!empty($_GET['word'])) ? $_GET['word'] : '';
//パラメータに不正な値が入っているかチェック
if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}

//表示件数
$listSpan = 20;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan); //１ページ目なら(1-1):20 = 0、２ページ目なら(2-1)*20 = 20
//DBから商品データを取得
$dbProductData = getProductList($currentMinNum, $category, $sort, $word);
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();

$dbSortData = getSort();

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '商品一覧';
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>

  <main>
    <section class="p-index">
      <section class="p-index__sidebar">
        <form class="p-index__search" name="" method="get">
          <input type="hidden" class="js-word-value" name="word" value="<?php echo getFormData('word', true); ?>">
          <div class="p-select-box js-select-form">
            <div class="p-select-heading js-select-heading"><?php echo $category ? $dbCategoryData[$category - 1]['name'] : 'カテゴリを選択'; ?></div>
            <ul class="p-select-list js-select-list">
            <li data-category="0" class="p-select-option js-select-option js-category-option">カテゴリを選択</li>
              <?php
              foreach ($dbCategoryData as $key => $val) {
              ?>
                <li data-category="<?php echo $val['id']; ?>" class="p-select-option js-select-option js-category-option"><?php echo $val['name']; ?></li>
              <?php
              }
              ?>
            </ul>
            <input type="text" name="category" value="<?php echo $category; ?>" class="c-form__input--hidden js-selected-value">
          </div>
          <div class="p-select-box js-select-form">
            <div class="p-select-heading js-select-heading"><?php echo $sort ? $dbSortData[$sort - 1]['name'] : '表示順を選択'; ?></div>
            <ul class="p-select-list js-select-list">
            <li data-category="0" class="p-select-option js-select-option js-sort-option">表示順を選択</li>
              <?php
              foreach ($dbSortData as $key => $val) {
              ?>
                <li data-sort="<?php echo $val['id']; ?>" class="p-select-option js-select-option js-sort-option"><?php echo $val['name']; ?></li>
              <?php
              }
              ?>
            </ul>
            <input type="text" name="sort" value="<?php echo $sort; ?>" class="c-form__input--hidden js-selected-value">
          </div>
          <input type="submit" class="c-btn--submit" value="検索">
        </form>
      </section>
      <section class="p-index__main">
        <div class="p-index__result">
          <div>
            <span><?php echo sanitize($dbProductData['total']); ?></span>件の商品が見つかりました
          </div>
          <div>
            <span><?php echo (!empty($dbProductData['data'])) ? $currentMinNum + 1 : 0; ?></span> - <span><?php echo $currentMinNum + count($dbProductData['data']); ?></span>件 / <span><?php echo sanitize($dbProductData['total']); ?></span>件中
          </div>
        </div>
        <div class="p-index__list">
          <?php
          foreach ($dbProductData['data'] as $key => $val) :
          ?>
            <div class="c-panel">
              <a href="productDetail.php?product_id=<?php echo sanitize($val['id']); ?>" class="u-extendLink"></a>
              <div class="c-panel__img">
                <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
              </div>
              <div class="c-panel__body">
                <p class="c-panel__title"><?php echo sanitize($val['name']); ?></p>
                <p class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></p>
              </div>
            </div>
          <?php
          endforeach;
          ?>
        </div>

        <?php pagination($currentPageNum, $dbProductData['total_page'],$_GET); ?>

      </section>
    </section>
  </main>
  <?php
  require('footer.php');
  ?>