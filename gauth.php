<?php
session_start();

//Get Account for each Applications
//https://code.google.com/apis/console/
$clientId="142287768578.apps.googleusercontent.com";
$clientSecret="Q4oCEMt0djuXtDYIXergztPo";

//Google Document ID for AccessControl
$docId = '0Byvi4bob8F8dZjNlZmI4YjUtMWU4Ny00YjY4LWJhYWMtMmEzZDcyYmY2MjBh';

//URL to this file
$callback = "http://localhost/~daichang/oauth/gauth.php";

//location after auth succeed
$onSucceed = 'http://localhost/~daichang/oauth/index.php';

if(isset($_GET['login'])){
	$scope = array(
	"https://docs.google.com/feeds/default/private/full/".$docId,
	"https://www.googleapis.com/auth/userinfo.profile",
	"https://www.googleapis.com/auth/userinfo.email",
	);
	$scope = join(' ', $scope);
	
	$url = 'https://accounts.google.com/o/oauth2/auth?client_id='.urlencode($clientId).
				'&redirect_uri='.urlencode($callback).'&scope='.urlencode($scope).'&response_type=code';

	header('Location: '.$url);
	exit;
}

if(!empty($_GET['code'])){

	$code = $_GET['code'];

	$data = array(
		'client_id' => $clientId,
		'client_secret' => $clientSecret,
		'redirect_uri' => $callback,
		'grant_type' => 'authorization_code',
		'code' => $code,
	);	
	$uri = 'https://accounts.google.com/o/oauth2/token';
	$var = post($uri, $data);
	
	if(empty($var)){
		die('token api failed');
	}
	$var = json_decode($var, true);
	if(empty($var['access_token'])){
		die('parse token failed');
	}
	$access_token = $var['access_token'];

	//ACL check
	$uri = 'https://docs.google.com/feeds/default/private/full/'.$docId.'';
	$cmd = 'curl -H "Authorization: Bearer '.$access_token.'" -H "GData-Version: 3.0" '.$uri;
	$doc = `$cmd`;
	if(empty($doc)){
		die('doc access failed');
	}
	$doc = simplexml_load_string($doc);
	if(empty($doc->title)){
		die('parse doc failed');
	}

	//get Email
	$cmd = 'curl -H "Authorization: Bearer '.$access_token.'" https://www.googleapis.com/oauth2/v1/userinfo';
	$dat = json_decode(`$cmd`, true);
	$email = $dat['email'];

	$_SESSION['GAUTH_EMAIL'] = $email;
	$_SESSION['GAUTH_TOKEN'] = $access_token;
	
	header('Location: '.$onSucceed);
	exit;
}

function post($url, $data=array()){
	$ch=curl_init();
	curl_setopt ($ch,CURLOPT_URL, $url);
	curl_setopt ($ch,CURLOPT_POST,1);
	

	$post = http_build_query($data);
	
	curl_setopt ($ch,CURLOPT_POSTFIELDS,$post);
	curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1);

	$res = curl_exec($ch);
	curl_close($ch);
	
	return $res;
}




