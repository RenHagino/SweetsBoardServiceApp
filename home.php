<!--商品検索機能の処理フロー
    
-->
<?php 
    //共通変数・関数読み込み
    require('function.php');

    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debug('「「「「「「「「「「　トップページ　「「「「「「「「「「');
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debugLogStart();

    //ログイン認証要求
    require('auth.php');

    //現在のページのGETパラメータを取得
    $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; 
    debug('現在の$_GET["currentPageNum"]の中身=>'.print_r($currentPageNum, true));
    
    //カテゴリーのGETパラメータを取得
    $category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : 0;
    debug('現在の$_GET["category"]の中身=>'.print_r($category, true));
    
    //ソートのGETパラメータを取得
    $sort = (!empty($_GET['sort'])) ? $_GET['sort'] : 0;
    debug('現在の$_GET["sort"]の中身=>'.print_r($_GET['sort'],true));

    //パラメータに不正な値が入っているかチェック。入っていたらトップページへ飛ばす（リダイレクト）
    if(!is_int( (int)$currentPageNum )){ //(int)を変数の前につけることで変数の中身をint型にキャストできる。
        error_log('エラー発生。currentPageNumに不正な値が入りました');
        header("Location::home.php");
    }
    //１ページに表示するスイーツの表示件数のデフォルトを設定 
    $listSpan = 12;
    
    //現在の表示レコード先頭を算出 
    //１ページ目の場合、 1 - 1 * listSpan(12) なので0件目から$listSpan(12件)取得(0~11件)
    //２ページ目の場合、 2 - 1 * listSpan(12) なので12件目から$listSpan(12件)取得(12~24件)
    $currentMinNum = (($currentPageNum-1)*$listSpan);
    //DBから商品データを取得 //現在の表示ページの先頭のスイーツの数値を引数に指定
    //$currentMinNum,$category,$sortの中身をクエリパラメータで取得。
    $dbSweetsData = getSweetsList($currentMinNum, $category, $sort, $listSpan); 
    //カテゴリーデータを取得
    $categoryData = getCategoryData();
    //変数をデバッグ
    debug('「「「「「「 ページ関係の 変数をデバッグします「「「「「「「「「「「「「「「');
    debug('現在のページ数のGETパラメータ:'.print_r($currentPageNum,true));
    debug('現在ページのGETソート順:'.print_r($sort,true));
    debug('現在のページのカテゴリーID:'.print_r($category,true));
    debug('現在の表示レコードの最小数:'.print_r($currentMinNum,true));
    debug('DBのスイーツデータの中身(1ページに表示):'.print_r($dbSweetsData,true));
    debug('「「「「「「 ページ関係の変数デバッグ終了「「「「「「「「「「「「「「「');

