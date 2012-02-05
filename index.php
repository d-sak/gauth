<?php
require_once 'config.php';
require_once 'gauth.php';

session_start();

if (isset($_GET['login'])) {
    gauth::login($options); 
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
<?php else: ?>
    <p><a href="?login">Login</a></p>
    <p><a href="?logout">Logout</a></p>
<?php endif ?>
</body>
</html>
