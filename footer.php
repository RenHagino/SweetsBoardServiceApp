<footer class="footer">
    <!--コピーライト-->
    <p>Copyright <a href="http://sweetsboard.com/">スウィーツボード</a>. All Rights Reserved.<p>
        
    <!--jquery 読み込み-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>  
    <!--js処理-->
    <script>
        $(function(){
            
            //SUCCESSメッセージ表示
            var $jsShowMsg = $('.js-show-msg');
            var msg = $jsShowMsg.text(); //.textで取得したDOMに入っているテキストを取り出してmsgという変数に入れている
            //半角や全角のタブ、スペースを""（空白）に指定し、そのあとに.lengthで文字数を取得している
            //これがないとメッセージがあると判断され、SUCCESSメッセージが無いのにスライドが降りてくることを防止する
            if(　msg.replace(/^[\s　]+|[\s　]+$/g,　"").length ){
                //slideToggleで隠れていたメッセージが出てくる。
                $jsShowMsg.slideToggle('slow');
                //5秒かけて第一引数の処理を実行。　//第二引数に指定した時間をかけて第一引数に書いたメソッドを実行するのがsetTimeout
                setTimeout( function(){$jsShowMsg.slideToggle('slow'); }, 5000);
            }

            //画像ライブプレビュー機能
                var $dropArea = $('.area-drop'); //labelタグ
                var $fileInput = $('.input-file');//type属性にfileを指定したinputタグ
                //動作確認OK
                $dropArea.on('dragover',function(e){ 
                    e.stopPropagation(); //余計なイベントの伝播を防ぐためのもの
                    e.preventDefault(); //余計なイベントの伝播を防ぐためのもの
                    //$(this)としてDOMを取得することで thisは.area-dropでも良い？ jqueryのメソッドが使える
                    $(this).css('border', '3px #FF99FF dashed'); //画像をドラッグして乗せた時
                });
                //動作確認OK
                $dropArea.on('dragleave',function(e){
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).css('border','none');
                });
                //動作確認OK
                $fileInput.on('change',function(e){ //inputタグに変更があった時
                    $dropArea.css('border', 'none');
                    var file = this.files[0], //$fileInputのfiles配列の最初の値（最後に追加された画像）を取得。０にするのは配列の最後に追加されたの写真を取得するため
                        $img = $(this).siblings('.prev-img'),//jqueryのsiblingsメソッドを使い、兄弟のimg要素でprev-imgというクラスを持つものを取得。DOMを入れるので$imgとしている。
                        fileReader = new FileReader(); //ファイルを読み込むFileReaderオブジェクトを作り、fileReaderという変数の中に入れる。
                        //デバッグ
                        console.log('ドロップされたファイルを確認します')
                        console.log(file); 
                        console.log($img);
                        console.log(this);
                    
                    //読み込みが完了した際（onload）のイベントハンドラ。 imgタグのsrcにデータをセット
                    fileReader.onload = function(event){ //引数のeventに画像が入っている。
                    $img.attr('src',event.target.result).show(); //先ほど作った$imgにattrでsrc属性をつける。
                    };

                    //画像読み込み //画像自体を読み込んでいるのではなく、画像ファイル自体をDataURLというものに変換している
                    fileReader.readAsDataURL(file); //readAdDataURLというメソッドに先ほど作ったfileという変数を指定
                    
                });
            //== 画像ライブプレビュー終了 ==//
                
            //===================================
            //文字数カウント機能
            //===================================
                var $countUp = $('.js-count');
                var $countView = $('.js-count-view');
                    $countUp.on('keyup',function(e){
                    $countView.html($(this).val().length);
                });

            //===================================
            //画像切り替え
            //===================================
             //変数にDOMを入れる
             var $switchImgSubs = $('.js-switch-img-sub'),
                 $switchImgMain = $('.js-switch-img-main');
             
             //実際の処理。サブイメージがクリックされたら、メインイメージと入れ替える。
             $switchImgSubs.on('click',function(e){ 
                 //サブイメージがクリックされた時、メイン画像のsrcを
                 //ダブ画像のsrc($this.attr('src'))に書き換えている
                 $switchImgMain.attr('src',$(this).attr('src'));
             });
             
            //===================================
            //お気に入り機能 
            //===================================
            var $like,
            likeSweetsId;

            $like = $('.js-like-click') || null ; //DOMが取れなかった場合はundefinedで後続の処理が止まってしまうのでそれを防ぐためにnullを入れる。
            likeSweetsId =  $like.data('sweetsid') || null  //js-like-clickのDOMにsweetsidが無かった場合はnullを入れる

            console.log($like);
            console.log(likeSweetsId);
            
            //数値の0はfalseとされてしまう。sweets_idが0の場合もあるので0もtrueとする場合にはundefinedとnullを判定する
            if(likeSweetsId !== undefined && likeSweetsId !== null){
                //ハートマーク（.js-like-click）がクリックされたとき
                $like.on('click',function(){
                    //$thisに.js-like-clickのDOMを入れる
                    var $this = $(this); //$(this)という自分自身のDOM($like)を変数に入れている
                    
                    //Ajax通信の処理
                    $.ajax({
                        type: "POST",
                        url: "ajaxLike.php", //通信先。パスの指定に注意
                        data: {sweetsId : likeSweetsId} //通信先に渡すキー： 渡す値（詳細画面のスイーツのID)が入っている
                
                    //ajax通信に成功した場合 .doneを使う
                    }).done(function (data){
                        //クラス属性をつけ外しする処理
                        $this.toggleClass('active');
                        //console.logはリリース時に外す＝＞ユーザーが見れるといけないため
                        console.log('Ajax通信に成功しました'); 
                        
                    //ajax通信に失敗した場合 .failを使う
                    }).fail(function(msg){
                        console.log('Ajax通信に失敗しました');
                    });
                });
            }
            
        });
    </script>
</footer>



