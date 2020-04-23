# ***サービス名***
  SweetsBoardService
# ***動作確認用URL***
  ### URL
  =>http://sweetsboardservice.com/new.php
  ### テストログイン用アカウント<br>
    メールアドレス:slikeyuka100@gmail.com
    パスワード:saitouy0202
  
# ***サービス概要***
  このサービスは自分が食べたお気に入りのスイーツを他のユーザーに紹介したり、他のユーザーが紹介したスイーツをお気に入り登録できるサービスです。
  
# ***機能一覧***<br>
 ### 1.ユーザー登録機能(signup.php)<br>
 ### 2.ログイン機能(login.php)<br>
 ### 3.ログアウト機能(logout.php)<br>
 ### 4.退会機能(withdraw.php)<br>
 ### 5.プロフィール編集機能(profEdit.php)<br>
 ### 6.パスワード変更機能(passEdit.php)<br>
 ### 7.スイーツ投稿機能(regitSweets.php)<br>
 ### 8.スイーツ一覧表示機能(home.php)<br>
 ### 9.スイーツ並び替え機能(値段とカテゴリー)(home.php)<br>
 ### 10.スイーツお気に入り登録機能(sweetsDetail.php)<br>
  
# ***開発環境、使用言語一覧***<br>
 1.HTML5<br>
 2.CSS3<br>
 3.Sass(scss記法)<br>
 4.jquery ver3.4.1<br>
 5.PHP ver7.3.7<br>
 6.MySQL ver2.4.41<br>
 
 ***パッケージ関連***<br>
 1.npm ver6.13.4<br>
 2.gulp ver4.0.2<br>
 3.node ver13.6.4<br>
 

# ***各画面の機能***<br>
## 1.新規訪問ページ(new.php)<br>
    ログインしていない状態でサイトに接続すると表示画面。ログインボタンと会員登録ボタンが存在し、クリックすると該当するページに遷移する。

## 2.ユーザー登録画面(signup.php)<br>
    新しく会員登録するページ。ユーザー登録に必要な情報を入力して登録ボタンを押すとマイページ(mypage.php)へ画面遷移する。
    登録した情報はマイページ(mypage.php)の左にあるサイドバーからプロフィール編集画面(profEdit.php)へ移動すると確認、編集することが可能

## 3.ホーム画面(home.php)<br>
    ・スイーツ登録画面(registSweets.php)で登録したスイーツが一覧表示されている。
    ・左のサイドバーから値段順を選択した状態で検索ボタンを押すと現在表示されているスイーツが選択した値段順で並び替えられる
      左のサイドバーからカテゴリーを選択した状態で検索ボタンを押すとカテゴリーに当てはまるスイーツのみが表示される。
      値段とカテゴリーは同時選択可能であり、スイーツをregistSweets.phpで投稿するときに投稿者が決められる。
    ・全ユーザーが投稿したスイーツが12件以上になると数字の書かれたブロックのページネーションボタンが画面下部に出現する。
      現在ユーザがみているページのボタンは黒色で表示され、それ以外のページのボタンはグレー色で表示される。
      現在見ているページ以外のボタンを押すとそのページへ移動し、移動前に見ていたスイーツ12件とは別のスイーツ12件が表示される
    ・表示されているスイーツの画像をクリックするとスイーツ詳細画面(SweetsDetail.php)へ遷移することができる

## 4.ログイン画面(login.php)<br>
    ・会員登録時(signup.php)に入力したメールアドレスとパスワードを入力し、両方が合っていればログイン成功となりマイページへ遷移する。
    ・ログアウトについては画面は用意せず、各画面の上部にあるヘッダーの右端にあるログアウトボタンを押すとログアウトできる

## 6.退会画面(withdraw.php)<br>
    ・退会画面を表示し、「退会する」ボタンを押した場合、新規訪問ページ(new.php)に遷移し、ログイン状態が解除される。
    ・退会したユーザーが使っていたメールアドレスとパスワードはログイン画面(login.php)では使えなくなっている。
   
## 7.パスワード変更画面(paddEdit.php)<br>
    ・パスワード変更画面では現在使っているパスワードと新規に登録したいパスワードを入力するとパスワードが入力した値に変更され、マイページ(mypage.php)に遷移する
    
## 8.マイページ画面(mypage.php)<br>
    ・マイページは他の画面に遷移するためのサイドバー(sidebar.php)とお気に入り登録されたスイーツの表示欄で構成されている。
    ・ホーム画面(home.php)からスイーツ詳細画面(sweetsDetail.php)に遷移し、そこでお気に入り登録ボタン（ハートマーク)を押したスイーツはここに表示される

## 9.投稿スイーツ確認画面(myRegistSweets.php)<br>
    ・投稿スイーツ確認画面では自分がスイーツ登録画面(registSweets.php)で登録したスイーツの一覧が表示される。
    ・既に投稿したスイーツの画像をクリックするとスイーツ登録（編集)画面に遷移し、一度登録したスイーツ名や値段、詳細などを編集することができる。
    
 ## 10.プロフィール編集画面(profEdit.php)<br>
    ・プロフィール編集画面では各項目(名前、住んでいる場所、メールアドレス、プロフィール画像)を入力し、設定ボタンを押すと更新され、マイページへ遷移する。
    
 ## 11.スイーツ登録(編集)画面(registSweets.php)<br>
    ・スイーツ登録画面ではスイーツの各項目を入力し、バリデーションを全て通過した場合は新規にスイーツが登録され、マイページ(mypege.php)に遷移する。
    ・登録したスイーツは投稿スイーツ確認画面(myRegistSweets.php)とホーム画面(home.php)で確認することができる。
    
 ## 12.スイーツ詳細画面(SweetsDetail.php)<br>
    ・ホーム画面(home.php)からスイーツの画像をクリックすると、この画面に遷移し、クリックしたスイーツの詳細画像を確認できる。
    ・画像2と画像3が登録されている場合は他の画像をクリックすると一番大きく表示されるメイン画像が入れ替わる。
    ・画面下部にはホーム画面(home.php)とマイページ(mypage.php)に遷移するためのボタンが設置されており、クリックするとそれぞれの画面に遷移する。
    
# ***画面を持たないもしくは他の画面を構成するファイルの機能***<br>
 ### 1.ヘッド(head.php)<br>
 ### 2.ヘッダー(header.php)<br>
 ### 3.フッター(footer.php)<br>
 ### 4.ログイン認証(auth.php)<br>
 ### 5.ログアウト(logout.php)<br>
 ### 6.お気に入り登録通信(ajaxLike.php)<br>
 
 
  
  
