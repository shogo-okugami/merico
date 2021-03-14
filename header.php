<header class="l-header js-header">
  <div class="l-header__container">
    <h1 class="l-header__title"><a href="index.php">merico</a></h1>
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php' || 'productIndex.php') : ?>
      <form action="productIndex.php" name="" method="get" class="js-search-form">
        <input class="c-form__input js-search-value" type="text" name="word" value="<?php echo getFormData('word', true); ?>" placeholder="キーワードを入力">
        <div class="c-btn--submit js-search-button">
          <img class="search" src="img/search-icon.svg">
          <input type="submit" value="">
        </div>
      </form>
    <?php endif; ?>
    <?php
    //ログインしていない場合
    if (!isLogin()) {
    ?>
      <div><a class="c-btn--login" href="login.php">ログイン</a></div>
    <?php
    //ログインしている場合はユーザーのプロフィール画像を表示する
     } else {
      $userInfo = getUser($_SESSION['user_id']);
    ?>
      <div class="p-profile__img--small">
        <img src="<?php echo sanitize(showProfImg($userInfo['pic'])); ?>" alt="プロフィール画像" />
        <a class="u-extendLink" href="mypage.php"></a>
      </div>
    <?php } ?>
  </div>
</header>