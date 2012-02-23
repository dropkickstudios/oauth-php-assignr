<html>
<body>
<?php
/*
  Two-legged oauth is used when you are not requesting a third party's data. 
  For example, when you are using a data feed from assignr.com on your own site.
  
  This assumes that you have the OAuth extension installed for PHP. 
  Oauth: http://www.php.net/manual/en/book.oauth.php
  Install: http://www.php.net/manual/en/oauth.installation.php

*/

$req_url = 'https://assignr.com/oauth/request_token';
$authurl = 'https://assignr.com/oauth/authorize';
$acc_url = 'https://assignr.com/oauth/access_token';
$api_url = 'https://assignr.com/api/v1';
// get these when you register an application on assignr.com
$conskey = 'consumer_key';
$conssec = 'consumer_secret';

try {
  $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  // remove this for production code
  $oauth->enableDebug();
  $oauth->fetch("$api_url/users.json");
  $json = json_decode($oauth->getLastResponse());
  foreach($json->users as $user)
  {
    echo '<p><span class="firstname">'.$user->first_name.'</span> <span class="firstname">'.$user->last_name.'</span></p>';
  }  

} catch(OAuthException $E) {
  print_r($E);
}
?>
</body>
</html>