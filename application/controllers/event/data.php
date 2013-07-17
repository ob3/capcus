<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . '/libraries/REST_Controller.php');

class data extends REST_Controller {

    //put your code here
    
    function index_get() {
        $this->load->model("m_event");
        $param = $this->get();
        $ret = $this->m_event->get($param);
        
        if(!$ret->status){
            $this->response(array($ret),202);
        }
        
        //if success
        $this->response($ret, 200);
    }

    //update
    function index_post() {
        
        $this->load->model("m_event");
        $param = $this->post();
        
        $ret = $this->m_event->update($param);
        //Insert Error send error info & error code
        
        if(!$ret->status){
            $this->response(array($ret),202);
        }
    
        //if success
        $this->response($ret, 200);
        $this->response(array('respon' => 'data updated'), 200);
    }

    function index_put() {
        $this->load->model("m_event");
        $param = $this->put();
        $ret = $this->m_event->create($param);
        
        //Insert Error send error info & error code
        if(!$ret->status){
            $this->response(array($ret),202);
        }
    
        //if success
        $this->response($ret, 200);
    }

    //delete
    function index_delete() {
        $this->response(array('respon' => 'Your deleted data'), 200);
    }
    
    function category_get() {
        $last_update = $this->get("last_update");
        $this->config->load('list_category');
        $list = $this->config->item("category");
        $info['last_update'] = $this->config->item("category_last_update");
        if($last_update >= $info['last_update']){
            $respon = wrap_response(false,"Already Updated");
        }else{
            $respon = wrap_response(true,$info ,$list);
        }
        $this->response(array('respon' => $respon), 200);
    }
    
    function list_get() {
        $this->load->model("m_event");
        $param = $this->get();
        $ret = $this->m_event->lists($param);
        
        //Insert Error send error info & error code
        if(!$ret->status){
            $this->response(array($ret),202);
        }
    
        //if success
        $this->response($ret, 200);
    }

    function peek_get() {
        $this->load->model('m_event');
        $param = $this->get();
        $result = $this->m_event->retrive($param);
//        $this->response(array('respon' => 'Your tiny data'), 200);
        $this->response($result, 200);
    }
}

?>
