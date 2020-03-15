<?php
//共通変数、関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「「「「「「「パスワード変更ページ「「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//ログイン認証
require('auth.php');

//==============================================================
//画面処理
//==============================================================

//DBからユーザー情報取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報:'.print_r($userData,true));

//POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST情報:'.print_r($_POST,true));
    
    //変数にユーザー情報を代入 //必要な情報＝＞１古いパスワード、２新しいパスワード、３新しいパスワード（再入力）
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    //見入力チェック
    validRequired($pass_old, 'pass_old');
    validRequired($pass_new, 'pass_new');
    validRequired($pass_new_re, 'pass_new_re');

    //未入力チェックを通過したら
    if(empty($err_msg)){

        /**
         * 古いパスワードは会員登録の時にバリデーションが設置されているため
         * ここでは半角英数字、最大文字数、最小文字数のバリデーションを
         * 設置しなくても「!passsword_verify」に引っかかるのでvalidPass
         * は古いパスワードに対しては使わない。
         */
        
        //新しいパスワードのチェック(半角英数字, 最大文字数, 最小文字数)
        validPass($pass_new,'pass_new');
        
        //古いパスワードとDBのパスワードを照合
        if(!password_verify($pass_old, $userData['password'])){
            //古いパスワードが違います
            $err_msg['pass_old'] = MSG12; 
        }
        
        //新しいパスワードが古いパスワードと同じままかチェック
        if($pass_old === $pass_new){
            //新しく設定するパスワードと現在設定されているパスワードが同じです
            $err_msg['pass_new'] = MSG13;
        }
        
        //パスワードとパスワード再入力があっているかをチェック
        validMatchPass($pass_new, $pass_new_re, 'pass_new_re');

        //一通りのバリデーションを通過したら
        if(empty($err_msg)){

            //DB処理
            try{
                //DBへ接続
                $dbh = dbConnect();
                //SQL実行
                $sql = 'UPDATE users SET password = :pass WHERE id = :id'; 
                //プレースホルダー //TODO:password_hashについて調べる。 user_idがどこで作られたか調べる
                $data= array(':id'=> $_SESSION['user_id'] , ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                //クエリ成功の場合
                if($stmt){


                    $_SESSION['msg-success'] = SUC01; 

                    //メールを送信する
                    //メールを送信
                        $username = ($userData['username']) ? $userData['username'] : '名無し';
                        $from = 'info@sweetsboard.com';
                        $to = $userData['email'];
                        $subject = 'パスワード変更通知｜SWEETSBOARD';
                        //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
                        //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
                        $comment = <<<EOT
                                {$username}　さん
                                パスワードが変更されました。                     
                                ////////////////////////////////////////
                                スイーツボードマーケットカスタマーセンター
                                URL  http://sweetsboard.com/
                                E-mail info@sweetsboard.com
                                ///////////////////////////////////////
EOT;
                    //メール送信関数使用
                    sendMail($from, $to, $subject, $comment);
                    //マイページへ遷移
                    header("Location:mypage.php");
                
                }
            }catch(Exception $e){
                error_log('エラーが発生しました:'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }

}
?>
<!DOCTYPE html>
<html lang="ja">
    <!--ヘッドタグ要求-->
    <?php
        $siteTitle ='パスワード変更画面';
        require('head.php');
    ?>
    <body>
        <!--ヘッダー要求-->
        <?php
            require('header.php');
        ?>
        <!--メイン-->
        <section class="main">
            <h2 class="main-title main-title-passedit">
            パスワード変更画面
            </h2>
            <div class="form-container">
                <form method="post" class="form form-m default-form">
                    <!--フォームタイトル-->
                    <div class="area-msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>
                    <!--古いパスワード-->
                    <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
                        古いパスワード<br>
                        <input type="password" name="pass_old" class="input" value="<?php echo getFormData('pass_old'); ?>">
                    </label>
                    <div class="area-msg"> 
                        <?php echo getErrMsg('pass_old');?> 
                    </div>
                    <!--新しいパスワード-->
                    <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
                        新しいパスワード<br>
                        <input type="password" name="pass_new" class="input" value="<?php echo getFormData('pass_new'); ?>">
                    </label>
                    <div class="area-msg"> 
                        <?php echo getErrMsg('pass_new');?> 
                    </div>
                    <!--新しいパスワード（再入力）-->
                    <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
                        新しいパスワード（再入力）<br>
                        <input type="password" name="pass_new_re" class="input" value="<?php echo getFormData('pass_new_re'); ?>">
                    </label>
                    <div class="area-msg"> 
                        <?php echo getErrMsg('pass_new_re');?> 
                    </div>
                    <!--ログインボタン-->
                    <div class="btn-container">
                        <input type="submit" class="btn btn-s" value="変更する">
                    </div>
                </form>
            </div>
        </section>
        <!--フッター-->
        <?php 
        require('footer.php');
        ?>
    </body>
</html>