<?php 
  //関数読み込み
  require('function.php');
  
  //ログイン認証
  require('auth.php');

  //デバッグ(デバッグをするログ設定はfunction.phpの最初に記述)
  debug('「「「「「「「「「「「「「「「「「「「「');
  debug('「「「「「「「「new.phpです「「「「「「');
  debug('「「「「「「「「「「「「「「「「「「「「「');
  
  //ログスタート(function.phpから呼び出し)
  debugLogStart();
?>
<!--HTML-->
<!DOCTYPE html>
<html lang="ja">
  <!--ヘッドを要求-->
  <?php 
    $siteTitle = 'new.php';
    require('head.php');
  ?>
  <body>
    <!--メイン(背景画像)-->
    <div class="new-back-content">
      <!--ウェブサービスのタイトル-->
      <h1 class="new-back-content__title">SweetsBoard</h1>
      <p class="new-back-content__sentence">
        スイーツボードへようこそ<br>
        あなたの食べたスイーツをみんなに共有してみませんか？
      <p>
      <!--コンテナー-->
      <div class="btn-container_new">
        <!--登録ボタン-->
        <a href="signup.php">
          <li class="btn-container">
            <p class="btn btn-m btn-new-signup">会員登録</p>
          </li>
        </a>
        <!--ログインボタン-->
        <a href="login.php">
          <li class="btn-container">
            <p class="btn btn-m btn-new-login">ログイン</p>
          </li>
        </a>
      </div>
    </div>
  </body>
</html>