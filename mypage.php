<?php
    //共通関数読み込み
    require('function.php');

    //デバッグ処理
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debug('「「「「「「「「マイページです「「「「「「「「「「「「「');
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
    $boardData = getMyMsgsAndBoard($u_id);
    //DBからデータが取れたかデバッグで確認
    debug('取得した商品データ:'.print_r($sweetsData,true));
    debug('取得した掲示板データ:'.print_r($boardData,true));
    debug('取得したお気に入りスイーツデータ:'.print_r($favorite,true));
    
    //TODO:お気に入りスイーツのページネーション
        //$currentFavPageNum = (!empty($_GET['fs'])) ? $_GET['fs'] : 1; //現在ページ
        //$favListSpan = 6; //表示件数を設定
        //$currentFavMinNum = (($currentFavPageNum-1)*$favListSpan);　//現在の表示レコード先頭を算出 //２ページ目 = 2 - 1 * listSpan(20) なので20件目からとなる。
        //$countFavData = getFavSweetsList($currentMinNum, $u_id); //TODO $spanはfunction側で定義
    
    
    //TODO:登録したスイーツのページネーション
       // $currentMyPageNum = (!empty($_GET['ms'])) ? $_GET['ms'] : 1; //現在ページ
       // $myListSpan = 6; //表示件数を設定
       // $currentMyMinNum = 1;
       // //(($currentMyPageNum-1)*$myListSpan);　//現在の表示レコード先頭を算出 //２ページ目 = 2 - 1 * listSpan(20) なので20件目からとなる。
       // $countMyData = getMySweetsList($currentMyMinNum, $u_id); //TODO $spanはfunction側で定義






?>
<!DOCTYPE html>
<html lang="ja">
    <!--ヘッドタグ呼び出し-->
    <?php 
        $siteTitle = 'マイページ';
        require('head.php');
    ?>
    <body>
        <!--ヘッダー-->
        <?php 
            require('header.php');
            ?>
        <!--スライドメッセージ--> <!--第１５回で追加 TODO:復習-->
        <!-- フッターにjs, CSSはanimation.scssに記述 function.phpで裏側の処理-->
        <p class="js-show-msg msg-slide" style="display: none;" >
            <?php echo getSessionFlash('msg-success'); ?> <!--msg-successにSUCが指定されると開発者ツールで中身を確認できる。-->
        </p>

        <!--メイン-->
        <section class="main">
            <!--サイドバー-->
            <?php 
                require('sidebar.php');
            ?>

            <!--左サイドに設置するリスト -->
            <section class="section-right">
                <!--セクション１ お気に入りリスト-->
                <section class="list fav-list">
                    <h2 class="fav-list__title">
                        お気に入り
                    </h2>

                    <?php
                        if(!empty($favorite)):
                            foreach($favorite as $key => $val):
                    ?>
                        <a href="sweetsDetail.php<?php echo (!empty(appendGetParam()))? appendGetParam().'&s_id='.$val['id'] : '?s_id='.$val['id']; ?>" class="panel">
                            <div class="panel-head">
                                <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="">
                            </div>
                            <div class="panel-body">
                                <p class="title">
                                    <?php echo sanitize($val['name']);?>
                                    <span class="badge-price badge-s">
                                        ¥<?php echo sanitize(number_format($val['price']));?>
                                    </span>
                                </p>

                            </div>
                        </a>
                    <?php
                    endforeach;
                    endif;
                    ?>
                    <!--TODO: 後で実装
                        お気に入りしたスイーツののページネーション -->
                    <?php 
                        //favPagenation($currentFavPageNum, $countMyData['total_page'] ); 
                    ?> 
                </section>
            <section>

        </section>
    </body>
        
</html>
