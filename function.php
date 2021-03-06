<?php
//=== ログ設定 ==================//
//ログを取るかどうか
ini_set('log_errors','off');
//出力ファイルを設定
ini_set('error_log','php.log');
//tocheck: 開発時のみオンにするように
ini_set('display_errors', 'off');


//===デバッグ設定================//
/*tochek: サービスをリリースするときは
    falseに設定し、開発するときのみ
    flgをtrueにする*/
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
    debug('セッション変数の中身'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ:'.time() );
    //もし、ログイン日時とログイン期限のセッションに何かしらの値が入っていた場合 ＝ ログインの形跡があった場合、
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        //そのセッションの中身を表示する
        debug( 'ログイン日時タイムスタンプ:'.($_SESSION['login_date']+ $_SESSION['login_limit']));
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
define('MSG01B', '画像1は必須です');
define('MSG02', 'Emailの形式で入力してください<br>(半角英数字と@のみ利用可能)');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05A','パスワードは6文字以上で入力してください');
define('MSG05B','パスワードは15文字以下で入力してください');
define('MSG05C','パスワードとパスワード再入力の値が一致しません');
define('MSG06A','100文字以下で入力してください');
define('MSG06B','30文字以下で入力してください');
define('MSG06C','15文字以下で入力してください');
define('MSG06D','15文字以下で入力してください');
define('MSG06E', '6ケタ以下で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailアドレスは他のユーザーが使用しています');
define('MSG09', 'メールアドレスまたはパスワードが違います'); 
define('MSG10','半角数字のみ入力できます');
define('MSG12', '古いパスワードが違います');
define('MSG13', '新しく設定するパスワードと現在設定されているパスワードが同じです');
define('MSG14','文字で入力してください');
define('MSG15','認証キーが違います');
define('MSG16','認証キーの有効期限が切れています');
define('MSG17','半角数字で入力してください'); 
define('MSG18','正しくありません。');
//サクセスメッセージ一覧
define('SUC01', 'パスワードを変更しました！');
define('SUC02', 'プロフィールを変更しました！');
define('SUC03','スイーツが登録されました！');

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

    //====================================
    //パスワードチェック
    //====================================
    function validPass($str, $key){
    //半角英数字チェック
    validHalf($str, $key);
    //最大文字数チェック
    validMaxPass($str, $key);
    //最小文字数チェック
    validMinPass($str, $key);
    }
    
    //最大文字数チェック 
    //関数を呼び出す時に指定すれば$maxの値を変えることが可能
    function validMaxComment($str,$key,$max=100){ 
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
    function validMaxName($str,$key,$max=15){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06C;
        }
    }
    //パスワード系統バリデーション
    function validMinPass($str,$key,$min=6){
        if(mb_strlen($str) < $min){
            global $err_msg;
            $err_msg[$key] = MSG05A;
        }
    }
    function validMaxPass($str,$key,$max=15){ 
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG05B;
        }
    }
    function validMatchPass($str1,$str2,$key){
        if($str1 !== $str2){
            global $err_msg;
            $err_msg[$key]= MSG05C;
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

    //半角数字チェック=>スイーツの金額のバリデーションに使う
    function validNumber($str, $key){
    if(!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG17;
        }
    }
    //画像必須バリデーション(pic1のみ)
    function validRequiredPic($str, $key){
        if($str === ''){ 
        global $err_msg; 
        $err_msg[$key] = MSG01B;
        }
    }

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
    
            //クエリ成功の場合 
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
    //お気に入りスイーツ情報取得関数 mypage.phpで使用
    //=============================================
    function getMyFavorite($u_id){
        debug('自分のお気に入り登録したスイーツの情報を取得します');
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
                return $stmt -> fetchAll();
            }else{
                return false;
            }
        

        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
        }
    }


    //===================================================
    // お気に入りスイーツ取得関数(ページネーションあり) mypage.phpで使用
    //  1,sweetsテーブルとfavoriteテーブルを結合し、お気に入りの総数と総ページ数を取得
    //  2,sweetsテーブルとfavoriteテーブルを結合し、$listSpan
    //===================================================
    function getFavSweetsList($currentMinNum=1, $listSpan, $u_id){
        //例外処理開始
        try{
            //デバッグ
            debug('mypage.phpでお気に入り登録されているスイーツを表示します');
            //SQL準備
            $dbh = dbConnect();
            //sqlが通らない場合はLIMIT以降をはずして別にsqlを作ってみる
            $sql = 'SELECT * FROM favorite AS f LEFT JOIN  sweets AS s ON f.sweets_id = s.id WHERE f.user_id = :u_id';
            $data = array(':u_id'=> $u_id);
            $stmt = queryPost($dbh, $sql, $data);
            //この2つのデータが必要 dbFavSweetsData['total'],['total_page']として呼び出せる
            $rst['total'] = $stmt->rowCount();
            $rst['total_page'] = ceil($rst['total']/$listSpan);
            debug('rstの[total]の結果:'.print_r($rst['total'], true));
            debug('rstの[total_page]の結果'.print_r($rst['total_page'], true));
            //クエリ判定
            if($stmt){
                debug('getFavSweetsListクエリ-1に成功しました');
            }else{
                debug('getFavSweetsListクエリ-1に失敗しました');
                return false;
            }

            //========================================================
            //お気に入りスイーツの総数と総ページ数を取得した後に1ページに表示する値を取得する
            //========================================================
            
            //$sql= 'SELECT * FROM favorite WHERE user_id = :u_id LIMIT '.$listSpan.' OFFSET '.$currentMinNum;
            $sql= 'SELECT * FROM favorite AS f LEFT JOIN sweets AS s ON f.sweets_id = s.id WHERE f.user_id = :u_id LIMIT '.$listSpan.' OFFSET '.$currentMinNum;
            $data = array('u_id'=>$u_id);
            $stmt = queryPost($dbh,$sql,$data);
            //クエリ判定
            if($stmt){
                //$rst['data']は1ページに表示する分だけが入っている
                $rst['data'] = $stmt -> fetchAll();
                debug('getFavSweetsListクエリ-2に成功しました');
                debug('クエリ結果の全レコードを取得しました。');
                debug('$rst[data]の結果:'.print_r($rst['data'],true));
                return $rst;
            }else{
                debug('getFavSweetsListクエリ-2に成功しました');
                return false;
            }
        //例外処理
        }catch(Exception $e){
            error_log('エラー発生：'.$e->getMessage());
        }
    }
    //=============================================
    //スイーツ情報取得関数 registSweets.phpで使用
    //=============================================
    function getRegistSweets($u_id, $s_id){
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

    //====================================================================
    //== スイーツのリスト取得関数   home.phpで使用　$categoryと$sortは商品の絞り込みと並び替えで使う
    //====================================================================
    function getSweetsList($currentMinNum, $category, $sort, $listSpan){
        //デバッグ
        debug('getSweetsListで商品情報を取得します。');
        debug('引数の$sortの中身'.print_r($sort,true));
        
        //DB処理
        try{
                //DBへ接続
                $dbh = dbConnect();

            //=============================================
            //SQL1 総件数と件ページ数用のSQLを作成
            //=============================================
                /*1-1sweetsテーブルからidを取得しスイーツが何件あるかを判別する
                    home.phpの$currentMinNumと$dbSweetsDataに値を与える*/
                //カテゴリーと値段による絞り込み、並び替えを実装。
                $sql = ' SELECT id FROM sweets '; 
                if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
                if(!empty($sort)){
                    debug('home.phpで$sortが確認できました。');
                    switch($sort){
                        case 1:
                            debug('値段が安い順に並び替えました');
                            $sql .=  ' ORDER BY price ASC ';
                            break;
                        case 2:
                            debug('値段が高い順に並び替えました');
                            $sql .= ' ORDER BY price DESC ';
                            break;
                    }
                }else{
                    debug('値段よる並び替えはありません');
                }
                $data = array();
                $stmt = queryPost($dbh,$sql,$data);
                //$rstのreturnは下の$rst['data']を定義してから行う(2度returnを行おうとするとエラーになる)
                //総レコード数 home.phpで echo dbSweetsData['total']; として呼び出す
                $rst['total'] = $stmt->rowCount(); 
                $rst['total_page'] = ceil($rst['total']/$listSpan); //総ページ数  home.phpで echo dbSweetsData['total_page']; として呼び出す
                //デバッグ
                debug('スイーツの総件数です:'.print_r($rst['total'],true));
                debug('home.phpの総ページ数:'.print_r($rst['total_page'],true));
                
                //クエリが失敗した場合
                if(!$stmt){
                    debug('getSweetsListのSQLは失敗しました');
                    return false;
                }else{
                    debug('getSweetsListのSQLは成功しました');
                }


            //===========================================//
            //SQL2 ページング用のSQLを作成
            //  1ページに表示する 画像の件数を$listSpanと$currentMinNumを定義し
            //  画像のデータ$rst['data']をhome.phpにreturnする
            //===========================================//
                //2-1 商品データを全て取得
                $sql = 'SELECT * FROM sweets';
                
                //2-2 カテゴリーがあった場合、並び替えを実行する
                if(!empty($category)){
                    $sql .= ' WHERE category_id = '.$category;
                } 
                //2-3 $sortがあった場合、価格順で並び替えられるようにする
                if(!empty($sort)){
                    switch($sort){
                    case 1:
                        $sql .= ' ORDER BY price ASC';
                        debug('値段が安い順に並び替えました');
                        break;
                    case 2:
                        $sql .= ' ORDER BY price DESC';
                        debug('値段が高い順に並び替えました');
                        break;
                    }
                }

                //2-3 商品が何件取得できたかを判別するSQL
                //$listSpanはこの関数の()内で、$currentMinNumはhome.phpで定義している
                $sql .= ' LIMIT '.$listSpan.' OFFSET '.$currentMinNum;
                $data = array();
                debug('ページング用SQLの結果：'.$sql);

                // クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                
                
                //dataには画像のデータが入っている
                    // [0] => Array
                    // (
                    //     [id] => 62
                    //     [name] => ケーキB
                    //     [store_name] => ケーキや
                    //     [category_id] => 1
                    //     [comment] => $pic1の2つの処理の順番を逆にした
                    //     [price] => 333
                    //     [pic1] => upload_img/4ca4b5b3cfc9e10cbafc2f9fcaa86ba574da1ea7.jpeg
                    //     [pic2] => 
                    //     [pic3] => 
                    //     [user_id] => 6
                    //     [delete_flg] => 0
                    //     [create_date] => 2019-08-09 13:58:58
                    //     [update_date] => 2019-08-09 22:58:58
                    // )
                if($stmt){
                    //home.phpで echo dbSweetsData['data']; として呼び出す
                    $rst['data'] = $stmt->fetchAll(); 
                    return $rst;
                    debug('クエリに成功しましたが');
                    debug('画像のデータ一覧が入っている$rst[data]の中身'.print_r($rst['data'],true));
                }else{
                    debug('クエリに失敗しましたが');
                    debug('画像のデータと総レコード数と総ページ数が取得できませんでした');
                    return false;
                }
            //3 例外処理
            }catch(Exception $e){
                error_log('getSweetsListでエラー発生:'.$e->getMessage());
            }
        }
    

    //=========================================================
    //== 自分の登録したスイーツの情報取得関数   myRegistSweets.phpで使用
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
                //総レコード数 home.phpで echo getSweetsData['total']; として呼び出す
                $rst['total'] = $stmt->rowCount(); 
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
    //=============================================
    // スイーツの情報取得関数   SweetsDetail.phpで使用 
    //  Sweetsテーブルとカテゴリーテーブルを外部結合してスイーツのカテゴリーも含めて情報を入手している。
    //=============================================
    function getSweetsDetail($s_id){
        debug('スイーツ情報を取得します');
        debug('getSweetsDetailのスイーツID:'.$s_id);

        try{
            //DB接続
            $dbh = dbConnect();
            
            //categoryテーブルから取ってくるデータはASで変換するようにsweetsテーブルのidとcategoryテーブルのidが同じになる可能性がある
            $sql =
                'SELECT s.id, s.name, s.store_name, s.category_id, s.comment, s.price, s.pic1, s.pic2, s.pic3, s.user_id, s.delete_flg, s.create_date, s.update_date, c.id AS category_id, c.name AS category_name
                FROM sweets AS s LEFT JOIN category AS c ON s.category_id = c.id WHERE s.id = :s_id AND s.delete_flg = 0 AND c.delete_flg = 0';

            //プレースホルダー
            $data = array(':s_id'=>$s_id);
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);
            debug('getSweetsDetailのクエリ実行');
            //クエリ成否判定
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

    //===============================================
    //自分の出品したスイーツの情報取得関数 myRegistSweets.phpで使用
    //===============================================
    function getMySweets($u_id){
        debug('自分の出品したスイーツ情報のを取得します');
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

        //取得した掲示板のクエリ結果を変数に入れる
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
                //取得したメッセージのクエリ結果をに入れる
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
    //サニタイズ関数 
    //=============================================
    
    //DBからデータを取って来て画面に表示する際などに不正な値を取り除く。
    function sanitize($str){
        return htmlspecialchars($str,ENT_QUOTES); //ENT_QUOTES =>
    }

    //=============================================
    //フォーム入力保持関数 profEdit.phpとregistSweets.phpで使用  
    //=============================================
    // フォーム入力保持
    function getFormData($str, $flg = false){
    
    //呼び出し元の送信がGETかPOSTか判定する
    if($flg){
        $method = $_GET;
        debug('getFormDataの判定はGET送信です');
    }else{
        $method = $_POST;
        debug('getFormDataの判定はPOST送信です');
    }
    //使う変数をグローバル宣言
    global $dbFormData;
    global $err_msg;

    //DBにデータがある場合
    if(!empty($dbFormData)){
        //フォームにエラーがある場合(バリデーションに引っかかる)
        if(!empty($err_msg[$str])){
            //フォームにデータがある場合
            if(isset($method[$str])){
                debug('結果1です');
                return sanitize($method[$str]);
            
            //ない場合（基本ありえない）はDBの情報を表示
            }else{
                debug('結果2です');
                return sanitize($dbFormData[$str]);
            }

        //フォームにエラーがない場合
        }else{
            //POSTにデータがあり、DBの情報と違う場合
            //出品したスイーツからregistSweets.phpに飛んで情報を更新した時。
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                debug('結果3です');
                return sanitize($method[$str]);
            //POSTにデータがあり、DBの情報と違う場合
            //出品したスイーツからregistSweets.phpに飛んだ時
            }else{
                debug('結果4です');
                return sanitize($dbFormData[$str]);
            }
        }   

    //dbFormDataが現在のページに存在しない場合(初めて入力するデータの場合)
    }else{
        if(isset($method[$str])){
            debug('結果5です');
            return sanitize($method[$str]);
        }
    }
    }
    //============================================
    //カテゴリーデータ入手関数 registSweets.phpで使用
    //============================================
    function getCategoryData(){
        //デバッグ
        debug('カテゴリーデータを入手します');
        //トライキャッチ
        try{
            //DBへ接続
            $dbh = dbConnect();
            //SQLを作成
            $sql = 'SELECT * FROM category';
            //tocheck: $dataは必要？ =>queryPostの
            $data = array();
            //クエリを関数に入れて変数に入れる
            $stmt =queryPost($dbh, $sql,$data);
            //クエリ成否判定
            if($stmt){
                //クエリデータを返却(fetchAll();で)
                return $stmt->fetchAll();
            }else{
                return false;
            }
        }catch(Exception $e){
            error_log('エラーが発生しました'.$e->getMessage());
        }
    }
    
    //=============================================
    //メール送信関数 
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
    //エラーメッセージ表示関数 
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
    //認証キー作成  追加パスワードリマインダーの認証キー発行 ト
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
    //固定長チェック関数 
    //=============================================
    function validLength($str,$key,$len = 8 ){ 
        if(mb_strlen($str) !== 8){
            global $err_msg;
            $err_msg[$key] = $len.MSG14;//8文字で入力してくださいとなる
        }
    }

    //=============================================
    //画像処理関数 
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
                    //in_arrayで調べた$typeの中身が第2引数に指定した三つに一致しなかった場合
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
                chmod($path,0644); 
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
    //=================================
    // 画像削除関数
    //=================================
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
    //============================================================
    function pagenation( $currentPageNum, $totalPageNum, $category, $sort, $link = '', $pageColNum = 5){

        // 現在のページが、総ページ数と同じかつ総ページ数が表示項目数以上なら、左にリンク４個出す
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
        // それ以外は左に2個出す。
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
                echo ' "><a class="list__item__link" href=" ?p='.$i.$link.'&c_id='.$category.'&sort='.$sort.' "> '.$i.'</a></li>';
            }

            //現在のページが最大ページではなく、最大ページが1以上の場合は進むボタン(>)を表示する
            if($currentPageNum != $maxPageNum && $maxPageNum > 1){
                echo '<li class="list__item"><a class="list__item__link" href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
            }
            echo '</ul>';
        echo '</div>';
    }

    //============================================================
    //mypage.phpのお気に入り商品のページング
    //============================================================
    //== home.phpの出品された商品のページング処理関数 ==///
    // $currentFavPageNum : 現在のページ数
    // $totalFavPageNum : 総ページ数
    // $link : 検索用GETパラメータリンク
    // $pageColNum : ページネーション表示数
    function favPagenation( $currentFavPageNum, $totalFavPageNum, $link = '', $pageColNum = 5){
        //デバッグ
        debug('現在のお気に入りのページ数:'.print_r($currentFavPageNum, true));
        debug('お気に入りのページ数の総数:'.print_r($totalFavPageNum, true));

        // 現在のページが、総ページ数と同じかつ総ページ数が表示項目数以上なら、左にリンク４個出す
        if( $currentFavPageNum == $totalFavPageNum && $totalFavPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum - 4;
            $maxPageNum = $currentFavPageNum;
            debug('結果5です');
        // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
        }elseif( $currentFavPageNum == ($totalFavPageNum-1) && $totalFavPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum - 3;
            $maxPageNum = $currentFavPageNum + 1;
            debug('結果4です');
        // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
        }elseif( $currentFavPageNum == 2 && $totalFavPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum - 1;
            $maxPageNum = $currentFavPageNum + 3;
            debug('結果3です');
        // 現ページが1の場合は左に何も出さない。右に５個出す。 $totalFavPageNum = 5  $pageColNum = 
        }elseif( $currentFavPageNum == 1 && $totalFavPageNum >= $pageColNum){
            $minPageNum = $currentFavPageNum;
            $maxPageNum = 5;
            debug('結果2です');
        // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
        }elseif($totalFavPageNum < $pageColNum){
            $minPageNum = 1;
            $maxPageNum = $totalFavPageNum;
            debug('結果1です');
        //それ以外は左に2個出す。
        }else{
            $minPageNum = $currentFavPageNum - 2;
            $maxPageNum = $currentFavPageNum + 2;
            debug('結果0です');
        }
        //実際に吐き出すページネーションの中身
        echo '<div class="pagenation">';
            echo '<ul class="pagenation__list">';
            if($currentFavPageNum != 1){
                echo '<li class="list__item"><a class="list__item__link" href="?fav_p=1'.$link.'">&lt;</a></li>';
            }
            for($i = $minPageNum; $i <= $maxPageNum; $i++){
                echo '<li class="list__item ';  
                if($currentFavPageNum == $i ){ echo 'active'; } 
                echo ' "><a class="list__item__link" href=" ?fav_p='.$i.$link.' "> '.$i.'</a></li>';
            }
            if($currentFavPageNum != $maxPageNum && $maxPageNum > 1){
                echo '<li class="list__item"><a class="list__item__link" href="?fav_p='.$maxPageNum.$link.'">&gt;</a></li>';
            }
            echo '</ul>';
        echo '</div>';
    }
    
    //=========================================
    //画像表示用関数
    //=========================================
    function showImg($path){
        if(empty($path)){
            return '../../../../portfolio_sweetsboard/src/img/sample_img1.jpg';
        }else{
            return $path;
        }
    }

    //=============================================
    //GETパラメータ付与 
    //=============================================
    // $del_key : 付与から取り除きたいGETパラメータのキー
     //=>productDetail.phpではs_id(スイーツのID)を取り除いてページ数のGETパラメータだけを付与するようにしている。
    function appendGetParam($arr_del_key = array()){
        //GETパラメータが存在した場合(URLに?をつけるので$strに格納する)
        if(!empty($_GET)){ 
            $str = '?';
            /*GETパラメータをkey=>valの形で分解して
            取り除きたいパラメータ($arr_del_key)と$keyを判別*/
            foreach($_GET as $key => $val){ 
                //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
                if(!in_array($key,$arr_del_key,true)){ 
                    // => ?$key=$val& となる。(?sweets_id=1)など
                    $str .= $key.'='.$val.'&'; 
                }
            }
            // $arr_del_keyを取り除いた時に ? も余るのでそれを取り除く
            $str = mb_substr($str, 0, -1, "UTF-8"); 
            //echoにするとhome.phpでスイーツの画像をクリックした時にGETパラメータがおかしくなる =>?p_idの処理に移ってしまう
            //echoからreturnに修正すると逆に今までechoにしていたところに影響が出るので関数を使っていたところを再度テストチェックするようにしよう
            return $str;
        }
    }

    //=============================================
    //ログイン認証関数  
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
    //お気に入り登録関数  
    //=============================================
    function isFav($u_id, $s_id){

        //デバッグ
        //print_rで$u_idや$s_idをチェックすると中身が１になるので注意
        debug('isFav関数でお気に入り情報があるか確認します');
        debug('自分のユーザーID:'.$u_id); 
        debug('スイーツID:'.$s_id);
        //DB処理
        try{
            $dbh = dbConnect();
            
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
            error_log('isFav関数でエラー発生:'.$e->getMessage() );
        }
    }


    //================================================//
    //==== DB接続系の関数 ==============================//
    //================================================//

    //DB接続関数(Xserver接続時)
    //function dbConnect(){
    //    //DBの接続準備 xserverとxsurvどちらが正解？
    //    $dsn = 'mysql:dbname=reol0405_sboardappdb;
    //                host=mysql8050.xserver.jp;
    //                charset=utf8';
//
    //    $user = 'reol0405_mysql28';
    //    $password = 'harenn28';
    //    $options = array(
    //        // SQL実行失敗時にはエラーコードのみ設定。ここを変えてエラーを特定できることもある
    //        //tocheck: 開発の時にはERRMODE_EXCEPTIONにし、サーバーに公開時にはERRMODE_SILENTにする
    //        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //        // デフォルトフェッチモードを連想配列形式に設定
    //        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //        // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    //        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    //        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    //    );
    //    $dbh = new PDO($dsn, $user, $password, $options);
    //    return $dbh; 
    //}

    //DB接続関数(localhostで開発時)
    function dbConnect(){
        //DBの接続準備 xserverとxsurvどちらが正解？
        $dsn = 'mysql:dbname=boardapp;host=localhost;charset=utf8';
        $user = 'root';
        $password = 'root';
        $options = array(
            // SQL実行失敗時にはエラーコードのみ設定。ここを変えてエラーを特定できることもある
            //tocheck: 開発の時にはERRMODE_EXCEPTIONにし、サーバーに公開時にはERRMODE_SILENTにする
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // デフォルトフェッチモードを連想配列形式に設定
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
            // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        );
        $dbh = new PDO($dsn, $user, $password, $options);
        return $dbh; 
    }

    //クエリ関数変更後（getUser関数や）
    //クエリの成功、失敗の判定はこの関数内で行う
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
                return 0; 
            }
            //成功した場合
            debug('クエリは成功しました');
            debug('成功したSQL:'.print_r($stmt,true));
            return $stmt; 
    }
?>
