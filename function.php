<?php
//=== ログ設定 ==================//
//ログを取るかどうか
ini_set('log_errors','on');
//出力ファイルを設定
ini_set('error_log','php.log');
//ユーザー画面にエラーメッセージを表示するかどうか
ini_set('display_errors', 'on');

//===デバッグ設定================//
//サービスをリリースするときはfalseに設定し、
//開発するときのみ、flgをtrueにする
$debug_flg = true;

function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ結果：'.$str);
    }
}

//===デバッグログ吐き出し関数================//
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始>>>>>>>>>>>>>');
    debug('セッションID'.session_id() );
    debug('セッション変数の中身'.print_r($_SESSION,true) );
    debug('現在日時タイムスタンプ:'.time() );
    //もし、ログイン日時とログイン期限のセッションに何かしらの値が入っていた場合 ＝ ログインの形跡があった場合、
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit']) ){
        //そのセッションの中身を表示する
        debug( 'ログイン日時タイムスタンプ:'.($_SESSION['login_date']+ $_SESSION['login_limit']) );
    }
}

//====セッション準備・セッション有効期限を延ばす==================//
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();


//===定数を設定=============================================//
define('MSG01','入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','6文字以上で入力してください');
define('MSG06A','255文字未満で入力してください');
define('MSG06B','30文字以下で入力してください');
define('MSG06C','20文字以下で入力してください');
define('MSG06D','15文字以下で入力してください');
define('MSG06E', '6ケタ以下で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います'); 
define('MSG10','半角数字のみ入力できます');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14','文字で入力してください');
define('MSG15','認証キーが違います');
define('MSG16','認証キーの有効期限が切れています');
define('MSG17','半角数字で入力してください'); 
define('MSG18','正しくありません。');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03','メールを送信しました');
define('SUC04','スイーツを新規に登録しました'); //LESSON17~19で登録 registSweets.php
define('SUC05','登録した人とチャットをしてみよう！');

//===グローバル変数==========================================//
//エラーメッセージ格納用の配列
$err_msg = array();


