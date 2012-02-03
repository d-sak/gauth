<?php
//https://code.google.com/apis/console/

require_once 'config.php';

session_start();
if (isset($_GET['login'])) {
    $scope = array(
        "https://docs.google.com/feeds/default/private/full/".$docId,
        "https://www.googleapis.com/auth/userinfo.profile",
        "https://www.googleapis.com/auth/userinfo.email",
    );
    $scope = implode(' ', $scope);

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
        throw new RuntimeException('token api failed');
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
        var_dump($doc);
        exit('doc access failed');
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

function post($url, $post_data = array())
{
    $data = http_build_query($post_data);

    //header
    $header = array(
        "Content-Type: application/x-www-form-urlencoded",
        "Content-Length: ".strlen($data)
    );

    $context = array(
        "http" => array(
            "method"  => "POST",
            "header"  => implode("\r\n", $header),
            "content" => $data
        )
    );

    return file_get_contents($url, false, stream_context_create($context));
}

function get($url, $data, $headers=array()){
    $data = http_build_query($data);
    $head = array();
    foreach($headers as $key=>$val){
        $head[] = $key . ': ' . $val;
    }
    $context = array(
        "http" => array(
            "method"  => "GET",
            "header"  => implode("\r\n", $head),
        )
    );
    return file_get_contents($url . '?' . $data, false, stream_context_create($context));
}

