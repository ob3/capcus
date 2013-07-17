<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Keys Controller
 *
 * This is a basic Key Management REST controller to make and delete keys.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
 */
// This can be removed if you use __autoload() in config.php
require(APPPATH . '/libraries/REST_Controller.php');

class Authorize extends REST_Controller {
//class Authorize extends CI_Controller {

    function index_get() {
        $info = parse_url("mysql://searchuser:megaportalan@10.50.12.180/oauth");
        $oauthdb = mysql_connect($info['host'], $info['user'], $info['pass']);
        mysql_select_db(basename($info['path']), $oauthdb);

        $this->load->library("oauth/OAuthStore");
        $this->oauthstore->instance("MySQL",array('conn' => $oauthdb));
        $this->load->library("/oauth/OauthServer");
        
	try
	{
                $this->oauthserver->verifyIfSigned();
                
                die("edo X");
	}
	catch (OAuthException2 $e)
	{
		header('HTTP/1.1 400 Bad Request');
		header('Content-Type: text/plain');
		
		echo "Failed OAuth Request: " . $e->getMessage();
	}
        
    }
    

    

    function create_get() {
        $xx = $this->_create();
        $this->response(array('respon' => $xx), 200);
    }

    function request_token_get() {
        $x = $this->get();
        $this->_request_token($this->get());
    }
    
    function request_token_post() {
        $x = $this->post();
        $this->_request_token($this->post());

    }

    
    function autho_get() {
        $info = parse_url("mysql://searchuser:megaportalan@10.50.12.180/oauth");
        $oauthdb = mysql_connect($info['host'], $info['user'], $info['pass']);
        mysql_select_db(basename($info['path']), $oauthdb);

        $this->load->library("oauth/OAuthStore");
        $this->oauthstore->instance("MySQL",array('conn' => $oauthdb));
        $this->load->library("/oauth/OauthServer");
		$this->oauthserver->authorizeVerify();
//		$this->oauthserver->authorizeFinish(true);
    }
    
    function autho_post() {
            $server->authorizeVerify();
            $server->authorizeFinish(true, 1);

    }
    
    function access_token_get() {
        $x = $this->get();
        $this->_access_token($this->get());
    }
    
    function access_token_post() {
        $x = $this->post();
        $this->_access_token($this->post());

    }

    function _request_token($param) {
        $info = parse_url("mysql://searchuser:megaportalan@10.50.12.180/oauth");
        $oauthdb = mysql_connect($info['host'], $info['user'], $info['pass']);
        mysql_select_db(basename($info['path']), $oauthdb);

        $this->load->library("oauth/OAuthStore");
        $this->oauthstore->instance("MySQL",array('conn' => $oauthdb));
        $this->load->library("/oauth/OauthServer");
        $this->oauthserver->requestToken();
        exit;
    }
    
    function _access_token($param) {
        
        $info = parse_url("mysql://searchuser:megaportalan@10.50.12.180/oauth");
        $oauthdb = mysql_connect($info['host'], $info['user'], $info['pass']);
        mysql_select_db(basename($info['path']), $oauthdb);
        
        $this->load->library("oauth/OAuthStore");
        $this->oauthstore->instance("MySQL",array('conn' => $oauthdb));
        $this->load->library("/oauth/OauthServer");
        
		$this->oauthserver->authorizeVerify();
		$this->oauthserver->authorizeFinish(true, 1);
//echo "edo";exit;
        $this->oauthserver->accessToken();
        exit;

    }

}

?>
