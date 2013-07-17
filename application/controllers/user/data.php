<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . '/libraries/REST_Controller.php');

class data extends REST_Controller {

    //put your code here
//    function index_get() {
//        $param = $this->get();
//        $this->_browse($param);
//    }
//    
//    function _browse($param){
//        $this->load->model("m_user");
//
//        $ret = $this->m_user->get($param);
//
//        if (!$ret->status) {
//            $this->response(array($ret), 202);
//        }
//
//        //if success
//        $this->response($ret, 200);
//        $this->response(array('respon' => 'data updated'), 200);
//    }

    //update
    function index_post() {

        $this->load->model("m_user");
        $param = $this->post();

        $ret = $this->m_user->update($param);
        //Insert Error send error info & error code

        if (!$ret->status) {
            $this->response(array($ret), 202);
        }

        //if success
        $this->response($ret, 200);
        $this->response(array('respon' => 'data updated'), 200);
    }

    function index_put() {
        $this->load->model("m_user");
        $param = $this->put();

        $ret = $this->m_user->create($param);

        //Insert Error send error info & error code
        if (!$ret->status) {
            $this->response(array($ret), 202);
        }

        //if success
        $this->response($ret, 200);
    }

    function list_get() {
        $this->load->model('m_user');
        $param = $this->get();
        $result = $this->m_user->lists($param);
        $this->response($result, 200);
    }
    
    function notification_get() {
        $this->load->model("m_user");
        $param['_id']=7;
        $this->m_user->notification($param);
    }
}

?>
