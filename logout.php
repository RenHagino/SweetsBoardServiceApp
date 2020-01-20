<?php

//共通変数・関数ファイルを読込み
require('function.php');


debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログアウトページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

debug('ログアウトします。');

// セッションを削除（ログアウトする）
session_destroy();

debug('ログインページへ遷移します。');

// ログインページへ
header("Location:login.php");

//削除方法は複数ある
//$_SESSION = array(); //
//session_unset(); //セッション変数の中身を消すものでセッション変数本体は残るので同じIdでアクセス可能
//session_destroy(); //セッション変数と共にセッションIDも消すので再度同じIDでアクセスはできない
?>