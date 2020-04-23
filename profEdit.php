<!--プロフィール編集機能の処理フロー
1:DBからユーザー情報取得
2:POSTされているかチェック
3:DB情報とPOSTされた情報を比べて違いがあればバリデーションチェック
4:DB接続
5:レコード更新
6:マイページへ遷移
-->
<?php
    //共通関数読み込み
    require('function.php');

    //デバッグ
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debug('「「「「「「「「プロフィール編集画面です「「「「「「「「「「「');
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debugLogStart();
    
    //ログイン認証
    require('auth.php');

    //DBのusersテーブルからユーザー情報を取得 //現在ログインいしているユーザーのID（$_SESSION['user_id']）
    $dbFormData = getUser($_SESSION['user_id']);
    //DBから取得したユーザー情報をデバッグ
    debug('取得したユーザー情報：'.print_r($dbFormData,true));

    //POST送信されていた場合、
        if(!empty($_POST)){
        
        //POSTされている情報を確認
        debug('POST送信があります');
        debug('POST情報:'.print_r($_POST,true));
        debug('FILE情報:'.print_r($_FILES,true)); 

        //変数にユーザー情報を代入 
        $username = $_POST['username'];
        $region = $_POST['region'];
        $email =$_POST['email'];
        
        //$_FILES変数のpicmのname(連想配列)に何か値があればuploadImg関数にpicの情報を渡してアップロードする(key=pic)
        $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic'): '';
        //現在$picに何も入っていなくて、DBに画像があった場合はそれを表示する
        $pic = ( empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic']: $pic;


        //========================================================
        // DBの情報と入力情報（POST送信された情報）が違う場合にバリデーションを行う
        //========================================================

        //ユーザー名バリデーション
        if($dbFormData['username'] !== $username){
            //必須チェック
            validRequired($username, 'username');
            if(empty($err_msg['username'])){    
                validMaxName($username,'username');
            }
        }

        //都道府県バリデーション
        if($dbFormData['region']!== $region){
            //最大文字数チェック
            validMaxName($region,'region');
        }

        //Emailバリデーション
        if($dbFormData['email'] !== $email){
            //必須チェック
            validRequired($email,'email');
            //必須チェックを通過した場合
            if(empty($err_msg['email'])){
                //最大文字数,Email形式チェック
                validMaxEmail($email,'email');
                validEmail($email,'email');
                //上記三つのチェックを通過した場合、DBに接続し、重複チェックを行う
                if(empty($err_msg['email'])){
                    //重複チェック（変更しようとしたアドレスが他のユーザーが使っている可能性）
                    validEmailDup($email,'email');
                }
            }
        } 
        

        //バリデーション通過後
        if(empty($err_msg)){
            debug('バリデーション全て通過です');

            try{
                //DBに接続
                $dbh = dbConnect();
                //SQL
                $sql = 'UPDATE users SET username = :u_name, region =:region, email=:email, pic = :pic WHERE id = :u_id';
                //データ流し込み //最後の$dbFormDataがどこから来た？=> Line22,23付近に注目。現在ログイン中のユーザーのテーブルにアクセスしている。
                $data = array(':u_name'=>$username,  ':region'=>$region, ':email'=>$email, ':pic'=>$pic, ':u_id'=>$dbFormData['id']);

                //クエリ実行
                $stmt = queryPost($dbh,$sql,$data);
    
                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg-success'] = SUC02;
                    debug('マイページに遷移します');
                    header("Location:mypage.php");
                }

            }catch( Exception $e ){
                error_log('エラー発生'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
            
        }

    }//POST送信があった場合

?>
<!DOCTYPE html>
<html>
    <?php
    $siteTitle ='プロフィール編集画面';
    require('head.php');
    ?>
    <body>
        <?php
        require('header.php');
        ?>
        <!--メインセクション-->
        <section class="main ">
            <!--サイドバー呼び出し-->
            <section class="sidebar  prof-sidebar section-left">
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
            
            <!--プロフィールリスト-->
            <section class="list prof-list section-right">
                <h2 class="list__title">
                    プロフィール編集
                </h2>
                <div class="form-container">
                    <!-- フォームタグにenctypeを追加していることに注意 これをつけないとフォームから添付ファイルを送れないので画像を取り扱えない-->
                    <form class="form form-m prof-form" method="post" enctype="multipart/form-data">
                        <div class="area-msg">
                            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                        </div>
                        <!--名前-->
                        <label class="label <?php if(!empty($err_msg['username'])) echo 'err'?>" >
                            名前(必須):<br> 
                            <input class="input" type="text" name="username" value="<?php echo getFormData('username')?>" >
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?>
                        </div>
                        <!--出身地-->
                        <label class="label <?php if(!empty($err_msg['region'])) echo 'err' ?>">
                            住んでいる場所:<br> <!--value属性にDBから取ってきた情報を指定-->
                            <input class="input" type="text" name="region"  value="<?php echo getFormData('region');?>">
                        </label>
                        <div class="area-msg">
                            <?php  if(!empty($err_msg['region'])) echo $err_msg['region']; ?>
                        </div>
                        <!--Email-->
                        <label class="label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                            Email(必須):<br>
                            <input class="input" type="text" name="email" value="<?php echo getFormData('email'); ?>"> 
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                        </div>

                        <!--プロフィールタイトル-->
                        <p class="prof-title">プロフィール画像をクリックして設定できます</p>
                        <!--プロフィール画像-->
                        <div class="imgDrop-container container-prof">
                            <label class="label area-drop area-drop-prof <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic" class="input-file input-file-prof">
                                <img src="<?php echo getFormData('pic') ?>" alt="" class="prev-img prev-img-prof" >
                                クリックして選択
                            </label>
                            <div class="area-msg">
                                <?php
                                    if(!empty($err_msg['pic'])) echo $err_msg['pic'];
                                ?>
                            </div>
                        </div>

                        <!--送信ボタン-->
                        <div class="btn-container">
                            <input type="submit" class="btn btn-s btn-changeprof" value="設定する">
                        </div>
                    </form>
                </div>
            </section>


        </section>
        <!--フッターよびだし-->
        <?php require('footer.php');?>
    </body>
</html>