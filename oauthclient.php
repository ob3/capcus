 <?php
 
//include './db.inc'; 
$cons_key = 'androidconsumerkey';
$cons_secret = 'androidconsumersecret';
$trec['state']=0;
//$u_id = $logged_in_uid; // Simplification of Slowgeek-specific user id system
//
//$twit = new twitter;
//$trec = $twit->load($u_id);

// If we are in state=1 there should be an oauth_token, if not go back to 0
//if($trec['state']==1 && !isset($_GET['oauth_token'])) $trec['state'] = 0;

try {

  $oauth = new OAuth($cons_key,$cons_secret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();  // This will generate debug output in your error_log

  if($trec['state']==0) {

    // State 0 - Generate request token and redirect user to Twitter to authorize
//    $request_token_info = $oauth->getRequestToken('https://twitter.com/oauth/request_token');

      $request_token_info = $oauth->getRequestToken('http://new.api.my.kompas.com/authorize/request_token');
      die("edo2");
    $rec['token'] = $request_token_info['oauth_token'];
    $rec['secret'] = $request_token_info['oauth_token_secret'];
    $rec['state'] = 1;
//    $twit->save($rec); // Save the token and the secret - we need the secret in state 1
//    header('Location: https://twitter.com/oauth/authorize?oauth_token='.$rec['token']);
    exit; 

  } else if($trec['state']==1) {

    // State 1 - Handle callback from Twitter and get and store an access token
    $oauth->setToken($_GET['oauth_token'],$trec['secret']);
    $access_token_info = $oauth->getAccessToken('https://twitter.com/oauth/access_token');
    $rec['state'] = 2;
    $rec['token'] = $access_token_info['oauth_token'];
    $rec['secret'] = $access_token_info['oauth_token_secret'];
    $twit->save($rec); // Save our access token and secret
    $trec['token'] = $rec['token'];
    $trec['secret'] = $rec['secret'];
    unset($rec);
    // Fall through to authorized state
  }

  // State 2 - Authorized. We can just use the stored access token
  $oauth->setToken($trec['token'],$trec['secret']);
  $oauth->fetch('https://twitter.com/account/verify_credentials.json'); 
  $json = json_decode($oauth->getLastResponse());
/*
  $debug = $oauth->getLastResponseInfo();
  print_r($debug);
*/
  $rec['name'] = (string)$json->screen_name;
  $rec['description'] = (string)$json->description;
  $rec['status'] = (string)$json->status->text;
  $rec['location'] = (string)$json->location;
  $rec['followers'] = (int)$json->followers_count;
  $twit->update($rec);
} catch(OAuthException $E) {
  print_r($E);
}

