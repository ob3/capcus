<?php defined('BASEPATH') OR exit('No direct script access allowed');

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
require(APPPATH.'/libraries/REST_Controller.php');

class browse extends REST_Controller
{
    
    function __construct()
    {
        parent::__construct();
    }
    
    function index_get(){
        $this->response(array('respon'=>'Authorized Request'), 200);
    }
    
    function insert_get(){
        
    }
    
    
    function detail_post(){
    }
    
    function detail_put(){
        
    }
    
    function node_get(){
        $data = $this->get();
        print_r($data);die;        
    }
    
    function node_post(){

    }

}

?>
