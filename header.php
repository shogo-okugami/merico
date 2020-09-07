<header class="l-header js-header">
  <div class="l-header__container">
    <h1 class="l-header__title"><a href="index.php">merico</a></h1>
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php') : ?>
      <form action="" name="" method="get" class="js-search-form">
        <input class="c-form__input js-search-value" type="text" name="word" value="<?php echo getFormData('word', true); ?>" placeholder="キーワードを入力">
        <div class="c-btn--submit js-search-button">
          <img class="search" src="img/search-icon.svg">
          <input type="submit" value="">
        </div>
      </form>
    <?php endif; ?>
    <div>
      <?php if (empty($_SESSION['user_id'])) : ?>
        <div class="u-mb20"><a href="login.php" class="c-btn--primary">ログイン</a></div>
        <div><a href="signup.php" class="c-btn--primary">ユーザー登録</a></div>
      <?php else : ?>
        <div class="u-mb20"><a href="mypage.php" class="c-btn--primary">マイページ</a></div>
        <div><a href="logout.php" class="c-btn--primary">ログアウト</a></div>
      <?php endif; ?>
    </div>
  </div>
</header>