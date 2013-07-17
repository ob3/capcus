<?php defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'/libraries/REST_Controller.php');

class auth  extends REST_Controller
{
    
    //put your code here
    function login_get(){
        $this->response['info']="error";
        $this->response(array('respon'=>'login get response'), 200);
    }
    
    function login_post(){
        $this->response(array('respon'=>'login post response'), 200);
    }
    
    function resetpassword_post(){
        $this->response(array('respon'=>'reset password response'), 200);
    }
    
    function register_post(){


    }
    
    function _login(){
        $this->response(array('respon'=>'default response'), 200);
    }
}

?>
