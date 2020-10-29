<?php
const callbackUrl = 'https://volgakurvar.com/shizudaisei-finder/api/users/callback';

session_start();
// require_once './secret.php';


// require_once '../api/vendor/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

//TwitterOAuth をインスタンス化
$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
//コールバックURLセット
$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => callbackUrl));

//callback.phpで使うのでセッションに入れる
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

//Twitter.com 上の認証画面のURLを取得
$url = $connection->url(
    'oauth/authenticate',
    ['oauth_token' => $request_token['oauth_token']]
);

//Twitter.com の認証画面へリダイレクト
?>
<script>
    window.location.href = "<?=$url?>";
</script>