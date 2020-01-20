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


