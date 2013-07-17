<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . '/libraries/REST_Controller.php');

class Rate extends REST_Controller {

    //put your code here
    function index_get() {
        $this->load->model("m_freenode");
        $label=$this->config->item("event_type");
        $data = $this->get();
        $ret = $this->m_freenode->rate($label,$data);
        
        if(!$ret->status){
            $this->response(array($ret),202);
        }
        //if success
        $this->response($ret, 200);
    }

    //update
    function index_post() {
        $this->load->model("m_freenode");
        $label=$this->config->item("event_type");
        $data = $this->post();
        $ret = $this->m_freenode->rate($label,$data);
        
        if(!$ret->status){
            $this->response(array($ret),202);
        }
        //if success
        $this->response($ret, 200);
    }

    //delete
    function like_post(){
        $data = $this->post();
        $ret = $this->m_freenode->rate($label,$data);
    }
    
    function attend_post(){
        $this->load->model("m_freenode");
        $data = $this->post();
        $data2["from"]['_id']=$data['_id'];
        $data2["to"]['_id']=$data['event_id'];
        $data2["relation_data"]['relation_type']='relation_attend';
        $ret = $this->m_freenode->create_relation($data2);
        
        if(!$ret->status){
            $this->response(array($ret),202);
        }
        //if success
        $this->response($ret, 200);
        
    }
}

?>
