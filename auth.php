<?php 
//============ ログイン認証・自動ログアウト機能 ====================//
//ログインしている場合
    if(!empty($_SESSION['login_date'])){
        //デバッグ
        debug('ログイン済みユーザーです');
    
        //現在日時が（ 最終ログイン日時+有効期限 ）を超えていた場合
        if(  ($_SESSION['login_date'] + $_SESSION['login_limit']) < time() ){
            debug('ログイン有効期限オーバーです');

            //セッションを削除(ログアウトする)
            session_destroy();

            //ログインページへ
            header("Location:new.php");

        //超えていなかった場合
        }else{
            //デバッグ
            debug('ログイン有効期限内です');

            //最終ログイン日時を現在日時に更新する
            $_SESSION['login_date'] = time();
            
            //ログイン有効期限内で現在いるページがログインページの場合のみマイページへ遷移する
            if(basename($_SERVER['PHP_SELF']) === 'login.php'){
                debug('マイページへ遷移します');    
                header("Location:mypage.php");            
            }
        }
    //そもそもログインしていなかった場合 $_SESSION['login_date']がemptyの場合
    }else{
        //デバッグ
        debug('未ログインユーザーです');
        //ログインしていない状況でログインページ以外の場所のみlogin.phpへリダイレクトする
        // !=='new.php'条件をつけないとnew.phpにログインしていない状態でアクセスした時にリダイレクトしてしまう
        if( basename($_SERVER['PHP_SELF']) !== 'new.php' ){
            debug('new.php以外です');
            header("Location:new.php"); 
        }
    }