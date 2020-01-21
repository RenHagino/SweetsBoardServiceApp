//gulpと必要なパッケージををrequireする
gulp = require('gulp');
var minify_css = require('gulp-minify-css');
var sass = require('gulp-sass');
var imagemin = require('gulp-imagemin');
var changed = require('gulp-changed');
var cleanCSS = require("gulp-clean-css");
var rename   = require("gulp-rename");




//=================================
//タスク１,CSS圧縮
//=================================
gulp.task('minify-css', function(){
  //gulp ver4からはreturnが必要
  //圧縮元のCSSを指定
  return gulp.src('./src/css/*.css')
  //圧縮タスクを呼び出し
  .pipe(minify_css())
  .pipe(rename('style-min.css'))
  //圧縮後の吐き出し先を指定
  .pipe(gulp.dest('./dist/css/'));
})
  

 
//=================================
//タスク２,画像圧縮
//=================================
var Img_paths = {
  srcDir: 'src/img/',
  dstDir: 'dist/img/'
}
//jpg,png,gif画像の圧縮タスク
gulp.task('imagemin', function(){
  var srcGlob = Img_paths.srcDir + '/**/*.+(jpg|jpeg|png|gif)';
  var dstGlob = Img_paths.dstDir;
  
  /*.task実行時にsrcGlobとdstGlobに差分があった場合
    にimageminを使って圧縮している*/
  gulp.src(srcGlob)
  .pipe(changed(dstGlob))
  .pipe(imagemin(
    [
      imagemin.gifsicle({interlaced: true}),
      imagemin.jpegtran({progressive: true}),
      imagemin.optipng({optimizationLevel: 5})
    ]
  ))
  .pipe(gulp.dest(dstGlob));
});

//================================
//タスク3, sass gulp.ver4で記述
//================================

//変換タスク
gulp.task('sass', gulp.series(function(){
  //変換対象のscssファイルをを設定
  return gulp.src('./src/sass/*.scss')
  //対象のscssフォルダに対してsass()メソッドを使う
  .pipe(sass())
  //コンパイルしたscssの吐き出し先をdistに設定
  .pipe(gulp.dest('src/css/'));
  //done
  done();
}));


//================================
//タスク３, SCSSのファイル監視　gulp.ver4で記述
// scssファイルに変更があればgulp-sassを発動する
//================================
gulp.task('watch-css', gulp.series(function(){
  //監視対象を全ての(**)CSSコンポーネントファイルにすることに注意
  gulp.watch('./src/sass/**', gulp.series('sass', 'minify-css'));
  //doneはいらない？
}));

//================================
//タスク４ デフォルトタスクを書く
//================================
//現在はver4を使っているのでその書き方をする
gulp.task('default', gulp.series('sass','minify-css')); 
//タスクを1つだけ設定する場合でもseries()は必要なので下記のように書く
  //gulp.task('default', gulp.series('watch-css'));

//ver3での書き方
  //gulp.task('default', ['watch-css', 'minify-css'])
