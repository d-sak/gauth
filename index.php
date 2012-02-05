<?php
require_once 'config.php';
require_once 'gauth.php';

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
<?php if ($_SESSION['GAUTH_TOKEN']): ?>
    hello, <?php echo $_SESSION['GAUTH_EMAIL'] ?>
    <p><a href="?logout">Logout</a></p>
<?php else: ?>
    <p><a href="?login">Login</a></p>
<?php endif ?>
</body>
</html>
