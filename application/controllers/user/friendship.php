<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . '/libraries/REST_Controller.php');

class friendship extends REST_Controller {

    //put your code here
    function index_get() {
        $this->load->model("m_friendship");
        $param = $this->get();

        $ret = $this->m_friendship->lists($param);
        
        //Insert Error send error info & error code
        if(!$ret->status){
            $this->response($ret,202);
        }
        
        //if success
        $this->response($ret, 200);
    }

    //update
    function index_post() {
        $this->load->model("m_friendship");
        $param = $this->post();
        $param['status']=$this->config->item("relation_friendship_confirmed");
        $ret = $this->m_friendship->update($param);
        
        //Insert Error send error info & error code
        if(!$ret->status){
            $this->response($ret,202);
        }
        
        //if success
        $this->response($ret, 200);
    }

    function index_put() {
        $this->load->model("m_friendship");
        $param = $this->put();
        $param['status'] = $this->config->item("relation_friendship_requested");
        $ret = $this->m_friendship->create($param);
        
        //Insert Error send error info & error code
        if(!$ret->status){
            $this->response($ret,202);
        }
    
        //if success
        $this->response($ret, 200);
    }
    
    function index_delete(){
        $this->load->model("m_friendship");
        $param = $this->delete();
        $ret = $this->m_friendship->delete($param);
        
        //Insert Error send error info & error code
        if(!$ret->status){
            $this->response($ret,202);
        }
        
        //if success
        $this->response($ret, 200);   
    }
    


}

?>
