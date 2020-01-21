###エラー解決

###スイーツボードのアカウント管理
    １ reol0405@gmail.com  harenn28
    ２ test0000@gmail.com test28
    ３ test0001@gmail.com test128 　どら焼きを出品
##1 mysqlをアップデートしたい
    phpMyadminの管理画面内で'SELECT version();'を実行
    #現在のバージョン
    mysql：5.7.25
    Apache/2.2.34
    (Unix) mod_wsgi/3.5
    Python/2.7.13
    PHP/7.3.1 
    mod_ssl/2.2.34 
    OpenSSL/1.0.2o DAV/2 mod_fastcgi/mod_fastcgi-SNAP-0910052141 
    mod_perl/2.0.9 
    Perl/v5.24.0 configured -- resuming normal operations

    アップグレード後 MAMPごとアップグレードすることに

#現在のmysqlの状態 
##$brew info mysql
    mysql: stable 8.0.17 (bottled)
    Open source relational database management system
    https://dev.mysql.com/doc/refman/8.0/en/
    Conflicts with:
        mariadb (because mysql, mariadb, and percona install the same binaries.)
        mariadb-connector-c (because both install plugins)
        mysql-cluster (because mysql, mariadb, and percona install the same binaries.)
        mysql-connector-c (because both install MySQL client libraries)
        percona-server (because mysql, mariadb, and percona install the same binaries.)
    /usr/local/Cellar/mysql/8.0.17 (284 files, 272.5MB) *
        Poured from bottle on 2019-08-07 at 18:50:38
    From: https://github.com/Homebrew/homebrew-core/blob/master/Formula/mysql.rb
    ==> Dependencies
    Build: cmake ✘
    Required: openssl ✔
    ==> Requirements
    Required: macOS >= 10.10 ✔
    ==> Caveats
    We've installed your MySQL database without a root password. To secure it run:
        mysql_secure_installation

    MySQL is configured to only allow connections from localhost by default

    To connect run:
        mysql -uroot

    To have launchd start mysql now and restart at login:
      brew services start mysql
    Or, if you don't want/need a background service you can just run:
      mysql.server start
    ==> Analytics
    install: 72,140 (30 days), 194,159 (90 days), 807,172 (365 days)
    install_on_request: 67,455 (30 days), 182,310 (90 days), 750,735 (365 days)
    build_error: 0 (30 days)
    renmac:~ haginoren$ 
## $brew link mysql
    Warning: Already linked: /usr/local/Cellar/mysql/8.0.17
    To relink: brew unlink mysql && brew link mysql
## $mysql --version
    mysql  Ver 8.0.17 for osx10.14 on x86_64 (Homebrew)
## $which mysql
    /usr/local/bin/mysql

## $sudo mysql_upgrade -u root -p => mysql_upgrade は mysql8.0.17から使えなくなったから下の $mysql.server start --upgrade=FORCEを使う。
    The mysql_upgrade client is now deprecated. The actions executed by the upgrade client are now done by the server.
    To upgrade, please start the new MySQL binary with the older data directory. Repairing user tables  is done automatically. Restart is not required after upgrade.
    The upgrade process automatically starts on running a new MySQL binary with an older data directory.
    To avoid accidental upgrades, please use the --upgrade=NONE option with the MySQL binary. The option   --upgrade=FORCE is also provided to run the server upgrade sequence on demand.
    It may be possible that the server upgrade fails due to a number of reasons. In that case, the  upgrade sequence will run again during the next MySQL server start. If the server upgrade fails  repeatedly, the server can be started with the --upgrade=MINIMAL option to start the server without  executing the upgrade sequence, thus allowing users to manually rectify the problem.
### $mysql.server start --upgrade=FORCE
    




### == お気に入り機能を作ろう ==== ###

###data属性とは？
htmlにつけられる属性のこと。任意の名前をつけ、その属性に値を持たせることができる。
data属性で持たせた値は主にjsで取得して使用する。
###data属性の書き方
# <div class="product_id" data-.p_id="abc111">商品のIDです</div>
# jsで取得 => var product_id = $(.product_id).data('p_id') 

###お気に入り機能の処理フロー
#１　クリックしたhtmlのdata属性から商品IDを取得

#２　商品IDをAjaxでDBへ登録 （すでに登録されていた場合DBのお気にりテーブルから削除

#３　クリックしたhtmlのスタイルを変更する

###主な変更点
#1　footer.phpにAjax通信の処理を記述
#2　Ajax-like.phpという新規はファイルの作成
#3　function.phpにログイン認証関数を追加（ function isLogin() ）


###追加したい機能のタスク
    ・お気に入りしたスイーツをマイページで確認できるようにする！
    ・
###解決したいエラー
    ・退会したユーザーのメールアドレスが再度登録の時に使えなくなっている =>解決
    ・プロフィール編集画面で年齢を変更しても別の年齢にランダム設定される（profEdit.phpでフォームのtypeをnumberした時だけ起こる。text型にすると起こらないバグ（謎だらけ） ）
    ・

