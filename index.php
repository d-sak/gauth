<?php
session_start();

?>
<html>
<body>

<?if($_SESSION['GAUTH_TOKEN']):?>
	hello, <?=$_SESSION['GAUTH_EMAIL']?>
	
	
	
<?else:?>
	<a href="./gauthcallback.php?login">Login</a>
<?endif;?>


</body>

</html>