//=============================================
//= バリデーション系の関数
//=============================================

    //未入力チェック
    function validRequired($str, $key){
        /*金額のフォームなどでは0が入る可能性もあるのでemptyは使わない
            0を許容しない場合はif(empty($str)){}とする*/
        if($str === ''){ 
            global $err_msg; 
            $err_msg[$key] = MSG01;
        }
    }
    //Email形式チェック
    function validEmail($str, $key){
        if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
            global $err_msg;
            $err_msg[$key]= MSG02;
        }
    }
    //Email重複チェック
    function validEmailDup($email){
        global $err_msg;
        //例外処理
        try{
            $dbh = dbConnect(); //DB接続関数の中身を全て詰め込んでいる。
            //SQL作成 
            //delete_flg=0を指定しないと退会したユーザーのアドレスも拾ってくるので一度退会に使われたアドレスは一生登録できないことになってしまう
            // =>DB側ではあくまでdelete_flg1を立てただけでemailは残っているのでユニークキーを外してあげる必要がある。
            // =>show indexes from users  => インデックス一覧が出てくるので 
            // => alter table users drop index email; //usersテーブルのemailカラム(Key_name)のインデックスを削除完了
            $sql = ' SELECT count(*) FROM users WHERE email = :email; AND delete_flg = 0' ;
            $data = array(':email'=> $email);
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            //クエリ結果の値を取得
            $result = $stmt -> fetch(PDO::FETCH_ASSOC);
            if(!empty(array_shift($result))){
                $err_msg['email'] = MSG08; //そのEmailはすでに使われています。
            }
        }catch(Exception $e){
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07; //ログインの文字の下のdivに表示
        }
    }
    //パスワードチェック
    function validPass($str, $key){
    //半角英数字チェック
    validHalf($str, $key);
    //最大文字数チェック
    validMaxContent($str, $key);
    //最小文字数チェック
    validMinLen($str, $key);
    }

    //パスワードの同値チェック（入力と再入力）
    function validMatch($str1,$str2,$key){
        if($str1 !== $str2){
            global $err_msg;
            $err_msg[$key]= MSG03;
        }
    }

    //最小文字数チェック //$minはDBの設定と照らし合わせて指定する
    function validMinLen($str,$key,$min=6){//関数を呼び出す時に指定すれば$maxの値を変えることも可能
        if(mb_strlen($str) < $min){
            global $err_msg;
            $err_msg[$key] = MSG05; //6文字以上で入力してね
        }
    }

    //最大文字数チェック //$maxはDBの設定と照らし合わせて指定する
    //関数を呼び出す時に指定すれば$maxの値を変えることも可能
    function validMaxContent($str,$key,$max=255){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06A;
        }
    }
    function validMaxEmail($str,$key,$max=30){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06B;
        }
    }
    function validMaxName($str,$key,$max=20){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06C;
        }
    }
    function validMaxPass($str,$key,$max=15){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06D;
        }
    }
    function validMaxPrice($str,$key,$max=6){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06E;
        }
    }

    //半角英数字チェック
    function validHalf($str, $key){
        if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
            global $err_msg;
            $err_msg[$key] = MSG04;
        }
    }

    /*半角数字チェック
        =>スイーツの金額のバリデーションに使う*/
    function validNumber($str, $key){
    if(!preg_match("/^[0-9]+$/", $str)){
      global $err_msg;
      $err_msg[$key] = MSG17;
    }
  }
  
    /*電話番号形式チェック、郵便番号形式チェック、半角数字チェックは今回は作らない*/
    //出身地文字数チェックチェックは最大文字数チェックでOK。



    //=============================================
    //ユーザー情報取得関数 (profEdit.phpで使用)
    //=============================================
    function getUser($u_id){
        debug('ユーザー情報を取得します');
        //例外処理
        try{
            //DB接続
            $dbh = dbConnect();
            //SQL作成
            $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
            //データ流し込み
            $data = array(':u_id'=> $u_id);
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);
    
            //クエリ成功の場合 LESSON17~//
            if($stmt){
                return $stmt->fetch(PDO::FETCH_ASSOC);
            //クエリ失敗の場合 
            }else{
                return false;
            }

        }catch(exception $e){
            error_log('エラー発生:'.$e->getMessage());
        }
    }
    
    //=============================================
    //お気に入りスイーツ情報取得関数
    //=============================================
    function getMyFavorite($u_id){
        debug('自分のお気に入り情報を取得します');
        debug('自分のユーザーID'.$u_id);

        //DB処理
        try{
            //接続とSQL
            $dbh = dbConnect();
            $sql = 'SELECT * FROM favorite AS f LEFT JOIN sweets AS s ON f.sweets_id = s.id WHERE f.user_id = :u_id';
            $data = array(':u_id'=> $u_id);

            //クエリ実行
            $stmt = queryPost($dbh,$sql, $data);

            //クエリ判定
            if($stmt){
                //クエリ結果の全レコードを返却
                return $stmt -> fetchAll();
            }else{
                return false;
            }
        

        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
        }
    }


    //=============================================
    //スイーツ情報取得関数 
    //=============================================
    function getSweets($u_id, $s_id){
        debug('商品情報を取得します');
        debug('ユーザーID:'.$u_id);
        debug('スウィーツID'.$s_id);
        
        //DB処理
        try{
            //DBへ接続
            $dbh = dbConnect();
            //SQL
            $sql = 'SELECT * FROM sweets WHERE user_id = :u_id AND id = :s_id AND delete_flg = 0';
            //プレースホルダー
            $data = array(':u_id'=>$u_id, ':s_id'=> $s_id);
            //クエリ実行
            $stmt = queryPost($dbh,$sql, $data);

            //クエリ成功失敗判定
            if($stmt){
                //クエリ結果のレコードを一行返却
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }else{
                return false;
            }

        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
        }
    }

    //=========================================================
    //== スイーツのリスト取得関数   home.phpで使用
    //=========================================================
    function getSweetsList($currentMinNum=1, $category, $sort, $span = 12){
        //デバッグ
        debug('商品情報を取得します。');
        
        //DB処理
        try{
            //==  SQL1 カテゴリ選択用の処理 Lesson22で追加 １ == //
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                debug('「「「「「「「「「「  カテゴリー選択用のSQLを実行します   「「「「「「「「「');
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

                //DBへ接続
                $dbh = dbConnect();
                //sweetsテーブルからidを取得（＝＞スイーツが何件あるかを判別する）
                $sql = 'SELECT id FROM sweets WHERE delete_flg = 0'; //ID数を取得して総レコード数をカウントする
                //商品カテゴリーを取得。LESSON22で追加
                    //home.phpで$categoryのGETパラメータがある場合が空の場合sqlをつなげる
                if(!empty($category)) $sql .= 'WHERE category_name ='.$category;
                //ソート順を取得 LESSON22で追加
                //home.phpで$sortが空ではない場合それぞれのcaseの$sqlを 'SLECT id FROM sweetsにつなげる'
                if(!empty($sort)){
                    switch($sort){
                        case 1: //昇順に並び替えのsqlを 「$sql .= 'WHERE category_name ='.$category;」につなげる
                            $sql .= 'ORDER BY price ASC';
                            break;
                        case 2: //降順に並び替えのsqlを「$sql .= 'WHERE category_name ='.$category;」につなげる
                            $sql .= 'ORDER BY price DESC';
                            break;
                    }
                }
                //プレースホルダー
                $data = array();
                
                //クエリ実行
                $stmt = queryPost($dbh,$sql,$data);

                //総レコード数と総ページ数を変数に格納
                $rst['total'] = $stmt->rowCount(); //総レコード数 home.phpで echo getSweetsData['total']; として呼び出す
                $rst['total_page'] = ceil($rst['total']/$span); //総ページ数  home.phpで echo getSweetsData['total_page']; として呼び出す
                
                //クエリが失敗した場合
                if(!$stmt){
                    debug('クエリに失敗しました１。');
                    debug('総レコード数と総ページ数が取得できませんでした');
                    return false;
                }else{
    
                    debug('クエリに成功しました１');
                    debug('スイーツの総件数:'.print_r($rst['total'],true));
                    debug('home.phpの総ページ数:'.print_r($rst['total_page'],true));
                    debug('総レコード数と総ページ数が取得できました');
                }
            //===========================================//
            //==== SQL2 検索機能用のコード 検索機能 Lesson22で追加====//
            //===========================================//
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                debug('「「「「「「「  カテゴリーと値段による並び替えののSQLを実行します 「「「「「');
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                //商品データを全て取得
                $sql = 'SELECT * FROM sweets WHERE delete_flg = 0';
                //$categoryがあった場合、そのカテゴリーに属するスイーツのデータを全て取得する
                if(!empty($category)) $sql .= ' WHERE category_name = '.$category;
                //ソートがあった場合、
                if(!empty($sort)){
                    switch($sort){
                    case 1:
                        $sql .= ' ORDER BY price ASC';
                        break;
                    case 2:
                        $sql .= ' ORDER BY price DESC';
                        break;
                    }
                }
            
            //===========================================//
            //== SQL3　全商品の取得数のSQL =======================//
            //===========================================//
            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
            debug('「「「「「「「「「「  商品の取得数を判別します  「「「「「「「「「');
            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
            //商品が何件取得できたかを判別するSQL
            //$spanと$currentMinNumはhome.phpで定義している
            $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
            $data = array();
            debug('商品が何件取得できたかを判別するSQLの結果：'.$sql);
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            // クエリが成功したら、
            if($stmt){
                // クエリ結果のデータを全レコードを格納
                $rst['data'] = $stmt->fetchAll();  //home.phpで echo getSweetsData['data']; として呼び出す
                debug('$rst[data]の中身:'.print_r($rst['data'],true));
                debug('クエリ結果の全レコードを取得しました。');
                return $rst;
            }else{
                return false;
            }


        //例外処理
        }catch(exception $e){
            $e->getMessage();
        }
    }
    

    //=========================================================
    //== 自分の登録したスイーツの情報取得関数   自分で追加
    //=========================================================
    function getMySweetsList($currentMyMinNum=1, $u_id, $span = 6){
        //デバッグ
        debug('自分の登録した商品情報を取得します。');
        
        //DB処理
        try{
            //==  SQL1 件数用の処理 「？件〜？件を表示しています」 == //
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                debug('「「「「  スイーツテーブルから自分の登録したスイーツを取得します 「「「「「「');
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

                //DBへ接続
                $dbh = dbConnect();
                //sweetsテーブルからidを取得（＝＞スイーツが何件あるかを判別する）
                $sql = 'SELECT id FROM sweets WHERE user_id = :u_id '; //ID数を取得して総レコード数をカウントする

                //プレースホルダー
                $data = array(':u_id'=>$u_id);
                
                //クエリ実行
                $stmt = queryPost($dbh,$sql,$data);

                //総レコード数と総ページ数を変数に格納
                $rst['total'] = $stmt->rowCount(); //総レコード数 home.phpで echo getSweetsData['total']; として呼び出す
                $rst['total_page'] = ceil($rst['total']/$span); //総ページ数  home.phpで echo getSweetsData['total_page']; として呼び出す
                
                //クエリが失敗した場合
                if(!$stmt){
                    debug('クエリに失敗しました１。');
                    debug('総レコード数と総ページ数が取得できませんでした');
                    return false;

                }else{
                    debug('クエリに成功しました１');
                    debug('$rst[total]の中身:'.print_r($rst['total'],true));
                    debug('$rst[total_page]の中身:'.print_r($rst['total_page'],true));
                    debug('自分の登録したスイーツの総レコード数と総ページ数が取得できました');
                }

            
            //==== SQL3 商品の取得数のSQL ==========================//
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                debug('「「「「「「「「「「  商品の取得数を判別しますよ  「「「「「「「「「');
                debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                //商品が何件取得できたかを判別するSQL
                $sql .= ' LIMIT '.$span.' OFFSET '.$currentMyMinNum;
                $data = array();
                debug('商品が何件取得できたかを判別するSQLの結果：'.$sql);
                // クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                // クエリが成功したら、
                if($stmt){
                    // クエリ結果のデータを全レコードを格納
                    $rst['data'] = $stmt->fetchAll();  //home.phpで echo getSweetsData['data']; として呼び出す
                    debug('$rst[data]の中身:'.print_r($rst['data'],true));
                    debug('クエリ結果の全レコードを取得しました。');
                    return $rst;
                }else{
                    debug('クエリに失敗しました');
                    return false;
                }

        //例外処理
        }catch(exception $e){
            $e->getMessage();
        }
    }


    //==========================================================
    //== 自分のお気に入り登録したスイーツの情報取得関数  LESSON20で追加==
    //function getFavSweetsList($currentMinNum=1, $u_id, $span = 6){
    //    //デバッグ
    //    debug('自分のお気に入りスイーツ情報を取得します。');
    //    
    //    //DB処理
    //    try{
    //        //==  SQL1 カテゴリ選択用の処理 Lesson22で追加 １ == //
    //            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    //            debug('「「「「「 お気に入りテーブルから自分のお気に入りスイーツを取得します「「「「「');
    //            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
