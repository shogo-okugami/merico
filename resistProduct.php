<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「商品出品登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETデータを格納
$product_id = (!empty($_GET['product_id'])) ? (int)$_GET['product_id'] : '';
//DBから商品データを取得
$dbFormData = (!empty($product_id)) ? getProduct($_SESSION['user_id'], $product_id) : '';
//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();
debug('商品ID：' . $product_id);
debug('フォーム用DBデータ：' . print_r($dbFormData, true));
debug('カテゴリデータ：' . print_r($dbCategoryData, true));

// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
if (!empty($product_id) && empty($dbFormData)) {
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:mypage.php"); //マイページへ
}

//トークンを格納
setToken();

// POST送信時処理
//================================
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  //トークン判定
  checkToken();

  //変数に商品情報を代入
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = (!empty($_POST['price'])) ? $_POST['price'] : 0; //0や空文字の場合は0を入れる。デフォルトのフォームには0が入っている。
  $comment = $_POST['comment'];
  $delete_flgs = $_POST['imgdelete'];

  //productsテーブルの画像カラムにインサートする変数$pic1~3
  for ($i = 1; $i <= 3; $i++) {
    $pic = 'pic' . $i;
    $$pic = '';
    if (!empty($_FILES['pic' . $i]['name'])) {
      $$pic = uploadImg($_FILES['pic' . $i], "pic{$i}");
    } elseif (empty($$pic) && !empty($dbFormData['pic' . $i]) && empty($delete_flgs['delete' . $i])) {
      $$pic = $dbFormData['pic' . $i];
    } else {
      $$pic = '';
    }
  }
  //更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if (empty($dbFormData)) {
    //未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name', 30);
    //セレクトボックスチェック
    validSelect($category, 'category');
    //最大文字数チェック
    validMaxLen($comment, 'comment');
    //未入力チェック
    validRequired($price, 'price');
    //未入力チェック
    validRequired($comment, 'comment');
    //未入力チェック
    validRequired($pic1, 'pic1');
    //半角数字チェック
    validNumber($price, 'price');
  } else {
    if ($dbFormData['name'] !== $name) {
      //未入力チェック
      validRequired($name, 'name');
      //最大文字数チェック
      validMaxLen($name, 'name', 30);
    }
    if ($dbFormData['category_id'] !== $category) {
      //セレクトボックスチェック
      validSelect($category, 'category');
    }
    if ($dbFormData['comment'] !== $comment) {
      validRequired($comment, 'comment');
      //最大文字数チェック
      validMaxLen($comment, 'comment');
    }
    if ($dbFormData['price'] !== $price) {
      //未入力チェック
      validRequired($price, 'price');
      //半角数字チェック
      validNumber($price, 'price');
    }
    if ($dbFormData['pic1'] !== $pic1) {
      //セレクトボックスチェック
      validRequired($pic1, 'pic1');
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      //編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if ($edit_flg) {
        debug('DB更新です。');
        $sql = 'UPDATE products SET name = :name, category_id = :category, price = :price, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :user_id AND id = :product_id';
        $data = array(':name' => $name, ':category' => $category, ':price' => $price, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':user_id' => $_SESSION['user_id'], ':product_id' => $product_id);
      } else {
        debug('DB新規登録です。');
        $sql = 'insert into products (name, category_id, price, comment, pic1, pic2, pic3, user_id, create_date ) values (:name, :category, :price, :comment, :pic1, :pic2, :pic3, :user_id, :date)';
        $data = array(':name' => $name, ':category' => $category, ':price' => $price, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':user_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL；' . $sql);
      debug('流し込みデータ：' . print_r($data, true));
      //クエリ実行
      $stmt = execute($dbh, $sql, $data);

      //クエリ成功の場合
      if ($stmt) {
        $_SESSION['msg_success'] = SUC04;
        unset($_SESSION['token']);
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
      } else {
        header("Location:{resistProduct.php?product_id={$product_id}");
      }
    } catch (PDOException $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = (!$edit_flg) ? '商品登録' : '商品編集';
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>
  <main>
    <div class="u-bgColor--gray">
      <section class="p-resist">
        <form action="" method="POST" class="c-form" enctype="multipart/form-data">
          <input type="hidden" name="token" value="<?php echo $token; ?>">
          <div class="c-form__msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($err_msg['name'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>商品名</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="text" name="name" class="c-form__input" value="<?php echo getFormData('name'); ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('name');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($err_msg['category'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>カテゴリ</div>
                <div class="c-badge--required">必須</div>
              </div>
              <div class="p-select-box js-select-form">
                <div class="p-select-heading js-select-heading">
                  <?php
                  if ($_POST['category']) {
                    echo $dbCategoryData[$_POST['category'] - 1]['name'];
                  } else if ($dbFormData && $_POST['category'] === '') {
                    echo 'カテゴリを選択';
                  } else if ($dbFormData) {
                    echo $dbCategoryData[$dbFormData['category_id'] - 1]['name'];
                  } else {
                    echo 'カテゴリを選択';
                  }
                  ?>
                </div>
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
                <input type="hidden" name="category" value="<?php echo (!empty($category)) ? $category : $dbFormData['category_id']; ?>" class="js-selected-value">
              </div>
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('category');
            ?>
          </div>

          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($err_msg['price'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>金額</div>
                <div class="c-badge--required">必須</div>
              </div>
              <input type="text" name="price" class="c-form__input" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0; ?>">
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('price');
            ?>
          </div>
          <div class="c-form__item">
            <label class="c-form__label <?php if (!empty($err_msg['comment'])) echo 'is-error'; ?>">
              <div class="c-form__text">
                <div>詳細</div>
                <div class="c-badge--required">必須</div>
              </div>
              <textarea name="comment" class="c-form__input js-count" cols="30" rows="10"><?php echo getFormData('comment'); ?></textarea>
              <p class="c-form__counter">
                <span class="js-count-view">
                  <?php
                  if (!empty($_POST['comment'])) {
                    echo mb_strlen($_POST['comment']);
                  } else if ($_POST && empty($_POST['comment']) && $dbFormData['comment']) {
                    echo '0';
                  } else if ($dbFormData['comment']) {
                    echo mb_strlen($dbFormData['comment']);
                  } else {
                    echo '0';
                  }
                  ?>
                </span>/255文字</p>
            </label>
          </div>
          <div class="c-form__msg">
            <?php
            echo getErrMsg('comment');
            ?>
          </div>
          <div class="c-form__images">
            <div class="c-form__item">

              <label class="c-form__heading <?php if (!empty($err_msg['pic1'])) echo 'is-error'; ?>" for="product-image1">
                画像を選択
                <span class="triangle"></span>
                <input id="product-image1" type="file" name="pic1" class="c-form__file js-file-input">
                <div class="c-form__delete js-delete">
                  <i class="far fa-lg fa-trash-alt delete js-img-delete"><input type="checkbox" name="imgdelete[delete1]"></i>
                </div>
              </label>

              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <div class="c-form__prev js-prev">

                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="js-prev-img" <?php if (empty(getFormData('pic1'))) echo 'style=display:none;' ?>>

              </div>

              <div class="c-form__msg">
                <?php
                echo getErrMsg('pic1');
                ?>
              </div>
            </div>

            <div class="c-form__item">

              <label class="c-form__heading <?php if (!empty($err_msg['pic2'])) echo 'is-error'; ?>" for="product-image2">
                <p>画像を選択</p>
                <span class="triangle"></span>
                <input id="product-image2" type="file" name="pic2" class="c-form__file js-file-input">
                <div class="c-form__delete js-delete">
                  <i class="far fa-lg fa-trash-alt delete js-img-delete"><input type="checkbox" name="imgdelete[delete2]"></i>
                </div>
              </label>

              <div class="c-form__prev js-prev">
                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="js-prev-img" <?php if (empty(getFormData('pic2'))) echo 'style=display:none;' ?>>
              </div>
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <div class="c-form__msg">
                <?php
                if (!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                ?>
              </div>
            </div>
            <div class="c-form__item">

              <label class="c-form__heading <?php if (!empty($err_msg['pic3'])) echo 'is-error'; ?>" for="product-image3">
                <p>画像を選択</p>
                <span class="triangle"></span>
                <input id="product-image3" type="file" name="pic3" class="c-form__file js-file-input">
                <div class="c-form__delete js-delete">
                  <i class="far fa-lg fa-trash-alt delete js-img-delete"><input type="checkbox" name="imgdelete[delete3]"></i>
                </div>
              </label>

              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <div class="c-form__prev js-prev">

                <img src="<?php echo getFormData('pic3'); ?>" alt="" class="js-prev-img" <?php if (empty(getFormData('pic3'))) echo 'style=display:none;' ?>>

              </div>
              <div class="c-form__msg">
                <?php
                if (!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                ?>
              </div>
            </div>

          </div>
          <div class="c-form__item">
            <input type="submit" class="c-btn--submit" value="登録する">
          </div>
        </form>
      </section>
    </div>
  </main>
  <script type="text/javascript" src="js/imageHandle.js"></script>
  <?php
  require('footer.php');
  ?>