<?php
require("config.inc.php");

try {
	$o = new OAuth(OAUTH_CONSUMER_KEY,OAUTH_CONSUMER_SECRET,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_AUTHORIZATION);

//	$arrayResp = $o->getRequestToken("https://fireeagle.yahooapis.com/oauth/request_token");
        $x  = $o->generateSignature("GET", "http://new.api.my.kompas.com/authorize/request_token");
//        $x  = $o->generateSignature();
var_dump($x);        

        $arrayResp = $o->getRequestToken("http://new.api.my.kompas.com/authorize/request_token");

        echo "<pre>";
        print_r($arrayResp);
        echo "</pre>";
        die;

	file_put_contents(OAUTH_TMP_DIR . "/request_token_resp",serialize($arrayResp));
	$authorizeUrl = "https://fireeagle.yahoo.net/oauth/authorize?oauth_token={$arrayResp["oauth_token"]}";
	if(PHP_SAPI=="cli") {
		echo "Navigate your http client to: {$authorizeUrl}\n";
	} else {
		/* note: on the redirect there is no need to pass anything other than the oauth_token parameter */
		header("Location: {$authorizeUrl}");
	}
} catch(OAuthException $E) {
	echo "Response: ". $E->lastResponse . "\n";
}
