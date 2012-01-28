<?php
session_start();

//https://code.google.com/apis/console/
//アプリケーションごとのクライアントIDとシークレットキー
$clientId="142287768578.apps.googleusercontent.com";
$clientSecret="Q4oCEMt0djuXtDYIXergztPo";

//アクセスコントロール用のGoogle Document ID
$docId = '0Byvi4bob8F8dZjNlZmI4YjUtMWU4Ny00YjY4LWJhYWMtMmEzZDcyYmY2MjBh';

//コールバックに指定したURL
$callback = "http://localhost/~daichang/oauth/gauth.php";

//認証完了後のURL
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
	$head = array(
	'Authorization' => 'Bearer '.$access_token,
	'GData-Version' => '3.0',
	);
	$doc = get($uri, array(), $head);
	if(empty($doc)){
		die('doc access failed');
	}
	$doc = simplexml_load_string($doc);
	if(empty($doc->title)){
		die('parse doc failed');
	}

	//get Email
	$uri = 'https://www.googleapis.com/oauth2/v1/userinfo';
	$head = array(
	'Authorization' => 'Bearer '.$access_token,
	);
	$doc = get($uri, array(), $head);
	$dat = json_decode($doc, true);
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
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
	
	if($status===200){	
		return $res;
	}else{
		return false;
	}
}

function get($url, $data, $headers=array()){
	$data = http_build_query($data);

	$ch=curl_init();
	curl_setopt ($ch,CURLOPT_URL, $url . '?' . $data);

	$head = array();	
	foreach($headers as $key=>$val){
		$head[] = $key . ':' . $val;
	}
	curl_setopt ($ch,CURLOPT_HTTPHEADER, $head);
	
	curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1);

	$res = curl_exec($ch);

	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
	
	if($status===200){	
		return $res;
	}else{
		return false;
	}
}