//
    //            //DBへ接続
    //            $dbh = dbConnect();
    //            //sweetsテーブルからidを取得（＝＞スイーツが何件あるかを判別する）
    //            $sql = 'SELECT sweets_id FROM favorite WHERE user_id =:u_id'; //ID数を取得して総レコード数をカウントする
//
    //            //プレースホルダー
    //            $data = array(':u_id'=>$u_id);
    //            
    //            //クエリ実行
    //            $stmt = queryPost($dbh,$sql,$data);
//
    //            //総レコード数と総ページ数を変数に格納
    //            $rst['total'] = $stmt->rowCount(); //総レコード数 home.phpで echo getSweetsData['total']; として呼び出す
    //            $rst['total_page'] = ceil($rst['total']/$span); //総ページ数  home.phpで echo getSweetsData['total_page']; として呼び出す
    //            
    //            //クエリが失敗した場合
    //            if(!$stmt){
    //                debug('クエリに失敗しました１。');
    //                debug('総レコード数と総ページ数が取得できませんでした');
    //                return false;
    //            }else{
    //
    //                debug('クエリに成功しました１');
    //                debug('$rst[total]の中身:'.print_r($rst['total'],true));
    //                debug('$rst[total_page]の中身:'.print_r($rst['total_page'],true));
    //                debug('総レコード数と総ページ数が取得できました');
    //            }
