<?php
session_start();
?>
<html>
<body>
<?php if ($_SESSION['GAUTH_TOKEN']): ?>
    hello, <?php echo $_SESSION['GAUTH_EMAIL'] ?>
<?php else: ?>
    <a href="./gauthcallback.php?login">Login</a>
<?php endif ?>
</body>
</html>
