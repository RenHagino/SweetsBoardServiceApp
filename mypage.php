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
    // 変数に必要な情報を入れる =>ユーザー情報、商品データ、掲示板データ、お気に入りデータ
    $u_id = $_SESSION['user_id'];
    $listSpan = 9;
    $currentFavPageNum = (!empty($_GET['fav_p'])) ? $_GET['fav_p'] : 1; 
    $currentFavMinNum = (($currentFavPageNum-1)*$listSpan);
    $dbFavSweetsData = getFavSweetsList($currentFavMinNum, $listSpan, $u_id);
    
    //DBからデータが取れたかデバッグで確認
    debug('お気に入りページ1ページに表示する数:'.print_r($listSpan,true));
    debug('現在のお気にいり一覧の最小数'.print_r($currentFavMinNum, true));
    debug('現在のお気にいり一覧のページ数(GET)'.print_r($currentFavPageNum, true));
    debug('現在のお気に入り一覧の総ページ数:'.print_r($dbFavSweetsData['total_page']));
    debug('現在のお気に入り一覧のデータの中身:'.print_r($dbFavSweetsData['data'],true));


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
            <!--検索結果-->
            <section class="search-result">
                <div class="search-result__left">
                    <?php if(!empty($dbFavSweetsData)){
                        echo '<span class="total_num">'.sanitize($dbFavSweetsData['total']).'</span>個のお気に入りスイーツが見つかりました！！！';
                    }else{
                        echo '<span class="total_num">スイーツが見つかりませんでした。</span>';
                    }
                    ?>
                </div>
                <div class="search-result__right">
                    <span class="num"><?php echo (!empty($dbFavSweetsData))? $currentFavMinNum+1: 0 ;?>件から-</span>
                    <span class="num"><?php echo (!empty($dbFavSweetsData))? $currentFavMinNum+count($dbFavSweetsData['data']): 0?></span>件を表示しています/
                    <span class="num"><?php echo (!empty($dbFavSweetsData['total']))? $dbFavSweetsData['total'] : 0 ?></span>件中
                </div>
            </section>
            <!--サイドバー-->
            <section class="sidebar mypage-sidebar  section-left">
                <div class="sidebar__title">
                    <p>メニュー</p>
                </div>
                <div class="sidebar__menu">
                    <a href="registSweets.php">スイーツを登録</a><br>
                    <a href="myRegistSweets.php">紹介したスイーツ</a><br>
                    <a href="profEdit.php">プロフィール編集</a><br>
                    <a href="passEdit.php">パスワード変更</a><br>
                    <a href="withdraw.php">退会</a><br>
                </div>
            </section>
            <!--左サイドに設置するリスト -->
            <section class="section-right">
                <!--セクション１ お気に入りリスト-->
                <section class="list fav-list">
                    <h2 class="main-title main-title-mypage">
                        お気に入り一覧
                    </h2>
                    <?php
                        if(!empty($dbFavSweetsData['data'])):
                            foreach($dbFavSweetsData['data'] as $key => $val):
                    ?>
                        <!-- TODO: appendGetParamの復習 -->
                        <a class="panel" href="SweetsDetail.php?s_id=<?php echo $val['sweets_id'];?>">
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
                </section>
            </section>
        </section>
        <!--ページネーション付与
            $dbFavSweetsDataは他の関数で取得し、その中のtotal_pageを引数に指定する-->
        <?php favPagenation($currentFavPageNum, $dbFavSweetsData['total_page']); ?>

        <!--フッター呼び出し-->
        <?php require('footer.php');?>
    </body>
</html>
