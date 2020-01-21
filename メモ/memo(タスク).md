===========================
やり残したこと
===========================

１マイページ(mypage.php)に掲示板履歴を貼り付ける
  src
  =>
  <!--セクション２ 掲示板-->
                <section class="list board-list ">
                    <h2 class="list__title">
                        掲示板リスト
                    </h2>
                    <table class="list__table">
                    <thead>
                        <tr>
                            <th>最新送信日時</th>
                            <th>取引相手</th>
                            <th>メッセージ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(!empty($boardData)){
                                foreach($boardData as $key => $val){
                                    if(!empty($val['msg'])){
                                        //TODO: array_shftを外してみる
                                        $msg = array_shift($val['msg'])
                        ?>
                            <tr>
                                <!--TODO:strtotimeを外してみる-->
                                <td><?php echo sanitize(date('Y.m.d H:i:s', strtotime($msg['send_date']))); ?></td>
                                <td>・・　・・</td>
                                <!--メッセージのIDとメッセージの内容を表示する-->
                                <td>
                                    <a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">
                                        <?php mb_substr(sanitize($msg['msg']),0,40);?>...
                                    </a>
                                </td>
                            <tr>
                        <?php
                        }else{
                        ?>
                            <tr>
                                <td>ーー</td>
                                <td>・・　・・</td>
                                <td><a href="msg.php?m_id=<?php  echo sanitize($val['id']); ?>">まだメッセージはありません</a></td>
                            <tr>
                        <?php
                                }
                            }
                        }
                        ?>
                    </tbody>
                </section>
  //


２スイーツ詳細画面(sweetsDetail.php)に購入ボタンを実装し、
    そこから掲示板機能を実装してそこに飛ばせるようにする。

    //sweetsDetail.phpに書くしょり
    //== POST送信処理 == /////////////////////////////////////
    if(!empty($_POST)){
        debug('POST送信があります');

        //ログイン認証 =>POST送信前に行なってはいけない
        require('auth.php');

        //例外処理
        try{
            //DB接続
            $dbh = dbConnect();

            //SQL作成
            $sql = 'INSERT INTO board (sale_user, buy_user, sweets_id, create_date) VALUES (:s_uid, :b_uid, :s_id, :date )';
            //プレースホルダー
            $data = array( 's_uid'=>$viewData['user_id'], ':b_uid'=>$_SESSION['user_id'], ':s_id'=>$s_id, ':date'=>date('Y-m-d H:i:s') );
            
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);

            //クエリ成功の場合
            if($stmt){
                $_SESSION['msg-success'] = SUC05;//登録した人とチャットをしたみよう！というメッセージを入れる
                debug('連絡掲示板へ遷移します');
                //掲示板へ取得 //TODO: b_id(board_id)はどこから取得？ lastInsetIDは$sqlでbordテーブルに接続した時に取得している。
                header("Location:msg.php?b_id=".$dbh->lastInsertId());
            }
        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }

3スイーツ登録画面(registSweets.php)から登録するとき、
    画像が選択されなかった場合はデフォルト画像が登録されるようにする
    デフォルト画像は src/img/に保存する。



//===============================================
//task4 myRegistSweets.phpで使う関数を完成させる
//===============================================
    //==========================================================
    //== 自分の登録したスイーツの情報取得関数  
    //function getFavSweetsList($currentMinNum=1, $u_id, $span = 6){
    //    //デバッグ
    //    debug('自分のお気に入りスイーツ情報を取得します。');
    //    
    //    //DB処理
    //    try{
    //        //==  SQL1 カテゴリ選択用の処理  １ == //
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
    //        //==== SQL2 検索機能用のコード 検索機能 ２ =========//
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
