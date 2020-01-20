<!--ヘッダ-->
<header class="header">
    <div class="site-width">
        <h1 class="site-title"><a href="home.php">Sweets Board</a></h1>
        <nav class="top-nav">
            <ul>
            <?php
            //ログインしていなかった場合
            if(empty($_SESSION['user_id'])){
            ?>
                <li class="btn-container"><a href="signup.php" class="btn btn-s btn-mypage">ユーザー登録</a></li>
                <li class="btn-container"><a href="login.php" class="btn btn-s btn-logout">ログイン</a></li>
            <?php
            //ログインしていた場合
            }else{
            ?>
                <li class="btn-container"><a href="mypage.php" class="btn btn-s btn-mypage">マイページへ</a></li>
                <li class="btn-container"><a href="logout.php" class="btn btn-s btn-logout">ログアウト</a></li>
            <?php
            }
            ?>
            </ul>
        </nav>
    </div>
</header>