<?php
    //共通関数読み込み
    require('function.php');
    
    //デバッグ処理
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debug('「「「「「「「「スイーツ登録画面です「「「「「「「「「「');
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debugLogStart();
    
    //ログイン認証
    require('auth.php');

    /// === 画面表示用データを取得 ===////
    // GETデータを格納
    $s_id = (!empty($_GET['s_id'])) ? $_GET['s_id'] : ''; //空でなければ''を選択
    //DBからスイーツのデータを取得 //ログイン中のユーザーIDに基づいて getRegistSweetsの第二引数は取りたいスウィーツのID
    $dbFormData = (!empty($s_id)) ? getRegistSweets( $_SESSION['user_id'], $s_id): '';
    //カテゴリーデータを入手
    $dbCategoryData = getCategoryData();
    //スイーツの新規登録か変種画面かの判断 
    $edit_flg =(!empty($dbFormData)) ? true : false; //DBにデータがあればtrue =編集画面; データがなければfalse = 編集画面
        
    //デバッグ
    debug('スウィーツID:'.$s_id);
    debug('フォーム用DBデータ:'.print_r($dbFormData,true));
    debug('DBカテゴリーデータ:'.print_r($dbCategoryData,true));


    //パラメータ改ざんチェック（GETパラメータはURLにあるのでいじられる可能性がある）
    //ログイン中のユーザーのURLからDBに登録されていないGETパラメーターが検出された場合、
    if(!empty($s_id) && empty($dbFormData)){ 
        //マイページへ飛ばす
        debug('GETパラメータの商品IDが違います。マイページへ遷移します');
        header("Location:mypage.php");
    } 

    //POST送信時処理
    if(!empty($_POST)){
        debug('POST送信がありました');
        debug('POST情報:'.print_r($_POST,true));
        //過去に登録した画像があればそれが$_FILESという変数の中に入ってくる
        debug('FILES情報:'.print_r($_FILES,true));
    

        //変数にユーザー情報を代入
        $category_id = $_POST['category_id'];
        $name = $_POST['name'];
        $store_name = $_POST['store_name'];
        /*TODO: $priceに入れる値はどうする？
            入力結果が0もしくは空文字の場合は0を入れる
            $price = (!empty($_POST['price']))? $_POST['price'] : 0;*/
        $price = $_POST['price'];
        $comment = $_POST['comment'];
        
        //画像をアップロードし、パスを格納
        //画像が空の場合、uploadImgを使う。画像がPOSTされて$_FILESの中身がある場合、
        //画像をPOSTしていない（過去に登録した）かつ、DBに画像のパスがある場合にはDBのパスを入れる、そうでない場合にはPOSTされた$pic1をそのまま入れる
        $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1'): '';
        $pic1 = (empty($pic1) && !empty($dbFormData['pic1']) ) ? $dbFormData['pic1']: $pic1; 
        
        $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2'): '';
        $pic2 = (empty($pic2) && !empty($dbFormData['pic2']) ) ? $dbFormData['pic2']: $pic2;
        $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'],'pic3'): '';
        $pic3 = (empty($pic3) && !empty($dbFormData['pic3']) ) ? $dbFormData['pic3']: $pic3;
        
        //デバッグ
        debug('$pic1の中身:'.print_r($pic1,true));
        debug('$pic1の$_FILESのpic1のname(多次元配列）:'.$_FILES['pic1']['name']);
        debug('$pic2の中身:'.print_r($pic2,true));
        debug('$pic2の$_FILESのpic2のname(多次元配列）:'.$_FILES['pic2']['name']);
        debug('$pic3の中身:'.print_r($pic3,true));
        debug('$pic3の$_FILESのpic3のname(多次元配列）:'.$_FILES['pic3']['name']);


        //更新の場合はDBと入力情報が異なる場合にバリデーションを行う
        // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
        if(empty($dbFormData)){ //新規登録の場合
            //デバッグ
            debug('新規登録のバリデーションを行います');
            //スイーツのカテゴリー名入力チェック
            validRequired($category_id, 'category_id');
            //スイーツ名の未入力と最大文字数チェック
            validRequired($name, 'name');
            validMaxName($name, 'name');
            //未入力チェック
            validRequired($store_name,'store_name');
            //最大文字数チェック
            validMaxName($store_name,'store_name');
            
            //金額の未入力,最大文字数、半角数字のバリデーションを行う
            validRequired($price, 'price');
            validMaxPrice($price, 'price');
            validNumber($price, 'price');

            //コメントの未入力、最大文字数チェック
            validRequired($comment,'comment');
            validMaxComment($comment, 'comment' );
            

            //画像１を必須にする
            validRequiredPic($pic1, 'pic1');
        
        //============================================================================
        //$dbFormDataがある場合getRegistSweetsに$s_id, $_SESSION['user_id']を
        //を渡し、該当するスイーツがある場合、更新画面としてバリデーションチェックを行う       
        //============================================================================
        }else{

            //デバッグ
            debug('===============================');
            debug('更新画面のバリデーsションを行います');
            debug('===============================');            

            //カテゴリーの必須チェックと行う
            if($dbFormData['category_id'] !== $category_id){ 
                validRequired($category_id, 'category_id');
            }
            //スイーツの名前の必須チェックと最大文字数チェック
            if($dbFormData['name'] !== $name){ 
                validRequired($name, 'name');
                validMaxName($name, 'name');
            }
            //店名の必須チェックと最大文字数チェック
            if($dbFormData['store_name'] !== $store_name){ 
                validRequired($store_name, 'store_name');
                validMaxName($store_name, 'store_name');
            }
            //詳細コメントの未入力チェックと最大文字数チェックを行う
            if($dbFormData['comment'] !== $comment){
                validRequired($comment, 'comment');
                validMaxComment($comment, 'comment');
            }
            //値段の未入力チェックと最大文字数チェックと半角数字チェックを行うを行う
            if($dbFormData['price'] !== $price){ //前回まではキャストしていたが、ゆるい判定でもいい
                validRequired($price, 'price');
                validMaxPrice($price, 'price');
                validNumber($price, 'price');
            }
            //画像１の必須チェックを行う
            validRequiredPic($pic1, 'pic1');
        }


        //============================================================
        //バリデーションを通過した場合 $edit_flg($dbFormDataの有無でtrue,falseが切り替わる)
        //のtrue, falseで走らせるSQLを切り替える。
        //============================================================
        if(empty($err_msg)){
            debug('バリデーションを全て通過しました');
            //DB接続
            try{
                //DB接続
                $dbh = dbConnect();
                //SQLは$edit_flgのtrue or falseで新規登録、更新の場合で場合分けする
                //SQL１ 更新の場合
                if($edit_flg){
                    debug('DB更新です');
                    $sql = 'UPDATE sweets SET name = :name, category_id = :category_id, store_name = :store_name, price = :price, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :s_id';
                    $data = array(':name'=>$name,':category_id'=>$category_id, ':store_name'=>$store_name, ':price'=>$price, ':comment'=>$comment, ':pic1'=>$pic1, ':pic2'=>$pic2, ':pic3'=>$pic3, ':u_id'=>$_SESSION['user_id'], ':s_id'=>$s_id );
                //SQL２ 新規登録の場合
                }else{
                    debug('新規登録です');
                    $sql = 'INSERT into sweets (name, category_id, store_name, price, comment, pic1, pic2, pic3, user_id, create_date) values (:name, :category_id, :store_name, :price, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
                    $data = array(':name'=>$name, ':category_id'=>$category_id, ':store_name'=>$store_name, ':price'=>$price, ':comment'=>$comment, ':pic1'=>$pic1, ':pic2'=>$pic2, ':pic3'=>$pic3, ':u_id'=>$_SESSION['user_id'], ':date'=>date('Y-m-d H:i:s'));
                }
                //SQLの内容をデバッグ
                debug('SQL:'.$sql);
                debug('流し込みデータ:'.print_r($data,true));

                //クエリ実行
                $stmt = queryPost($dbh,$sql,$data);

                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg-success'] = SUC03; 
                    debug('マイページへ遷移します');
                    header("Location:mypage.php"); 
                }

            }catch(Exception $e){
                error_log('エラー発生:'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
    //デバッグ
    debug('＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
    debug('画面表示処理終了');
    debug('＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');

?>  

<!DOCTYPE html>
<html lang="ja">
    <?php
    $siteTitle = (!$edit_flg)? 'スイーツ登録画面':'スイーツ編集画面';
    require('head.php');
    ?>
    <body>
        <?php
        require('header.php');
        ?>
        <section class="main">
            <!--フォームのタイトル： エディットフラグで新規紹介か編集画面かを切り替える-->
            <h2 class="main-title main-title-default">
                <?php echo (!$edit_flg) ? 'スイーツを紹介する' : 'スイーツを編集する';?>
            </h2>
            <div class="form-container">
                <!--enctypeを追加したことに注意-->
                <form class="form form-m regist-form" action ="" method="post" enctype="multipart/form-data">
                    
                    <!--共通エリアメッセージ-->
                    <div class="area-msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>

                    <!--カテゴリー TODO:仕組みを要復習。ソースコードをいじって動作を確認してみる-->
                    <label class="label <?php if(!empty($err_msg['category_id'])) echo 'err'?>" >
                        カテゴリー<span class="label-require">必須</span><br>
                        <!--セレクトボックスで実装-->
                        <select class="category_select" name="category_id" id="">
                            <option value="" <?php if(getFormData('category_id')==""){echo 'selected';} ?>>
                            選択してください
                            </option>
                            <?php
                                foreach($dbCategoryData as $key => $val){
                            ?>
                                <!--foreachで取得した$valのなかにid,name,delete_flg, create_date, update_dateが入っている-->
                                <option value="<?php echo $val['id'];?>" <?php if(getFormData('category_id') == $val['id']){echo 'selected';}?>>
                                    <?php echo $val['name'];?>
                                </option>
                            <?php 
                                }
                            ?>
                        </select>
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['category_id'])) echo $err_msg['category_id'] ?>
                    </div><br>

                    <!--商品名-->
                    <label class="label <?php if(!empty($err_msg['name'])) echo 'err' ?>">
                    商品名<span class="label-require">必須</span><br>
                    <input type="text" class="input" name="name" value="<?php echo getFormData('name'); ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
                    </div><br>


                    <!--店名-->
                    <label class="label <?php if(!empty($err_msg['store_name'])) echo 'err' ?>">
                    店名<span class="label-require">必須</span><br>
                    <input type="text" class="input" name="store_name" value="<?php echo getFormData('store_name'); ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['store_name'])) echo $err_msg['store_name']; ?>
                    </div><br>

                    <!--金額-->
                    <label class="label <?php if(!empty($err_msg['price'])) echo 'err'; ?>">
                    金額<span class="label-require">必須</span>
                    <div class="form-group">
                    <input type="text" class="input" name="price" style="width: 200px;" placeholder="金額を入力して下さい" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : ''; ?>"><span class="option">円</span>
                    </div>
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['price'])) echo $err_msg['price'];?>
                    </div><br>

                    <!--詳細文（テキストエリア） getFormDataを使う場所に注意。value=""は使えない　-->
                    <label  class="label <?php if(!empty($err_msg['comment'])) echo 'err'?>">
                    スイーツの詳細<span class="label-require">必須</span><br>
                    <!--テキストエリアタグを改行すると空文字が入るので注意-->
                    <textarea class="input js-count" name="comment" cols="50" rows="3"><?php echo getFormData('comment'); ?></textarea>
                    </label> 
                    <!--TODO: jsで処理するので動作確認-->
                    <p class="count-text">
                        <span class="js-count-view">0</span>/100文字
                    </p>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
                    </div><br>

                    <!--画像コンテナー-->
                    <div style="overflow:hidden;">
                        <!--画像1-->
                        <div class="imgDrop-container container-sweets">
                            画像1<br>
                            <label class="label area-drop area-drop-sweets <?php if(!empty($err_msg['pic1'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic1" class="input-file input-file-sweets">
                                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img prev-img-sweets">
                                画像１をクリックして選択<br>
                            </label>
                        </div>
                        <!--画像２-->
                        <div class="imgDrop-container container-sweets">
                            画像2<br>
                            <label class="label area-drop area-drop-sweets <?php if(!empty($err_msg['pic2'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic2" class="input-file input-file-sweets">
                                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img prev-img-sweets">
                                画像2をクリックして選択
                            </label>
                            <div class="area-msg">
                                <?php 
                                if(!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                                ?>
                            </div>
                        </div>
                        <!--画像３-->
                        <div class="imgDrop-container container-sweets">
                            画像3<br>
                            <label class="label area-drop area-drop-sweets <?php if(!empty($err_msg['pic3'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic3" class="input-file input-file-sweets">
                                <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img prev-img-sweets">
                                画像3をクリックして選択
                            </label>
                            <div class="area-msg">
                                <?php 
                                if(!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                                ?>
                            </div>
                        </div>
                    </div>
                    <!--画像１のエラーメッセージ表示エリア-->
                    <div class="area-msg">
                            <?php 
                            if(!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                            ?>
                    </div>
                    
                    <!--送信ボタン-->
                    <div class="btn-container">
                        <input type="submit" class="btn btn-m btn-regist" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>" >
                    </div>
                    
                </form>
            </div>
        </section>
        <!--フッター呼び出し-->
        <?php 
        require('footer.php');
        ?>
    </body>
</html>

<!--
    このページの処理フロー
    １GETパラメーターがあるかチェック
    ２フォームに表示するDBデータを取得
    ３POSTされているかをチェック
    ４画像がPOSTされていればアップロード
    ５バリデーションチェック
    ６DB新規登録 or 更新
    ７マイページへ遷移
-->
