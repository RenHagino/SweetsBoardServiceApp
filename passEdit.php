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

    //見入力チェックを通過したら
    if(empty($err_msg)){

        //ValidPass =>最大文字数、最小文字数、半角英数字をまとめて行なっている.=>function.php
        //古いパスワードのチェック 
        validPass($pass_old,'pass_old');
        //新しいパスワードのチェック
        validPass($pass_new,'pass_new');

        //古いパスワードとDBのパスワードを照合
        //もし、同じなら半角英数字や最大文字数チェックは行わなくて良い
        if(!password_verify($pass_old, $userData['password'])){
            $err_msg['pass_old'] = MSG12; //古いパスワードが違います
        }

        //新しいパスワードが古いパスワードと同じままかチェック
        if($pass_old === $pass_new){
            $err_msg['pass_new'] = MSG13;//古いパスワードと同じです
        }

        //パスワードとパスワード再入力があっているかをチェック
        validMatch($pass_new, $pass_new_re, 'pass_new_re');

        //最大文字数、最小文字数、半角英数字チェックは validPassの中で行なっているので
        //ここに直接書く必要はない

        //一通りのバリデーションに通過したら
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
                    //LESSON17で削除。queryPost関数内で行うようにしたから。
                    //debug('クエリ成功です');

                    $_SESSION['msg-success'] = SUC01; //パスワードを変更しました。

                    //メールを送信する
                    //メールを送信
                        $username = ($userData['username']) ? $userData['username'] : '名無し';
                        $from = 'info@webukatu.com';
                        $to = $userData['email'];
                        $subject = 'パスワード変更通知｜WEBUKATUMARKET';
                        //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
                        //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
                        $comment = <<<EOT
                                {$username}　さん
                                パスワードが変更されました。                     
                                ////////////////////////////////////////
                                ウェブカツマーケットカスタマーセンター
                                URL  http://sweetsboard.com/
                                E-mail info@sweetsboard.com
                                ///////////////////////////////////////
EOT;
                    //メール送信関数使用
                    sendMail($from, $to, $subject, $comment);
                    //マイページへ遷移
                    header("Location:mypage.php");
                
                //LESSON17で削除失敗した場合の処理はqueryPost関数内で行うようになったので
                //}else{
                    //debug('クエリ失敗です');
                    //$err_msg['common'] = MSG07;//エラーが発生しました。しばらく〜〜ください
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
            <div class="form-container">
                <form method="post" class="form form-m pass-form">
                    <!--フォームタイトル-->
                    <h2>パスワード変更</h2>
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
                        <input type="submit" class="btn btn-mid" value="変更する">
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