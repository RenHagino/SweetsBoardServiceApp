<?php
 //共通関数読み込み
 require('function.php');

 //デバッグ処理
 debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
 debug('「「「「「「お気に入りスイーツの一覧ページです「「「「「');
 debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
 debugLogStart();

 //ログイン認証読み込み
 require('auth.php');

 //==================================================
 //=====  画面処理 ===================================
 //==================================================

 //==================================================
 // 変数に必要な情報を入れる =>ユーザー情報、商品データ、掲示板データ、お気に入りデータ
 $u_id = $_SESSION['user_id'];
 $sweetsData = getMySweets($u_id);
 $favorite = getMyfavorite($u_id);
 //DBからデータが取れたかデバッグで確認
 debug('取得した商品データ:'.print_r($sweetsData,true));
 debug('取得したお気に入りスイーツデータ:'.print_r($favorite,true));
?>
<!DOCTYPE html >
<html lang="ja">
  <!--ヘッドタグ呼び出し-->
  <?php
    $siteTitle ='紹介したスイーツ';
    require('head.php');
  ?>
  <!--ヘッダー-->
  <?php
    require('header.php');
  ?>
  <body>
    <!--ヘッダー呼び出し-->
    <section class="main">
        <!--マイスイーツのタイトル-->
        <h2 class="main-title main-title-myregist">
        紹介したスイーツ一覧
        </h2>
        <!--セクション２（登録スイーツリスト）-->
      <section class="list mysweets-list">
        <!--登録商品一欄を確認-->
        <?php
            if(!empty($sweetsData)):
                foreach($sweetsData as $key => $val):
        ?>  
          <div class="panel">
            <!--パネル-->
            <a href="registSweets.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'$s_id'.$val['id']:'?s_id='.$val['id']; ?>" class="panel-head" >
              <!--パネルヘッド-->
              <img src="<?php echo showImg(sanitize($val['pic1']));?>" alt="">
            </a>
            <!--パネルボディ-->
            <div class="panel-body">
              <p class="panel-body__title">
                <?php echo sanitize($val['name']);?>
              </p>
              <span class="panel-body__price">
              ¥<?php echo sanitize(number_format($val['price']));?>
              </span>
            </div>
          </div>
        <?php
            endforeach;
            endif;
        ?>
        <!-- TODO: 後で実装 
            登録したスイーツのページネーション-->
        <?php 
            //pagenation($currentMyPageNum, $countMyData['total_page'] ); 
        ?>
      </section>
    </section>
    <!--フッター呼び出し-->
    <?php 
    require('footer.php');
    ?>
  </body>
</html>
