<?php
// gauthによるシンプルな認証サンプル
// ログインに成功すると$_SESSION['GAUTH_*']が返ってくる
require_once 'gauth.php';

// パラメーターは各自の環境にあわせて書き換えてください
$options = array(
    'client_id' => '735196303294.apps.googleusercontent.com',
    'client_secret' => 'pfHf6IeldUavHPAlt_7juzrR',
    'doc_id' => '1JcJVn_dRDAjlCULJ6SNkGwr0NqX_9twET_cL1c-gBeM',
    'redirect_uri' => "http://project-p.jp/halt/gauth_index.php",
    'onSucceed' => 'http://project-p.jp/halt/gauth_index.php',
); 

session_start();

if (isset($_GET['login'])) {
    gauth::login($options); 
}

if (!empty($_GET['code'])) {
    $code = $_GET['code'];
    gauth::auth($code, $options); 
}

if (isset($_GET['logout'])) {
    session_destroy(); 
    session_start();
}
?>
<html>
<body>
<?php if (isset($_SESSION['GAUTH_TOKEN'])): ?>
    hello, <?php echo $_SESSION['GAUTH_USERINFO']['name'] ?>
    <p><a href="?logout">Logout</a></p>
<?php else: ?>
    <p><a href="?login">Login</a></p>
<?php endif ?>
</body>
</html>
