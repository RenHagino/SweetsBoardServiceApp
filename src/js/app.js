//Jsファイルが読み込まれたか確認するためのコード
  window.onload=function(){
    var script = document.createElement('script');
    script.src = "app.js";
    script.onload = function() {
      console.log("読み込み完了");
    }
    document.getElementsByTagName("body")[0].appendChild(script);
  }