?>
<!--HTML-->
<html lang="ja">
    <?php
        $siteTitle ='ホームページ';
        require('head.php');
    ?>
    <?php 
        require('header.php');
    ?>
    <body>
        <div class="main-contents_wrapper">
            <!--main-->
            <div class="main home_main">

                <!--検索結果件数-->
                <section class="search-result">
                        <div class="search-result__left">
                            <?php if(!empty($dbSweetsData)){
                                echo '<span class="total-num">'.sanitize($dbSweetsData['total']).'</span>個のスイーツが見つかりました！！！';
                            }else{
                                echo  '<span class="total-num">スイーツが見つかりませんでした</span>';
                            }
                            ?>
                        </div>
                        <!--全データの中、今表示しているのが何件〜何件であるかを示す部分-->
                        <div class="search-result__right"> 
                            <span class="num">
                                <!--ここを$currentMinNum + 1にしないと１ページ目だと０件目から１９件目まで表示になってしまう-->
                                <?php echo (!empty($dbSweetsData['data'])) ? $currentMinNum+1 : 0 ; ?>
                            </span>件から ー 
                            <span class="num">
                                <!--2ページ目の場合は-->
                                <?php echo $currentMinNum+count($dbSweetsData['data']); ?>
                            </span>件を表示しています/
                            <span class="num">
                                <?php echo sanitize($dbSweetsData['total'])?>
                            </span>件中
                        </div>
                    </section>
                <!--サイドバーセクション-->
                <section class="home-sidebar section-left">
                    <div class="home-sidebar__title">
                        <p>絞り込み</p>
                    </div>
                    <form  action="" method="get" class="form home-sidebar__form">
                        <div class="side-contents">
                            <!--カテゴリー選択-->
                            <h1 class="title">カテゴリーで<br>絞り込み</h1>
                            <span class="icn_select"></span>
                            <select name="c_id" class="category">
                                <!--getFormData()でGETデータのc_idが0の場合、-->
                                <option value="0" <?php if(getFormData('c_id', true) == 0){echo 'selected';} ?>>
                                    選択
                                </option>
                                <?php 
                                    foreach($categoryData as $key => $val){
                                ?> 
                                <option value="<?php echo $val['id'] ?>" 
                                        <?php if(getFormData('c_id', true) == $val['id'] ){ echo 'selected';}?>>
                                        <?php echo $val['name']; ?>
                                </option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>
                        <!--表示順（ソート）--> <!--ここは金額が安い順か高い順かだけで実装しているので選択肢が二つしかないのでforeachは使わない-->
                        <div class="side-contents">
                            <h1 class="title">表示順を変更</h1>
                            <select name="sort" class="sort">
                                <!--getFormDataでGETパラメータを取得し、その数値によってechoさせる値を変化させる trueを指定することで$_GETになる-->
                                <option value="0" <?php if(getFormData('sort',true) == 0 ){echo 'selected'; }?> >選択</option>
                                <option value="1"<?php if(getFormData('sort',true) == 1 ){echo 'selected'; }?> >金額が安い順</option>
                                <option value="2" <?php if(getFormData('sort',true) == 2 ){echo 'selected'; }?>>金額が高い順</option>
                            </select>
                        </div>
                        <!--検索ボタン-->
                        <div class="side-contents btn-container">
                            <input type = "submit" value="検索する" class="btn btn-s btn-search">
                        </div>
                    </form>
                </section>

                <!--//////////////////////////////////////////////////
                ----メインセクション---
                ///////////////////////////////////////////////////////-->
                <section class="main-section section-right">
                    <!--スイーツの画像リスト-->
                    <div class="panel-list">
                        <!--foreach始まり。-->
                        <?php
                            foreach($dbSweetsData['data'] as $key => $val): 
                        ?>
                        <!--パネルのどこかをクリックするとクリックしたスイーツの詳細画面(SweetsDetail.com)に飛べるようになっている-->
                        <!--変更前：productDetail.comにスイーツのID(s_id)を繋げ、ページ数(&p=)に$currentPageNumを指定してつなげる-->
                        <!--変更後：appendGetParamが適用できた場合はappendGetParam()に's_id'=.$val['id']としている。
                            appendGetParamが適用できなかった場合、そのまま's_id'=.$val['id']を記述-->
                        <a class="panel" href="SweetsDetail.php?s_id=<?php echo $val['id'].'&p='.$currentPageNum; ?>" > 
                            <!--パネルヘッド : 画像とスイーツの名前を入れている-->
                            <div class="panel-head">
                                <img src="<?php echo sanitize($val['pic1'])?>" alt="<?php echo sanitize($val['name'])?>">
                            </div>
                            <!-- パネルボディ : -->
                            <div class="panel-body">
                                <!--スイーツの名前-->
                                <p class="panel-body__title"><?php echo sanitize($val['name']); ?></p><br>
                                <!--スイーツの値段-->
                                <span class="panel-body__price">
                                    <!-- number_format関数を使って千の位ごとに値段表示にカンマをつける-->
                                    ¥<?php echo sanitize(number_format($val['price'])); ?>
                                <span>
                            </div>
                        </a>
                        <!--foreach終了-->
                        <?php
                            endforeach;
                            ?>
                    </div>
                    <!--====================
                         //ページネーション付与
                    =====================-->
                    <?php 
                        pagenation($currentPageNum, $dbSweetsData['total_page'], $category, $sort); 
                    ?>
                </section>
            </div>
            <!--フッター-->
            <?php
            require('footer.php');
            ?>
        </div>
    </body>
</html>
