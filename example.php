<?php
/*
  This is a very basic example of a PHP page that requests access from an assignr.com user, 
  stores the request token values in a session variable, and displays a list of users.
  
  This assumes that you have the OAuth PECL extension installed for PHP. 
  Oauth: http://www.php.net/manual/en/book.oauth.php
  Install: http://www.php.net/manual/en/oauth.installation.php
    
*/
$req_url = 'http://assignr.local/oauth/request_token';
$authurl = 'http://assignr.local/oauth/authorize';
$acc_url = 'http://assignr.local/oauth/access_token';
$api_url = 'http://assignr.local/api/v1';
// get these when you register an application on assignr.com
$conskey = 'consumer_key';
$conssec = 'consumer_secret';

session_start();

// In state=1 the next request should include an oauth_token.
// If it doesn't go back to 0
if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
  $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();
  if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
    // this block redirects to assignr.com to obtain authorization
    $request_token_info = $oauth->getRequestToken($req_url);
    $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
    $_SESSION['state'] = 1;
    header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
    exit;
  } else if($_SESSION['state']==1) {
    // this block executes when assignr.com returns to your site,
    // assuming the callback URL is this page
    $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
    // this calls assignr.com to get the access tokens.

    $access_token_info = $oauth->getAccessToken($acc_url);
    $_SESSION['state'] = 2;
    // retain these values in a database to pull data from the API after the end user authorizes the request
    $_SESSION['access_token'] = $access_token_info['oauth_token'];
    $_SESSION['access_token_secret'] = $access_token_info['oauth_token_secret'];
  } 
  $oauth->setToken($_SESSION['access_token'],$_SESSION['access_token_secret']);
  
  $oauth->fetch("$api_url/users.json");
  $json = json_decode($oauth->getLastResponse());
  foreach($json->users as $user)
  {
    echo '<p><span class="firstname">'.$user->first_name.'</span> <span class="firstname">'.$user->last_name.'</span></p>';
  }
/*

  once authorized, future requests can simply use this:
  
  $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  // the access_token and access_token_secret were retained from the user authorization 
  $oauth->setToken('access_token','access_token_secret'); 
  $oauth->fetch("$api_url/users.json");
  $json = json_decode($oauth->getLastResponse());
  foreach($json->users as $user)
  {
    echo '<p><span class="firstname">'.$user->first_name.'</span> <span class="firstname">'.$user->last_name.'</span></p>';
  }

*/  
  
    
} catch(OAuthException $E) {
  print_r($E);
}
?>
