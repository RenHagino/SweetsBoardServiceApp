<!-- スイーツの詳細画面処理フロー
    1:GETパラメータがあるかチェック、＝＞画面に表示するDBデータを取得、できなければトップページへ遷移
    2:POSTされているかチェック
    3:ログイン認証（購入ユーザー）が誰か分からないと掲示板のデータをいじれないので
    4:DB新規登録
    5:掲示板ページへ遷移

-->

<?php 
    //共通関数読み込み
    require('function.php');
    //デバッグ
    debugLogStart();
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debug('「 商品詳細ページ 」');
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');

    //=======================================================
    // スイーツ詳細画面：画面処理
    //=======================================================

    //== 画面表示用データ取得 == ///////////////////////////////

        //スイーツのGETパラメータを取得
        $s_id = (!empty($_GET['s_id'])) ? $_GET['s_id'] : '' ;
        //デバッグ
        debug('現在見ているスイーツ詳細のID:'.$s_id);
        
        //DBからスイーツデータを取得 //Sweetsテーブルとカテゴリーテーブルを結合している。
        $viewData = getSweetsDetail($s_id);
        debug('取得したDBのviewData:'.print_r($viewData,true));
    
        //パラメータに不正な値が入っているかチェック
        if(empty($viewData)){
            error_log('エラー発生:指定ページに不正な値が入りましたよ');
            header("Location:home.php");
        }

    //デバッグ
    debug('＝＝＝＝＝＝＝＝＝＝＝  商品詳細画面：画面表示処理終了  ＝＝＝＝＝＝＝＝＝＝＝＝＝');

?>

<!DOCTYPE html>
<html lang="ja">
    <?php
        $siteTitle = '商品詳細画面';
        require('head.php');
    ?>
    <body>
        <!--ヘッダー-->
        <!--詳細画面ではいらない？-->
        <h2 class="main-title main-title__sweetsdetail">
            スイーツの詳細
        </h2>
        <!--メインセクション-->
        <section class="main">
            <!--==========================-->
            <!--スイーツ詳細(情報)-->
            <!--==========================-->
            <section class="sweets-detail__info">
                <!--スイーツの名前とカテゴリー-->
                <div class="name">
                    <?php echo sanitize($viewData['name']); ?>
                </div>
                <!--カテゴリー-->
                <span class="category">
                    <p><?php echo sanitize($viewData['category_name']); ?></p>
                </span>
                <!--値段表示-->
                <div class="price">
                    <p>¥<?php echo sanitize(number_format($viewData['price'])); ?></p>
                </div>
                <!--お気に入りボタン isFavでDBにすでにお気に入り登録されていた場合、アイコンをactiveにして色をつけておく。-->
                <div class="like">
                    <!--isFav関数を使って-->
                    <i class="fas fa-heart icn-like js-like-click <?php if(isFav($_SESSION['user_id'], $viewData['id'])){ echo 'active'; }?>"
                        aria-hidden="true"
                        data-sweetsid = "<?php echo sanitize($viewData['id']); ?>" >
                    </i>
                </div>
            </section>
            <!--==========================-->
            <!--スイーツ詳細(メイン=>画像&コメント)-->
            <!--==========================-->
            <section class="sweets-detail__main">
                <!--スイーツ画像-->
                <div class="sweets-imgs-container">
                    <div class="img-main">
                        <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像": <?php sanitize($viewData['name']); ?> class="js-switch-img-main">
                    </div>
                    <div class="img-sub">
                        <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="画像１": <?php sanitize($viewData['name']);?> class="js-switch-img-sub">
                        <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="画像２": <?php sanitize($viewData['name']);?> class="js-switch-img-sub">
                        <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="画像３": <?php sanitize($viewData['name']);?> class="js-switch-img-sub">
                    </div>
                </div>
                <!--スイーツの詳細-->
                <div class="comment">
                    <p><?php echo sanitize($viewData['comment']);?></p>
                </div>
            </section>
        </section>
        <!--バナーセクション-->
        <section class="sweets-detail__undermenu">
            <!--商品一覧に戻るボタン。 appendGetParamが無いと２ページ目の商品の詳細画面から戻るボタンを押した時に１ページ目に戻るのでappendGetParamは必要-->
            <div class="btn-container item-left">
                <!--戻った時に該当商品のページ数まで含めて戻れるようにappendGetParamをつけている。 s_idは商品のIDだが、リンクに必要ないので取り除くパラメータに指定している。-->
                <a class="btn btn-m" href="home.php<?php echo appendGetParam(array('s_id')); ?>">&lt; 商品一覧に戻る </a>
            </div>
            <!--出品者とチャットをするボタン TODO:掲示板ページを追加する-->
            <div class="btn-container item-right">
                <!--戻った時に該当商品のページ数まで含めて戻れるようにappendGetParamをつけている。 s_idは商品のIDだが、リンクに必要ないので取り除くパラメータに指定している。-->
                <a class="btn btn-m" href="home.php<?php echo appendGetParam(array('s_id')); ?>">出品者とチャットをする &gt;</a>
            </div>
        </section>
        <!--フッター呼び出し-->
        <?php
            require('footer.php');
        ?>
    </body>
</html>
