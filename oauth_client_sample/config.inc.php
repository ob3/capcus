<?php
/* you need to register a consumer key at https://fireeagle.yahoo.net/developer, after registering you are given the secret, and the general purpose token/secret */
define('OAUTH_CONSUMER_KEY','androidconsumerkey');
define('OAUTH_CONSUMER_SECRET','kd94hf93k423kf44_3w4wer');
define('GENERAL_PURPOSE_TOKEN','footoken');
define('GENERAL_PURPOSE_TOKEN_SECRET','footokensecret');
define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));
?>
