=========================
Gulpについての特徴
=========================
gulp.series(...tasks) ... 引数のタスクを順番に処理をする APIドキュメント
gulp.parallel(...tasks) ... 引数のタスクを並列に処理をする APIドキュメント


===============================
環境構築で遭遇したエラー
===============================

１gulp-sassを使うとエラー
  =>status: 1
    file: /Applications/MAMP/htdocs/portfolio_sweetsboard/src/sass/style.scss
    line: 14
    column: 13
    formatted: Error: File to import not found or unreadable: sass/object/component/form.scss.
        on line 14 of src/sass/style.scss
        @import 'sass/object/component/form.scss';
    解決
    =>style.scssで@importするパス指定方法が問題だった
    //前：@import 'src/sass/foundation/variables.scss';

    //後@import '../../src/sass/foundation/variables.scss';

２

INSERT into sweets ( name, store_name, category_name, price, comment,  user_id, create_date)
values ('ショコラケーキ', 'ケーキショップ', 'ケーキ', 200, '美味しいよ',  1, 2020-01-13 14:50:21);