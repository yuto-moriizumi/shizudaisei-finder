<?php
session_start();
// require_once './twitter/secret.php';

// require_once '../api/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//login.phpでセットしたセッション
$request_token = [];

$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];
//Twitterから返されたOAuthトークンと、あらかじめlogin.phpで入れておいたセッション上のものと一致するかをチェック
if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
    session_regenerate_id();
    die('Error!');
}
//OAuth トークンも用いて TwitterOAuth をインスタンス化
$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
//配列access_tokenにOauthトークンとTokenSecretを入れる
// $_SESSION['access_token']
$_SESSION['access_token'] = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);


// setcookie('access_token', $access_token['oauth_token']);
// setcookie('access_token_secret', $access_token['oauth_token_secret']);


//セッションIDをリジェネレート
// session_regenerate_id();

//リダイレクト
// header('location: ../dist/#/find/');
?>
<script>
    window.location.href = "../../dist/#/find/";
</script>