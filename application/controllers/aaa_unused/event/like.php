<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . '/libraries/REST_Controller.php');

class Rate extends REST_Controller {

    //put your code here
    function index_get() {
        $this->load->model("m_event");
        $this->m_event->getRate();
        
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

    //rate
    function index_put() {
        $this->load->model("m_event");
        $this->m_event->rate();
    }

    //delete
    function index_delete() {

    }
}

?>
