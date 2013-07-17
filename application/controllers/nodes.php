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

class nodes extends REST_Controller
{
    
    function __construct()
    {
        parent::__construct();
        // nanti diambil dari session user yang sudah login
        $this->user_id=43;
        //-------------------------------------------------
    }
    
    function home_get(){
        $this->load->model("m_home");
        $ret = $this->m_home->news_feed($this->user_id);

        if(!$ret->status){
            $this->response(array($ret),202);
        }
        //if success
        $this->response($ret, 200);
    }
    
    function home_put(){
        $data = $this->put();
   
        $data["from"] = array("_id" => $this->user_id);
        $data['from'] = json_encode($data['from']);
        
        $required = array('from','to','relation_data');
        
        foreach ($required as $val){
            if(!isset($data[$val])){
                $this->response(wrap_response(false, "required data not satisfied: ".  implode(", ", $required)), 202);
            }
        }
        
        
        unset($required);
        unset($val);
        

        foreach($data as $key => $value){
             $data[$key] = json_decode($value,true);
             if(!is_array($data[$key]))
                 $this->response(wrap_response(false, "$key is not a valid Json", 202));
        }
        
//        $data['to']['creator'] = $this->user_id;


        $this->load->model("m_freenode");
        //validate from node
        $data['from'] = $this->m_freenode->is_valid_node($data['from']);
        if(!$data['from']->status){
            $this->response($data['from'], 202);
        }
        
        $data['from'] = $data['from']->data;


        $subnode = $this->m_freenode->create_subnode($data);
        if(!$subnode->status){
            $this->response($subnode, 202);
        }
               
        
        $this->response($subnode, 200);
    }
    
    function detail_get($_id=null){
        $out=wrap_response(false,"Invalid Request");
        if(is_null($_id)) $this->response($out,400);
        
        try {
            $out=array();
            $node = $this->graph->getNodeById($_id);
            $out['label'] = $node->getLabel();
            $out['data'] = $node->_data;

            $this->load->model("m_freenode");
            $out['relation'] = $this->m_freenode->get_relation_summary($_id);
        } catch (NotFoundException $e) {
            $out = wrap_response(false, "No data Found");
        }
        
        $this->response($out,200);

    }
    
    function detail_post($_id=null){
        $out=wrap_response(false,"ID not known");
        if(is_null($_id)) $this->response($out,400);
        

        $this->load->model("m_freenode");        
        $data = $this->post();
        
        foreach($data as $key => $value){
             $data[$key] = json_decode($value,true);
             if(!is_array($data[$key]))
                 $this->response(wrap_response(false, "$key is not a valid Json", 202));
        }
        
//        $relation = $this->m_freenode->is_relation_valid($data['relation_data']);
//        if(!$relation->status){
//            $this->response($relation, 202);
//        }
//        
//        if($relation->data['is_create']===true){
//            $this->response(wrap_response(false, "Relation only allowed for create"), 202);
//        }

        $data["from"] = array("_id" => $this->user_id);
        $data["to"] = array("_id" => $_id);

        $ret = $this->m_freenode->create_relation($data);

        if(!$ret->status){
            $this->response(array($ret),202);
        }
        //if success
        $this->response($ret, 200);
    }
    
    function activity_get($_id){
        $this->load->model("m_home");
        $ret = $this->m_home->user_activity($_id);
        
        if(!$ret->status){
            $this->response(array($ret),202);
        }
        //if success
        $this->response($ret, 200);
    }
    
    function lists_get(){
        
    }

}

?>