//
    //        //==== SQL2 検索機能用のコード 検索機能 Lesson22で追加 ２ =========//
    //            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    //            debug('「「「「「「「  カテゴリーと値段による並び替えののSQLを実行します 「「「「「');
    //            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    //            //商品データを全て取得
    //            $sql = 'SELECT * FROM sweets';
    //            //$categoryがあった場合、そのカテゴリーに属するスイーツのデータを全て取得する
    //            if(!empty($category)) $sql .= ' WHERE category_name = '.$category;
    //            //ソートがあった場合、
    //            if(!empty($sort)){
    //                switch($sort){
    //                case 1:
    //                    $sql .= ' ORDER BY price ASC';
    //                    break;
    //                case 2:
    //                    $sql .= ' ORDER BY price DESC';
    //                    break;
    //                }
    //            }
    //        //================================================//
    //        //SQL3 商品の取得数のSQL 
    //        //================================================//
    //            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    //            debug('「「「「「「「「「「  商品の取得数を判別します  「「「「「「「「「');
    //            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
    //            //商品が何件取得できたかを判別するSQL
    //            $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    //            $data = array();
    //            debug('商品が何件取得できたかを判別するSQLの結果：'.$sql);
    //            // クエリ実行
    //            $stmt = queryPost($dbh, $sql, $data);
    //            // クエリが成功したら、
    //            if($stmt){
    //                // クエリ結果のデータを全レコードを格納
    //                $rst['data'] = $stmt->fetchAll();  //home.phpで echo getSweetsData['data']; として呼び出す
    //                debug('$rst[data]の中身:'.print_r($rst['data'],true));
    //                debug('クエリ結果の全レコードを取得しました。');
    //                return $rst;
    //            }else{
    //                return false;
    //            }
