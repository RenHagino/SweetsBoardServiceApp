<?php 
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証キー入力ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人が使う画面なので）

//SESSIONに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])){
   header("Location:passRemindSend.php"); //認証キー送信ページへ
}

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST)){

    debug('POST送信があります');
    debug('POST情報:'.print_r($_POST,true));

    //認証キーを変数に入れる //TODO:どこからtokenが来た？
    $auth_key = $_POST['token'];

    //未入力チェック
    validRequired($auth_key,'token');

    //未入力チェックを通過したら
    if(!empty($err_msg)){

        //固定長チェック
        validLength($auth_key,'token');
        //半角英数字チェック
        validHalf($auth_key,'token');

        //固定長チェックと半角チェックを通過したら
        if(!empty($err_msg)){
            //認証キーが発行したものと一致しているか確認
            if($auth_key !== $_SESSION['auth_key']){
                $err_msg['common'] = MSG15; //TODO: $err_msg['token']に変えて動作確認
            }
            //認証キーの期限が切れていないか確認
            if(time() > $_SESSION['auth_key_limit']){
                $err_msg['common'] = MSG16; //TODO: $err_msg['token']に変えて動作確認
            }

            //認証キーの一致と期限のチェックを確認したら
            if(!empty($err_msg)){
                debug('認証を全て通過しました');
                
                //パスワードを生成し、変数に入れる
                $pass = makeRandKey();

                //DB処理
                try{
                    //DB接続
                    $dbh = dbConnect();
                    //SQL
                    $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg =0';
                    //プレースホルダー
                    $data = array(':pass' => password_hash($pass,PASSWORD_DEFAULT), ':email'=>$_SESSION['auth_email']);
                    //クエリ実行
                    $stmt = queryPost($dbh,$sql,$data);
                    //クエリ判定
                    if($stmt){
                        debug('クエリに成功しました');

                        ////////メール準備////////////////////////
                        $from = 'info@webukatu.com';
                        $to = $_SESSION['auth_email'];
                        $subject = '【パスワード再発行完了】｜WEBUKATUMARKET';
                        //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
                        //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
                        $comment = <<<EOT
                        本メールアドレス宛にパスワードの再発行を致しました。
                        下記のURLにて再発行パスワードをご入力頂き、ログインください。

                        ログインページ：http://localhost:8888/webservice_practice07/login.php
                        再発行パスワード：{$pass}
                        ※ログイン後、パスワードのご変更をお願い致します

                        ////////////////////////////////////////
                        ウェブカツマーケットカスタマーセンター
                        URL  http://webukatu.com/
                        E-mail info@webukatu.com
                        ////////////////////////////////////////
EOT;
                        //メール送信関数
                        sendMail($from,$to,$subject,$comment);

                        //セッションを削除
                        session_unset();
                        //成功メッセージ
                        $_SESSION['msg-success'] = SUC03;//メールを送信しました
                        //セッション変数の中身を確認
                        debug('セッション変数の中身'.print_r($_SESSION,true));
                        //ログインページへ遷移
                        header('Location:login.php');

                    //クエリ失敗した場合
                    }else{
                        debug('クエリに失敗しました');
                        $err_msg['common'] = MSG07;
                    }

                //例外処理    
                }catch(Exception $e){
                    error_log('エラー発生'.$e->getMessage() );
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
    <?php
        require('head.php');
    ?>
    <body>
        <!--ヘッダー-->
        <?php
            require('header.php');
        ?>
        <!--サクセスメッセージ-->
        <p id="js-show-msg" style="display:none;" class="msg-slide">
            <?php echo getSessionFlash('msg-success'); ?>
        </p>
        <section class="main">
            <div class="form-container">

                <form action="" method="post" class="form">
                    <p>ご指定のメールアドレスお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力ください。</p>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>
                    <!--認証キー-->
                    <label class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
                            認証キー
                            <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['token'])) echo $err_msg['token']; ?>
                        </div>
                        <!--送信ボタン-->
                        <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="再発行する">
                        </div>
                    </form>
            </div>
        </section>
    </body>
</html>