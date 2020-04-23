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
    

    //===ログイン画面処理 =====================
    if(!empty($_POST)){
        debug('post送信があります');

        //変数にユーザー情報代入
        $email = $_POST['email']; 
        $pass = $_POST['pass'];
        $pass_save = (!empty($_POST['pass_save']))? true : false; //空でなければtrue

        //emeilの形式チ、最大文字数チェック
        validEmail($email, 'email');
        validMaxEmail($email, 'email');

        //パスワードの半角英数字、最大文字数、最小文字数をチェック
        validHalf($pass, 'pass');
        validMaxPass($pass,'pass');
        validMinPass($pass,'pass');

        //未入力チェック
        validRequired($email,'email');
        validRequired($pass, 'pass');

        //通過したら
        if(empty($err_msg)){
            //デバッグ
            debug('login.phpのバリデーションOKです');

            //DB接続処理へ入っていく。
            try{
                //DBへ接続
                //各種準備。（接続、sql、プレースホルダー）
                $dbh = dbConnect();
                //退会したユーザーのアドレスを拾って退会しているのにログインできるようにしないため。
                $sql = 'SELECT password, id  FROM users WHERE email = :email  AND delete_flg = 0 ';
                $data = array(':email' => $email);
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                //クエリ実行の値を取得
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                //デバッグでクエリ実行によってDBから取得した中身を確認
                debug('クエリ結果の中身:'.print_r($result, true));


                //パスワード照合 
                //array_shiftはsqlで取得した最初の値を取り出すので SELECT id, passwordの順だとidを取り出してしまう
                //password_verifyはハッシュ化したパスワード（DBのもの）とハッシュ化される前のもの（このページで入力）を比べるために使う。
                //パスワードが一致したら
                if(!empty($result) && password_verify($pass, array_shift($result))){
                    debug('パスワードが一致しました');
                    //パスワードが一致してログインしたら
                    
                    //ログイン有効期限を１時間にリセット設定
                    $seeLimit = 60 * 60 ;
                    
                    //セッションに入っている最終ログイン日時をリセット設定
                    $_SESSION['login_date'] = time();
                    
                    ////ログイン保持にチェックがある場合////
                    if($pass_save){
                        debug('ログイン保持にチェックがあります');
                        //ログイン有効期限を30日にしてセット
                        $_SESSION['login_limit'] = $seeLimit * 24 * 30; 
                    }else{
                        debug('ログイン保持にチェックはありません');
                        //ログインの有効期限を１時間後にセット
                        $_SESSION['login_limit'] = $seeLimit;
                    }
                    
                    //ユーザーIDを格納 //セッションのuser_idにはsqlで得た結果のid = ログイン主のidを入れる
                    $_SESSION['user_id'] = $result['id']; 
                    
                    //セッション変数の中身を確認
                    debug('セッション変数の中身:'.print_r($_SESSION, true));//trueの場合、情報を表示する代わりに情報を返す。false(初期値)の場合、情報を表示する
                    debug('マイページへ遷移します。');
                    header("Location:mypage.php");

                //パスワードが一致しなかったら
                }else{
                    debug('パスワードがアンマッチです');
                    $err_msg['pass'] = MSG09;
                }

            }catch(Exception $e){
                error_log('エラー発生:'. $e->getMessage()); //error_logとdebug間違えないように
                $err_msg['common'] = MSG07;
                }
        }
    }

    debug('login.php ===========画面表示処理終了==========================');
?> 

<!DOCTYPE html>
<html lang="ja">
    <!--ヘッドタグ呼び出し-->
    <?php 
        $siteTitle = 'ログイン画面';
        require('head.php');
    ?>
    <!--ボディ-->
    <body>
        <!--ヘッダー呼び出し-->
        <?php 
        require('header.php');
        ?>
        <!--SUUCESSメッセージ -->
        <p class="js-show-msg show-slide">
            <?php echo getSessionFlash('msg-success');?>
        </p>
        <section class="main">
            <h2 class="main-title main-title-yellow">ログイン画面</h2>
            <div class="form-container">
                <form method="post" class="form form-m default-form">
                    <div class="area-msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>
                    <!--メールアドレス--> <!--なぜ 1,'err'を使う？どこから？ labelのクラスに$err_msgを入れる意味-->
                    <label class="label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                        Email<br>
                        <input type="text" name="email" class="input" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['email'])) echo $err_msg['email']?>
                    </div><br>
                    <!--パスワード-->
                    <label class="label <?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
                        パスワード<br>
                        <input type="password" name="pass" class="input" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']?>
                    </div><br>
                    <!--次回ログイン省略-->
                    <label class="label">
                        <input type="checkbox" name="pass_save" class="checkbox">次回ログインを省略する
                    </label><br>
                    <!--ログインボタン-->
                    <div class="btn-container">
                        <input type="submit" class="btn btn-s btn-login" value="ログイン">
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