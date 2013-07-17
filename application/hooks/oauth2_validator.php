<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of oauth2_validator
 *
 * @author kcm
 */
class oauth2_validator {
    //put your code here
    function validate(){
//        require_once __DIR__.'/server.php';
        require_once __DIR__.'/../../oauth2.0/server.php';
        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }
    }
}

?>
