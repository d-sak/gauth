<?php
//https://code.google.com/apis/console/

/*
$options = array(
    'client_id' => '',
    'client_secret' => '',
    'doc_id' => '',
    'redirect_uri' => "",
    'onSucceed' => '',
); 
 */

require_once 'config.php';

class gauth
{
    public static function login(array $options) 
    {
        $scope = array(
            "https://docs.google.com/feeds/default/private/full/{$options['doc_id']}",
            "https://www.googleapis.com/auth/userinfo.profile",
            "https://www.googleapis.com/auth/userinfo.email",
        );
        $scope = implode(' ', $scope);

        $url = 'https://accounts.google.com/o/oauth2/auth?client_id='.urlencode($options['client_id']).
            '&redirect_uri='.urlencode($options['redirect_uri']).'&scope='.urlencode($scope).'&response_type=code';

        header('Location: '.$url);
        exit;
    }

    public static function auth($code, array $options) 
    {
        $options['grant_type'] = 'authorization_code';
        $options['code'] = $code;

        $access_token = self::getAccessToken($options); 
        self::checkACL($access_token, $options); 

        $_SESSION['GAUTH_EMAIL'] = self::getEmail($access_token); 
        $_SESSION['GAUTH_TOKEN'] = $access_token;

        header('Location: '.$options['onSucceed']);
        exit;
    }

    private static function getAccessToken(array $options) 
    {
        $uri = 'https://accounts.google.com/o/oauth2/token';
        $var = self::post($uri, $options);
        if (empty($var)) {
            throw new RuntimeException('token api failed');
        }
        $var = json_decode($var, true);
        if(empty($var['access_token'])){
            die('parse token failed');
        }

        return $var['access_token'];
    }

    private static function checkACL($access_token, array $options) 
    {
        //ACL check
        $uri = "https://docs.google.com/feeds/default/private/full/{$options['doc_id']}";
        $head = array(
            'Authorization' => 'Bearer '.$access_token,
            'GData-Version' => '3.0',
        );
        $doc = self::get($uri, array(), $head);
        if (empty($doc)) {
            exit('doc access failed');
        }
        $doc = simplexml_load_string($doc);
        if(empty($doc->title)){
            die('parse doc failed');
        }
    }

    //get Email
    private static function getEmail($access_token) 
    {
        $uri = 'https://www.googleapis.com/oauth2/v1/userinfo';
        $head = array(
            'Authorization' => 'Bearer '.$access_token,
        );
        $doc = self::get($uri, array(), $head);
        $dat = json_decode($doc, true);
        return $dat['email'];
    }

    private static function post($url, $post_data = array())
    {
        $data = http_build_query($post_data);

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

    private static function get($url, $data, $headers=array()){
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
}

session_start();
if (!empty($_GET['code'])) {
    $code = $_GET['code'];
    gauth::auth($code, $options); 
}