//
//
    //    //例外処理
    //    }catch(exception $e){
    //        $e->getMessage();
    //    }
    //}



    //=============================================
    // スイーツの情報取得関数 Lesson21で追加  SweetsDetail.phpで使用 
    //  Sweetsテーブルとカテゴリーテーブルを外部結合してスイーツのカテゴリーも含めて情報を入手している。
    //=============================================
    function getSweetsOne($s_id){
        debug('スイーツ情報を取得します');
        debug('スイーツのID:'.$s_id);

        try{
            //DB接続
            $dbh = dbConnect(); 
            //TODO:復習
            //SQL c.name => categoryテーブルのnameカラム。s.idだとSweetsテーブルのidカラムになる 
            //LEFT J
            //$sql ='SELECT s.id, s.name, s.comment, s.price, s.pic1, s.pic2, s.pic3, s.user_id, s.create_date, s.update_date, c.name AS category
            //    FROM Sweets AS s LEFT JOIN category AS c ON s.category_name = c.id  
            //    WHERE s.id = :s_id AND s.delete_flg = 0 AND c.delete_flg = 0';
            //新しいSQL (スイーツテーブルとカテゴリーテーブルを分けていない想定)   
            $sql = 'SELECT id, name, store_name, category_name, comment, price, pic1, pic2, pic3, user_id, create_date, update_date
                FROM sweets
                WHERE id = :s_id AND delete_flg = 0';

            //プレースホルダー
            $data = array(':s_id'=>$s_id);
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);

            if($stmt){
                //クエリ結果のデータを１レコード返却
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }else{
                return false;
            }

        }catch(Exception $e){
            error_log('エラー発生：'.$e->getMessage());
        }
        
    }

    //=============================================
    //== マイスイーツ（お気に入り）の情報取得関数 
    //Lesson25で追加  mypage.phpで使用 == //
    //=============================================
    function getMySweets($u_id){
        debug('自分のお気に入りのスイーツ情報を取得します');
        debug('ユーザーID:'.$u_id);

        //DB処理開始
        try{
            $dbh = dbConnect();
            $sql = 'SELECT * FROM sweets WHERE user_id =:u_id AND delete_flg = 0';
            $data = array(':u_id'=>$u_id);
            $stmt = queryPost($dbh, $sql, $data);

            if($stmt){
                //クエリ結果の全レコードを返却
                return $stmt->fetchAll();
            }else{
                //falseを返す
                return false;
            }

        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
        }
    }

    //=============================================
    //掲示板情報取得関数（掲示板とメッセージの情報を結合）
    //=============================================
    function getMsgAndBoard2($b_id){
        debug('メッセージ情報を取得します');
        debug('掲示板ID:'.$b_id); //ここはOK
        //DB処理
        try{
            //DB接続
            $dbh = dbConnect();
            //SQL作成
            $sql = 
                'SELECT m.id AS m_id, board_id, send_date, to_user, from_user, sale_user, buy_user, msg, b.create_date
                FROM message AS m RIGHT JOIN board AS b ON  b.id = m.board_id
                WHERE b.id = :id AND m.delete_flg = 0  
                ORDER BY send_date ASC ' ;

            //プレースホルダー
            $data = array(':id'=> $b_id);
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);
        
            //クエリ
            if($stmt){
                //クエリ結果の全データを返却
                return $stmt->fetchAll();
            }else{
                //失敗したらfalseを返す
                return false;
            }

        }catch(Exception $e){
            error_log('エラー発生:' . $e->getMessage());
        }
    }

    //=============================================
    //掲示板情報取得関数（掲示板とメッセージの情報を結合）
    //=============================================
    function getMsgAndBoard($b_id){
    debug('msg情報を取得します。');
    debug('掲示板ID：'.$b_id);
    //例外処理
        try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT m.id AS m_id, board_id, send_date, to_user, from_user, sale_user, buy_user, sweets_id, msg, b.create_date 
                FROM message AS m RIGHT JOIN board AS b ON b.id = m.board_id 
                WHERE b.id = :id
                ORDER BY send_date ASC';

        $data = array(':id' => $b_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
    
        if($stmt){
            // クエリ結果の全データを返却 
            //ここでfetch(PDO::FETCH_ASSOC)を使うと結合したDBからデータを取得できないので fetchAll()を使う
            return $stmt->fetchAll();
        }else{
            return false;
        }
    
        } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage() );
        }
    }
    //=============================================
    //掲示板情報取得関数（掲示板とメッセージの情報を結合）
    //=============================================
    function getMyMsgsAndBoard($u_id){
        //デバッグ
        debug('自分の掲示板メッセージ情報を取得します');

        //DB処理
        $dbh = dbConnect();
        //掲示板テーブルから自分が販売者or登録者に該当する掲示板データを探し出す。
        $sql = 'SELECT * FROM board AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
        $data = array(':id'=>$u_id);
        $stmt = queryPost($dbh,$sql,$data);

        //取得した掲示板のクエリ結果を変数に入れる  TODO:return $stmt->fetchAll()だと駄目？
        $rst = $stmt->fetchAll();
        debug('掲示板から取得したデータ:'.print_r($rst,true));

        //クエリ結果（掲示板データ）が取得できた場合、それをforeachで回して表示する
        //$rstの中身は掲示板データ
        if(!empty($rst)){
            foreach($rst as $key => $val){
                //メッセージテーブルからデータを取得、掲示板IDが分解した$rstにあるIDに該当するもの
                $sql = 'SELECT * FROM `message` WHERE board_id = :id  AND delete_flg =0 ORDER BY send_date DESC';
                $data = array(':id'=>$val['id']);
                $stmt = queryPost($dbh, $sql, $data);

                //取得したメッセージのクエリ結果をに入れる TODO:デバッグ結果を見て理解する。
                $rst[$key]['msg'] = $stmt->fetchAll();
                debug( 'メッセージテーブルから取得したデータ:'.print_r($rst[$key]['msg'],true) );
            }
        }
        
        //クエリ結果を判定
        if($stmt){
            //クエリ結果の全データを返却
            return $rst;
        }else{
            return false;
        }
    }

    //=============================================
    //カテゴリーデータ取得関数 //LESSON17で追加 home.phpで使用
    //=============================================
    function getCategory(){
        debug('カテゴリー情報を取得します');
        try{
            //DBへ接続
            $dbh = dbConnect();
            //SQL
            $sql = 'SELECT category_name FROM sweets';
            //プレースホルダー
            $data = array(); 
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);

            //クエリが成功したら
            if($stmt){
                return $stmt->fetchAll(); //取得したデータを返す
            }else{
                return false; //falseを返す
            }
        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage() );
        }
    }

    //=============================================
    //サニタイズ関数 LESSON20で追加 
    //=============================================
    
    //DBからデータを取って来て画面に表示する際などに不正な値を取り除く。
    function sanitize($str){
        return htmlspecialchars($str,ENT_QUOTES); //ENT_QUOTES =>
    }

    //=============================================
    //フォーム入力保持関数 profEdit.phpとregistSweets.phpで使用  
    //TODO:　条件分岐通りに動くかチェックしてみる
    //=============================================
    function getFormData($str, $flg = false){
        
        //Lesson22でGETかPOSTを判別する処理を追加 
            //デフォルトではfalseになっている
            if($flg){
                $method = $_GET;
                debug('GET送信です');
            }else{
                $method = $_POST;
                debug('POST送信です');
            }
        
        //== ==//
            //グローバル変数化
            global $dbFormData;

            //ユーザーデータがあった場合
            if(!empty($dbFormData)){

                //ユーザーデータあり、フォームにエラーがあった場合
                if(!empty($err_msg[$str])){
                    //POSTにデータがあった場合（エラーが発生したフォーム部分の処理になる）
                    if(isset($method[$str])){ //金額や郵便番号などのフォームの可能性があり、emptyだと0が入力されても入ってないと判定されてしまうのでissetにした
                        return sanitize($method[$str]);//POSTされたデータを受け入れる
                    //POSTにデータが無かった場合（一部分のフォームだけエラーが発生した場合の想定）
                    }else{
                        return sanitize($dbFormData[$str]);//もともとあったDBの情報をそのままフォーム上にに保持する
                    }

                //ユーザーデータがあり、フォームにエラーが無かった場合
                }else{
                    //POSTにデータがあり、DBにある情報と差異が見られた場合
                    if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                        return sanitize($method[$str]); //POSTされたデータに更新する
                    //変更していない場合（ データがPOSTされなかった場合）
                    }else{
                        return sanitize($dbFormData[$str]);//もともとの値を保持する
                    }
                }

        //そもそもユーザーデータが無かった場合
        }else{
            //ポストされたデータがあればそれをreturnする
            if(isset($method[$str])){
                return sanitize($method[$str]);
            }
        }
    }
    
    //=============================================
    //メール送信関数 Lesson15で追加
    //=============================================
    function sendMail($from, $to, $subject, $comment){
        if(!empty($to) && !empty($subject) && !empty($comment)){
            //文字化けしないように設定
            mb_language("Japanese");
            mb_internal_encoding("UTF-8");

            //メール送信（送信結果はtrue or falseで返ってくる）
            $result = mb_send_mail($to, $subject, $comment, "From:".$from);
            if($result){
                debug('メールを送信しました');
            }else{
                debug('[エラー発生]メールの送信に失敗しました');
            }
        }
    }

    //=============================================
    //エラーメッセージ表示関数 Lesson15で追加
    //=============================================
    function getErrMsg($key){
        global $err_msg;
        if(!empty($err_msg[$key])){
            return $err_msg[$key];
        }
    }

    //=============================================
    //セッション取得関数（１度のみ取得）mypage.phpの中で使用
    //=============================================
    function getSessionFlash($key){
        if(!empty($_SESSION[$key])){
            $data = $_SESSION[$key];
            $_SESSION[$key]= '';
            return $data;
        }
    }

    //=============================================
    //認証キー作成 //第１６回で追加パスワードリマインダーの認証キー発行 TODO：テスト
    //=============================================
    function makeRandKey($length = 8){ //認証キーを８文字に制限
        $chars = 'abcdefghijklmnopqrstuvxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i = 0; $i < $length; ++$i){
            $str .=  $chars[mt_rand(0,61)]; //小文字のa~z,大文字のA~Z,数字の0~9を全て合わせると61個になる。
        }
        return $length;
    }

    //=============================================
    //固定長チェック関数 //LESSON16で追加
    //=============================================
    function validLength($str,$key,$len = 8 ){ 
        if(mb_strlen($str) !== 8){
            global $err_msg;
            $err_msg[$key] = $len.MSG14;//8文字で入力してくださいとなる
        }
    }

    //=============================================
    //selectboxチェック //LEESON17で追加
    //=============================================

    //function validSelect($str,$key){
        //if(!preg_match("/^[0-9]+$/", $str)){
        //    global $err_msg;
        //    $err_msg[$key] = MSG18;
        //}
    //}

    //=============================================
    //画像処理関数 LESSON17で作成
    //=============================================
    function uploadImg($file,$key){
        debug('画像アップロード処理開始');
        debug('FILE情報:'.print_r($file,true));

        //画像のエラーあり、それが数値型かどうかis_intでチェックする
        if( isset($file['error']) && is_int($file['error'])){
            //例外処理
            try{
                //画像のエラー
                //RuntimeException => フォーム入力以外で起こるエラー 。DB接続できなかった場合やファイルアップロードの失敗など
                //LogicException => 普通は起こりえないはずのエラー。
                switch($file['error']){
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('ファイルが定義されていません');
                    case UPLOAD_ERR_INI_SIZE: //画像サイズがphp.iniの定義を超えた場合 =>upload_max_filesize、post_max_size、memory_limitの三つを編集して決める。
                        throw new RuntimeException('ファイルサイズがphp.iniで定義したサイズを超えています');
                    case UPLOAD_ERR_FORM_SIZE: //画像サイズがフォームの定義を超えた場合＝＞htmlに設置したinputタグ内でvalueに指定できる。3145728 = 1024byte * 1024byte = 1MB * 3で 3MB = 3145728byte
                        throw new RuntimeException('ファイルサイズがフォームで定義したサイズを超えています。');
                    default: 
                        throw new RuntimeException('その他のエラーが発生しました');
                }

                //MIMEタイプを自動でチェックする
                $type = @exif_imagetype($file['tmp_name']);
                if(!in_array($type,[IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
                    //in_arrayで調べた$typeの中身が第二引数に指定した三つに一致しなかった場合
                    throw new RuntimeException('画像形式が未対応です'); 
                }

                //画像パスを指定。 upload_imgフォルダの名前に$fileの中にあるtmp_nameをハッシュ化したものを入れ、そこにマイムタイプ（.jpeg,.png,など）つなげる。
                //同じ画像名でアップロードされる可能性があるためハッシュ化している
                //sh1_fileというハッシュ化は解読される可能性があるので個人情報（パスワードなど）には使わないようにしよう。今回は画像の名前をハッシュ化するだけなのでOK
                $path = 'upload_img/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
                //もし、move_upload_fileでfalseが返ってきたら（true or false）で返ってくる
                if(!move_uploaded_file($file['tmp_name'],$path)){
                    throw new RuntimeException('ファイル保存時にエラーが発生しました');
                }

                //保存したファイルパスのパーミッションを変更する
                chmod($path,0644); //TODO: 0644の意味は？権限について自分で調べる。
                debug('ファイルは正常にアップロードされました');
                debug('ファイルパス:'.$path);
                return $path;
                
            }catch(RuntimeException $e){
                debug($e->getMessage());
                global $err_msg;
                $err_msg[$key] = $e->getMessage();
            }
        }
    }

    //=============================================
    //画像処理
    //=============================================
    function uploadImg2($file,$key){
        debug('画像アップロード処理開始');
        debug('FILE情報：'.print_r($file,true));

        if (isset($file['error']) && is_int($file['error'])) {
        
        try{
            // バリデーション
            // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
            //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
            switch ($file['error']) {
                case UPLOAD_ERR_OK: // OK
                    break;
                case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
                    throw new RuntimeException('ファイルが選択されていません');
                case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
                case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
                    throw new RuntimeException('ファイルサイズが大きすぎます');
                default: // その他の場合
                    throw new RuntimeException('その他のエラーが発生しました');
            }

            // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
            // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
            $type = @exif_imagetype($file['tmp_name']);
            if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
                throw new RuntimeException('画像形式が未対応です');
            }

            // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
            // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
            // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
            // image_type_to_extension関数はファイルの拡張子を取得するもの
            $path = 'upload_img/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
            if(!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
                throw new RuntimeException('ファイル保存時にエラーが発生しました');
            }
            // 保存したファイルパスのパーミッション（権限）を変更する
            chmod($path, 0644);
            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス：'.$path);
            return $path;
        
            }catch (RuntimeException $e) {
                debug($e->getMessage());
                global $err_msg;
                $err_msg[$key] = $e->getMessage();
            }
        }
    }
    //============================================================
    // 画像削除関数
        function deleteImg($pic,$s_id){
            debug('画像１〜３判別情報:'.print_r($pic,true));
            debug('スイーツのID:'.$s_id);
            debug('IDが'.$s_id.'のスイーツの画像'.print_r($pic,true).'を削除します');
            
            try{
                //DB接続
                $dbh = dbConnect();
                ////SQL
                $sql = 'DELETE :pic FROM sweets WHERE id = :s_id';
                ////プレースホルダー
                $data = array(':pic'=>$pic, ':s_id'=>$s_id);

                //クエリー
                $stmt = queryPost($dbh, $sql, $data);

                //クエリ判定
                if($stmt){
                    debug('画像の削除に成功しました');
                    return true;
                }else{
                    debug('画像の削除に失敗しました');
                    return false;
                }

            }catch(Exception $e){
                error_log('エラー発生:'.$e->getMessage() );
            }
        }





    //============================================================
    //== home.phpの出品された商品のページング処理関数 ==///
    // $currentPageNum : 現在のページ数
    // $totalPageNum : 総ページ数
    // $link : 検索用GETパラメータリンク
    // $pageColNum : ページネーション表示数
    function pagenation( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
        // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
        if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
            $minPageNum = $currentPageNum - 4;
            $maxPageNum = $currentPageNum;
        // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
        }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
            $minPageNum = $currentPageNum - 3;
            $maxPageNum = $currentPageNum + 1;
        // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
        }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
            $minPageNum = $currentPageNum - 1;
            $maxPageNum = $currentPageNum + 3;
        // 現ページが1の場合は左に何も出さない。右にリンクを4個出す。
        }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
            $minPageNum = $currentPageNum;
            $maxPageNum = 5;
        // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
        }elseif($totalPageNum < $pageColNum){
            $minPageNum = 1;
            $maxPageNum = $totalPageNum;
        // それ以外は左に２個出す。
        }else{
            $minPageNum = $currentPageNum - 2;
            $maxPageNum = $currentPageNum + 2;
        }
        //実際に吐き出すページネーションの中身
        echo '<div class="pagenation">';
            echo '<ul class="pagenation__list">';

            //現在のページが1でなければ戻るボタン(<)を表示する
            if($currentPageNum != 1){
                echo '<li class="list__item"><a class="list__item__link" href="?p=1'.$link.'">&lt;</a></li>';
            }

            //ページネーションのボタンを表示する
            //$minPageNumが$maxPageNumより少ない限りページネーションのボタンをふやす
            for($i = $minPageNum; $i <= $maxPageNum; $i++){
                echo '<li class="list__item ';  
                if($currentPageNum == $i ){ echo 'active'; } 
                echo ' "><a class="list__item__link" href=" ?p='.$i.$link.' "> '.$i.'</a></li>';
            }

            //現在のページが最大ページではなく、最大ページが1以上の場合は進むボタン(>)を表示する
            if($currentPageNum != $maxPageNum && $maxPageNum > 1){
                echo '<li class="list__item"><a class="list__item__link" href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
            }
            echo '</ul>';
        echo '</div>';
    }

    //============================================================
    //　mypage.phpのお気に入り商品のページング
    //============================================================
    //== home.phpの出品された商品のページング処理関数 ==///
    // $currentFavPageNum : 現在のページ数
    // $totalPageNum : 総ページ数
    // $link : 検索用GETパラメータリンク
    // $pageColNum : ページネーション表示数
    function favPagenation( $currentFavPageNum, $totalPageNum, $link = '', $pageColNum = 5){
        // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
        if( $currentFavPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum - 4;
            $maxPageNum = $currentFavPageNum;
        // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
        }elseif( $currentFavPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum - 3;
            $maxPageNum = $currentFavPageNum + 1;
        // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
        }elseif( $currentFavPageNum == 2 && $totalPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum - 1;
            $maxPageNum = $currentFavPageNum + 3;
        // 現ページが1の場合は左に何も出さない。右に５個出す。 $totalPageNum = 5  $pageColNum = 
        }elseif( $currentFavPageNum == 1 && $totalPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum;
            $maxPageNum = 5;
        // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
        }elseif($totalPageNum < $pageColNum){
            $minPageNum = 1;
            $maxPageNum = $totalPageNum;
        // それ以外は左に２個出す。
        }else{
            $minPageNum = $currentFavPageNum - 2;
            $maxPageNum = $currentFavPageNum + 2;
        }
        //実際に吐き出すページネーションの中身
        echo '<div class="pagenation">';
            echo '<ul class="pagenation__list">';
            if($currentFavPageNum != 1){
                echo '<li class="list__item"><a class="list__item__link" href="?p=1'.$link.'">&lt;</a></li>';
            }
            for($i = $minPageNum; $i <= $maxPageNum; $i++){
                echo '<li class="list__item ';  
                if($currentFavPageNum == $i ){ echo 'active'; } 
                echo ' "><a class="list__item__link" href=" ?p='.$i.$link.' "> '.$i.'</a></li>';
            }
            if($currentFavPageNum != $maxPageNum && $maxPageNum > 1){
                echo '<li class="list__item"><a class="list__item__link" href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
            }
            echo '</ul>';
        echo '</div>';
    }
    
    //=========================================
    //画像表示用関数
    //=========================================
    function showImg($path){
        if(empty($path)){
            return 'img/sample_img1.jpg';
        }else{
            return $path;
        }
    }

    //=============================================
    //GETパラメータ付与 // TODO復習
    //=============================================
    // $del_key : 付与から取り除きたいGETパラメータのキー
        //=>productDetail.phpではs_id(スイーツのID)を取り除いてページ数のGETパラメータだけを付与するようにしている。
    function appendGetParam($arr_del_key = array()){
        if(!empty($_GET)){ //GETパラメータが存在した場合
            $str = '?';
            foreach($_GET as $key => $val){ //GETパラメータをkey=>valの形で分解
                if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
                    $str .= $key.'='.$val.'&'; // => ?$key=$val& となる。
                }
            }
            
            $str = mb_substr($str, 0, -1, "UTF-8"); // $arr_del_keyを取り除いた時に ? も余るのでそれを取り除く
            return $str;  
            //echoにするとhome.phpでスイーツの画像をクリックした時にGETパラメータがおかしくなる =>?p_idの処理に移ってしまう
            //echoからreturnに修正すると逆に今までechoにしていたところに影響が出るので関数を使っていたところを再度テストチェックするようにしよう
        }
    }

    //=============================================
    //ログイン認証関数  Lesson24で追加
    //=============================================
    function isLogin(){
        //ログインしている場合
        if(!empty($_SESSION['login_date'])){
            //デバッグ
            debug('ログイン済みユーザーです');

            //現在日時が最終ログイン日時 + 有効期限を超えていた場合
            if( time() > $_SESSION['login_date'] + $_SESSION['login_limit']){
                debug('ログイン期限をオーバーしています');
                session_destroy();
                return false;

                //現在日時が最終ログイン日時 + 有効期限を超えていない場合。
                }else{
                    debug('ログイン有効期限内のユーザーです');
                    return true;
                }

        //ログインしていない場合
        }else{
            debug('未ログインユーザーです');
            return false;
        }
    }
    
    
    //=============================================
    //お気に入り登録関数  Lesson24で追加
    //=============================================
    function isFav($u_id, $s_id){

        //デバッグ
        //print_rで$u_idや$s_idをチェックすると中身が１になるので注意
        debug('お気に入り情報があるか確認します');
        debug('自分のユーザーID:'.$u_id); 
        debug('スイーツID:'.$s_id);
        //DB処理
        try{
            $dbh = dbConnect();
            //
            $sql = 'SELECT * FROM  favorite WHERE user_id = :u_id AND sweets_id =  :s_id ';
            $data = array( ':u_id'=>$u_id, ':s_id'=>$s_id );

            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            
            //お気に入り
            if($stmt -> rowCount()){
                debug('このスイーツはお気に入りに登録してあります');
                return true;
            }else{
                debug('このスイーツはまだお気に入りに登録していません');
                return false;
            }

        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage() );
        }
    }


    //================================================//
    //==== DB接続系の関数 ==============================//
    //================================================//

    //DB接続関数
    function dbConnect(){
        //DBの接続準備
        $dsn = 'mysql:dbname=boardapp;host=localhost;charset=utf8';
        $user = 'root';
        $password = 'root';
        $options = array(
            // SQL実行失敗時にはエラーコードのみ設定。ここを変えてエラーを特定できることもある
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // デフォルトフェッチモードを連想配列形式に設定
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
            // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        );
        $dbh = new PDO($dsn, $user, $password, $options);
        return $dbh; //忘れずに！
    }

    //クエリ実行関数
    //function queryPost($dbh, $sql, $data){
        //クエリ作成
        //$stmt = $dbh->prepare($sql);
        //プレースホルダーに値をセットし、SQLを実行
        //$stmt -> execute($data);
        //実行結果を返す
        //return $stmt;
    //}

    //クエリ関数変更後（getUser関数や）
    //今まで呼び出し元で行っていたクエリの成功、失敗の判定を関数内で行うようにした
    function queryPost($dbh, $sql, $data){
        
        //クエリ作成
        $stmt = $dbh->prepare($sql);
        
        //SQLのエラー内容をデバッグ
        debug('SQLエラー:'.print_r($stmt->errorInfo(),true));

        //プレースホルダーに値をセットしSQLを実行
            if(!$stmt->execute($data)){
                debug('クエリは失敗しました');
                debug('失敗したSQL:'.print_r($stmt,true));
                $err_msg['common'] = MSG07;
                return 0; //なぜ０を返す？
            }
            //成功した場合
            debug('クエリは成功しました');
            debug('成功したSQL:'.print_r($stmt,true));
            return $stmt; 
    }

?>