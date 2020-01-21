<?php
    //共通関数呼び出し
    require('function.php');


    //ここからが実際に走るphpの処理//
    if(!empty($_POST)){
        //POST送信があっ
        debug('POST送信があります。');

        //変数にユーザー情報を代入
        $email = $_POST['email'];
        $u_name = $_POST['u_name'];
        $region = $_POST['region'];
        $pass = $_POST['pass'];
        $pass_re = $_POST['pass_re'];
        //未入力チェック
        validRequired($email, 'email');
        validRequired($u_name, 'u_name');
        validRequired($region, 'region');
        validRequired($pass, 'pass');
        validRequired($pass_re, 'pass_re');



        //ここまでチェックが済んだら以下のチェックに進む
        //未入力チェックが済んでいないのに形式チェックをすると余計なDB接続が増える
        if(empty($err_msg)){
            debug('バリデーション１段階目通過です');

            //Email形式チェック、最大文字数、重複チェック
            validEmail($email,'email');
            //なくてもDBで設定すれば良いのでは？
            validMaxEmail($email,'email'); 
            //メールアドレス重複チェック
            validEmailDup($email);

            //ユーザーネーム最大文字数チェック
            validMaxName($u_name,'u_name');

            //都道府県チェック
            validMaxName($region, 'region');

            //パスワード最大小文字数、半角チェック
            validMinLen($pass,'pass');
            validMaxPass($pass,'pass');
            validHalf($pass,'pass');
            
            //パスワード再入力チェック
            validMinLen($pass_re,'pass_re');
            validMaxPass($pass_re,'pass_re');

            //ここまでチェックが済んだら以下のチェックに進む
            if(empty($err_msg)){
                debug('バリデーション２段階目通過です');
                
                //パスワードとパスワード再入力があっているかをチェック
                validMatch($pass, $pass_re, 'pass_re');

                //ここまでチェックが済んだらDBに接続してデータを取り出す
                if(empty($err_msg)){
                    debug('バリデーション３段階目通過です');

                    //例外処理つきの処理
                    try{
                        // DBへ接続
                        $dbh = dbConnect();
                        // SQL文作成
                        $sql = 'INSERT INTO users (email,username,region,password,login_time,create_date) VALUES(:email,:username,:region,:pass,:login_time,:create_date)';
                        //プレースホルダー
                        $data = array(
                            ':email' => $email, 
                            ':username'=>$u_name,
                            ':region'=>$region,
                            ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                            ':login_time' => date('Y-m-d H:i:s'),
                            ':create_date' => date('Y-m-d H:i:s')
                        );
                        // クエリ実行
                        $stmt = queryPost($dbh, $sql, $data);

                        // クエリ成功の場合
                        if($stmt){
                            //ログイン有効期限（デフォルトを１時間とする）
                            $sesLimit = 60*60;
                            // 最終ログイン日時を現在日時に
                            $_SESSION['login_date'] = time();
                            $_SESSION['login_limit'] = $sesLimit;
                            $_SESSION['user_id'] = $dbh->lastInsertId();
                            // ユーザーIDを格納 =>DB最後に格納されたIDをセッションに当てはめる？
                            //user_idのカラムにAuto Incrementをしないと他のIDを取ってくる可能性がある。
                            //$dbhのdbconnect()の中に入っているPDOオブジェクトの所有する lastInsertIdというメソッドを使う。
                            debug('セッション変数の中身：'.print_r($_SESSION,true));
                            //マイページへ遷移
                            header("Location:mypage.php"); 

                        }
                        
                        }catch (Exception $e) {
                            error_log('エラー発生:' . $e->getMessage());
                            $err_msg['common'] = MSG07;
                        }
                    }
                }
            }
        }
?>
<!--phpの処理おわり-->
<!--ここからHTML-->
<!DOCTYPE html>
<html lang="ja">

    <!--ヘッドタグ呼び出し-->
    <?php
    $siteTitle = 'サインアップ';
    require('head.php');
    ?>
    <!--ボディ-->
    <body>
        <!--ヘッダー呼び出し-->
        <?php
        require('header.php');
        ?>
        
        <!--メイン-->
        <section class="section main signup-form">
            <!--サインアップフォームのタイトル-->
            <h2 class="main-title main-title__signup">
                ユーザー登録
            </h2>
            <!--フォームコンテナー-->
            <div class="form-container">
                <!--フォーム-->
                <form method="post" class="form form-m signup-form">
                    <!-- area-msg -->
                    <div class="area-msg">
                         <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>
                    <!--メールアドレス-->
                     <div class="area-msg">
                         <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                    </div>
                     <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>" >
                         Email<br>
                         <input type="text" name="email"  class="input" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>"><br>
                     </label>
                    <!--ユーザーネーム-->
                        <div class="area-msg">
                            <?php if(!empty($err_msg['u_name'])) echo $err_msg['u_name'];?>
                        </div>
                        <label class="<?php if(!empty($err_msg['u_name'])) echo 'err'?>">
                            ユーザーネーム<br>
                            <input type="text" name="u_name" class="input" value="<?php if(!empty($_POST['u_name'])) echo $_POST['u_name']; ?>"><br>
                        </label>
                    <!--出身地-->
                        <div class="area-msg">
                            <?php if(!empty($err_msg['region'])) echo $err_msg['region'];?><br>
                        </div>
                        <label class="<?php if(!empty($err_msg['region'])) echo 'err' ?>">
                            都道府県<br>
                            <input type="text" name="region" class="input" value="<?php if(!empty($_POST['region'])) echo $_POST['region']; ?>"><br>
                        </label>
                    <!--パスワード入力-->
                        <div class="area-msg">
                            <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['pass'])) echo 'err'?>">
                            パスワード<br>
                            <input type="password" name="pass" class="input"  value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>"><br>
                        </label>
                    <!--パスワード再入力-->
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];?>
                    </div>
                    <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
                        パスワード（再入力）<br>
                        <input type="password" name="pass_re" class="input" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>"><br>
                    </label>
                    <!--送信ボタン-->
                    <div class="btn-container">
                        <input type="submit" class="btn btn-s btn-signup" value="登録する"> <!--TODO:クラス設計見直し-->
                    </div>
                </form>
            </div>
        </section>
        <!--jquery読み込み-->
        <script
            src="https://code.jquery.com/jquery-3.4.1.js"
            integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
            crossorigin="anonymous">
        </script>
        <!--フッター呼び出し-->
        <?php
        require('footer.php');
        ?>
    </body>
</html>


