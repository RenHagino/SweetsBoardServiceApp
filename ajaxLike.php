<?php 
    //======================================================================
    //=========================     Ajax通信の処理      ===================
    //======================================================================

    //共通関数読み込み
    require('function.php');

    //デバッグ
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    debug('「「「「「「「「「「「ajax.phpの処理に入ります「「「「「「「「「「');
    debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

    //スイーツのIDやユーザーIDは0から始まる可能性があるので issetを使って確認。
    //スイーツの詳細画面にいるということはログインしていると思うが、ログインページへ飛ばないようにisLogin関数で確認し、ログインしていなかったとしてもheader関数をつかわない
    if(isset($_POST['sweetsId']) && isset($_SESSION['user_id']) && isLogin() ){
        
        debug('POST送信があります。');
        //スイーツIDとユーザーIDを変数に入れる
        $s_id = $_POST['sweetsId']; //footer.phpのajaxからPOSTされたもの
        $u_id = $_SESSION['user_id'];

        //デバッグ
        debug('登録しようとしているスイーツのID:'.$s_id);
        debug('自分ユーザーID:'.$u_id);
    

        //例外処理
        try{
            
            //DBへ接続
            $dbh = dbConnect();
            //まずはDBのお気に入りテーブルに自分が見ているスイーツがあるか確認
            $sql = 'SELECT * FROM favorite WHERE sweets_id =:s_id AND user_id = :u_id';
            $data = array(':s_id'=>$s_id ,':u_id'=>$u_id);

            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            
            //クエリした結果、今見ているスイーツのIDが自分のお気に入りにあるか確認
            $resultCount = $stmt->rowCount();
            debug('DBから取得したお気に入り:'.print_r($resultCount,true));

            //レコードが一件でもある場合 =
            if(!empty($resultCount)){
                //デバッグ
                debug('このスイーツはお気に入りに存在します。お気に入りからこのスイーツを削除します');
                //DBのお気に入りテーブルからスイーツを削除する
                $sql = 'DELETE FROM favorite WHERE sweets_id = :s_id AND user_id = :u_id' ;
                $data =  array( ':s_id'=>$s_id, ':u_id'=>$_SESSION['user_id'] );
                $stmt = queryPost($dbh, $sql, $data);
            
            //スイーツのレコードがDBには無かった場合、
            }else{
                //デバッグ
                debug('このスイーツはお気に入りにありませんでした。お気に入りに登録します');
                //DBのお気に入りテーブルに情報を挿入している
                $sql = 'INSERT into favorite (sweets_id, user_id, create_date) VALUES(:s_id, :u_id, :date)';
                $data = array( ':s_id'=>$s_id, ':u_id'=>$_SESSION['user_id'], ':date'=>date('Y-m-d H:i:s')  );
                $stmt = queryPost($dbh, $sql, $data);
                
            }
             //例外処理
            }catch(Exception $e){
                error_log('エラーが発生しました:'.$e->getMessage());
            }
    }

    //======   Ajax通信終了 ===========//å
?>