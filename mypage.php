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
    $favorite = getMyfavorite($u_id);
    $boardData = getMyMsgsAndBoard($u_id);
    //DBからデータが取れたかデバッグで確認
    debug('取得した掲示板データ:'.print_r($boardData,true));
    debug('取得したお気に入りスイーツデータ:'.print_r($favorite,true));

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
        <!--スライドメッセージ-->
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
