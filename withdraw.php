<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「 退会ページ「「「「「「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');


//============= 画面処理 =====================================//

//post送信されていた場合,
if(!empty($_POST)){
    debug('post送信があります');

    //例外処理　
    try{
        //DBへ接続
        $dbh = dbConnect();

        //SQL TODO:なぜuser_idではなくus_idにした？
        // idはusersテーブルのもの。 user_idは他のテーブルのユーザーID判別のためのもの
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
        $sql2 = 'UPDATE sweets SET delete_flg = 1 WHERE user_id = :us_id';
        $sql3 = 'UPDATE sweets SET delete_flg = 1 WHERE user_id =:us_id';
        //データ流し込み
        $data = array(':us_id' => $_SESSION['user_id']);
        //クエリ実行 //SQLが３つあるため一回ずつ実行する必要がある？
        $stmt1 = queryPost($dbh, $sql1, $data);
        $stmt2 = queryPost($dbh, $sql2, $data);
        $stmt3 = queryPost($dbh, $sql3, $data);

        //クエリ成功の場合、（ここの条件は最悪usersテーブルのみ削除成功していれば良いので$stmt1のみにした
        if($stmt1){
            //セッション削除
            session_destroy();
            //削除できたか確認
            debug('セッション変数の中身:'.print_r($_SESSION,true));
            debug('トップページへ遷移します');
            //ホームページへ
            header("Location:home.php");
        }else{
            debug('クエリが失敗しました');
            $err_msg['common'] = MSG07;
        }

    //例外：
    }catch(exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $error_log['common'] = MSG07;
    }
}//post送信されていた場合

debug('画面表示終了');
?>
<!DOCTYPE html >
<html lang="ja">
    <!--ヘッドタグ呼び出し-->
    <?php
        $siteTitle ='退会する';
        require('head.php');
    ?>
    <!--ヘッダー-->
    <?php
        require('header.php');
    ?>
    <body>
        <!--ヘッダー呼び出し-->
        <section class="main">
            <!--サインアップフォームのタイトル-->
            <h2 class="main-title main-title__withdraw">
                退会フォーム
            </h2>
            <div class="form-container">
                <form action="" method="post" class="form form-m withdraw-form"> 
                    <h2>退会しますか？</h2>
                    <div class="area-msg">
                        <?php
                            if(!empty($err_msg['common'])) echo $err_msg['common'];
                        ?>
                    </div>
                    <div class="btn-container">
                        <!--$_POSTの中には name="ここに指定したもの"が入るので指定しないとエラーとなる-->
                        <input type="submit" name="submit" class="btn btn-s btn-withdraw" value="退会する">
                    </div>
                    <div class="btn-container">
                        <a href="mypage.php" class="btn btn-s btn-back">&lt; マイページに戻る</a>
                    <div>
                </form>
            </div>
        </section>
    </body>
    <?php
        require('footer.php');
    ?>
</html>