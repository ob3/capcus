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

class flexible extends REST_Controller
{
    
    function __construct()
    {
            parent::__construct();
    }
    
    function index_get(){
        $data = $this->get();
//        $this->response($data, 404);
//        $this->response(array('respon'=>'Authorized Request'), 200);
    }

    function insert_get(){
        $data=$this->get();
        print_r($data);
        
    }
    
    
    function insert_post(){
        $data=$this->get();
        print_r($data);
        
    }
    
    
    function add_relation_post(){
/*
 SAMPLE DATA
 * 
 * 
 
from={"_id":13,"labels":"USERX"}&to={"_id":"10","type":"USERX"}&relation_type=relation_friendship&relation_data={
"status":"CONFIRMED",
"dua":"2",
"tiga":"3"
}

 */
           
        $data = $this->post();
        $data2 = array('from'=>array(
                            "_id"       => 1,
                            "label"     => "USER"
                            ),
                        'to'=>array(
                            "_id"       => 2,
                            "label"     => "USER"
                        ),
                        'relation_type' => "relation_friendship",
                        'relation_data' =>array(
                            "status"     => "CONFIRMED"
                        )
                );
       
        foreach($data as $key => $value){
            $data[$key] = json_decode($value,true);
        }
//         $data=$data2;
        
        $this->load->model("m_freenode");
        $ret = $this->m_freenode->create_relation($data);
        
        if (!$ret->status) {
            $this->response($ret, 202);
        }

        //if success
        $this->response($ret, 200);
    }

    function add_subnode_post(){
        $data = $this->post();
   
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


        $this->load->model("m_freenode");
        //validate from node
        $valid_creator = $this->m_freenode->is_valid_node($data['from']);
        if(!$valid_creator->status){
            $this->response($valid_creator, 202);
        }
        
        //validate relation type & data
        $relation = $this->m_freenode->is_relation_valid($data['relation_data']);
        if(!$relation->status){
            $this->response($relation, 202);
        }
        
        //create subnode if relation & creator is valid
        $data['to']['creator'] = $data['from']['_id'];
        $subnode = $this->m_freenode->create_subnode($data['to']);
        if(!$subnode->status){
            $this->response($subnode, 202);
        }
        
        
        
        $data['from'] = $valid_creator->data;
//        $data['relation_data']=$relation;
        $data['to']=$subnode->data;
        
        $ret = $this->m_freenode->create_relation($data);
        
        if(!$ret->status){
            $this->response($ret, 202);
        }
        
        $this->response($ret, 200);
        
    }
    
    function detail_put(){
        
    }

}

?>
