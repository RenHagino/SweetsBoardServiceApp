<!--
    
-->
<?php
//共通関数呼び出し
require('function.php');
//デバッグスタート
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「ログインページです「「「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証呼び出し
//debug('ログイン認証を開始します');
//require('auth.php');

if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST情報:'.print_r($_POST,true));

    //変数にPOST情報を代入
    $email = $_POST['email'];

    //未入力チェック
    validRequired($email,'email');

    //未入力チェック通過後
    if(empty($err_msg)){
        debug('未入力チェック通過');

        //形式チェック
        validEmail($email,'email');

        //最大文字数チェック
        validMaxEmail($email,'email');

        //形式チェックと最大文字数チェック通過後
        if(!empty($err_msg)){
            debug('バリデーションを全て通過しました');
            
            //DB処理に突入
            try{
                //DBに接続
                $dbh = dbConnect();
                //SQL
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                //プレースホルダー
                $data = array(':email' => $email);
                //クエリー
                $stmt = queryPost($dbh,$sql,$data);
                //クエリ結果の値を取得 //TODO:クエリ結果を取得するときとしない時の差は？
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                //EmailがDBに登録されてる場合 クエリが成功し、クエリ結果の値の最初をarray_shiftで取り出す
                if($stmt && array_shift($result)){
                    debug('クエリ成功です');
                    $_SESSION['msg-success'] = SUC03;

                    //認証キーを作成 関数を使う
                    $auth_key = makeRandKey(); 
                    
                    //メールを送信
                    $from = 'SweetsBoard.com';
                    $to = $email; //$_POST['email'];
                    $subject = 'パスワード再発行認証:SweetsBoard'; 
                    $comment = <<<EOT
                        本メールアドレス宛にパスワード再発行のご依頼がありました。
                        下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

                        パスワード再発行認証キー入力ページ：http://localhost:8888/portfolio3/passRemindRecieve.php
                        認証キー：{$auth_key}
                        ※認証キーの有効期限は30分となります

                        認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
                        http://localhost:8888/webservice_practice07/passRemindSend.php

                        ////////////////////////////////////////
                        ウェブカツマーケットカスタマーセンター
                        URL  http://webukatu.com/
                        E-mail info@webukatu.com
                            ////////////////////////////////////////
EOT;
                    //実際にメールを送信。
                    sendMail($from, $to, $subject, $comment);
                    //メールが送り終わったら認証に必要な情報をセッションに格納
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;//$_POST['email'];
                    $_SESSION['auth_key_limit'] = time()+(60*30); //現在時刻+30分（60秒*30）を起源に設定
                    //セッション変数の中身を確認
                    debug('セッション変数の中身'.print_r($_SESSION,true));

                }else{
                    debug('クエリに失敗したか、DBに存在しないEmailが入力されました');
                    $err_msg['common'] = MSG07;
                }
            }catch(Exception $e){
                error_log('エラー発生:'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }

    }
}
?>
<!DOCTYPE html>
<html lang="ja">
    <?php
        $siteTitle ='パスワード再発行ページ';
        require('head.php');
    ?>
    <body>
        <?php
            require('header.php');
        ?>
        <section class="main">
            <div class="form-container">
                <form action="" method="post" class="form form-m">
                    <!--common-->
                    <div class="area-msg">
                        <?php
                            if(!empty($err_msg['common'])) echo $err_msg['common'];
                        ?>
                    </div>
                    <!--Email-->
                    <label class="label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                        Email:
                        <input type="text" class="input" name="email" value="<?php echo getFormData('email'); ?>" >
                    </label>
                    <div class="area-msg">
                        <?php 
                            if(!empty($err_msg['email'])) echo $err_msg['email'];
                        ?>
                    </div>
                    <!--送信ボタン-->
                    <div class="btn-container">
                        <input type="submit" style="float:none;" class="btn btn-mid" value="送信する">
                    </div>
                    <!--マイページへ戻る-->
                    <a href="mypage.php">
                        <h3 class="mypage_back">&lt;マイページへ戻る</h3>
                    </a>
                </form>
            </div>
        </section>
        <!--フッター-->
        <?php
            require('footer.php');
        ?>
    </body>
</html>