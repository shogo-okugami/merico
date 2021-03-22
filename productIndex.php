<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「  商品検索  ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//トークンを破棄
unSetToken();

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
//サブカテゴリー
$subCategory = (!empty($_GET['sub_category'])) ? $_GET['sub_category'] : 0;
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : 0;
//検索ワード
$word = (!empty($_GET['word'])) ? $_GET['word'] : '';
$followedCategories = isset($_GET['sub_categories'][0]) ? $_GET['sub_categories'] : null;
//パラメータに不正な値が入っているかチェック
if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}

//表示件数
$listSpan = 20;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan); //１ページ目なら(1-1):20 = 0、２ページ目なら(2-1)*20 = 20
debug('カテゴリ' . print_r($followedCategories, true));
//DBから商品データを取得
$dbProductData = getProductList($currentMinNum, $category, $subCategory, $sort, $word, $listSpan, $followedCategories);
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();
//DBからソートデータを取得
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
        <?php
        foreach ($dbCategoryData as $id => $val) {
        ?>
          <div data-category="<?php echo $id; ?>" class="p-select-box js-list"><?php echo $val['name']; ?>
            <ul class="p-select-list js-menu">
              <li class="p-select-option"><a href=<?php echo "productIndex.php?category=" . $id; ?>>すべての<?php echo $val['name']; ?></a></li>
              <?php
              foreach ($dbCategoryData[$id]['sub_categories'] as $id => $val) {
              ?>
                <li class="p-select-option"><a href=<?php echo "productIndex.php?sub_category=" . $id; ?>><?php echo $val['name']; ?></a></li>
              <?php
              }
              ?>
            </ul>
          </div>
        <?php
        }
        ?>
      </section>
      <section class="p-index__main">
        <div class="p-index__result">
          <div>
            <span><?php echo (!empty($dbProductData['data'])) ? $currentMinNum + 1 : 0; ?></span> - <span><?php echo $currentMinNum + count($dbProductData['data']); ?></span>件 / <span><?php echo sanitize($dbProductData['total']); ?></span>件中
          </div>
          <div class="p-select-box js-sort-list">
            <p>並び替え：<?php echo $sort ? $dbSortData[$sort - 1]['name'] : '選択してください' ?></p>
            <ul class="p-select-list js-sort-menu">
              <?php foreach ($dbSortData as $key => $val) { ?>
                <li class="p-select-option"><a href=<?php echo "index.php?sort=" . $val['id']; ?>><?php echo $val['name']; ?></a></li>
              <?php } ?>
            </ul>
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
                <p class="c-panel__price">¥<?php echo sanitize(number_format($val['price'])); ?></p>
              </div>
              <div class="c-panel__body">
                <p class="c-panel__title"><?php echo mb_strimwidth(sanitize($val['name']), 0, 28, '...'); ?></p>
              </div>
            </div>
          <?php
          endforeach;
          ?>
        </div>

        <?php pagination($currentPageNum, $dbProductData['total_page'], $_GET); ?>

      </section>
    </section>
  </main>
  <script type="text/javascript" src="js/categoryOption.js"></script>
  <script type="text/javascript" src="js/sortOption.js"></script>
  <script type="text/javascript" src="js/wordInput.js"></script>
  <?php
  require('footer.php');
  ?>