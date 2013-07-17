<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

if ( ! function_exists('extract_node_data'))
{
	function extract_node_data($bulk)
	{
            if(empty($bulk))
                return false;
            $ret=array();
            foreach($bulk as $key => $single_node){
                $data = array();
                $data['_id']=$single_node[0]->getId();
                $data = array_merge($data,$single_node[0]->getProperties());
                $ret[]=$data;
            }

            if(empty($ret)) return false;
            return $ret;
	}
}

function is_array_satisfied($param,$input){
    foreach($param as $key){
        if(!array_key_exists($key,$input)) return false;
    }
    return true;
}

function is_array_allowed($param,$input){
    foreach($param as $key){
        if(!array_key_exists($key,$input)) return false;
    }
    return true;
}

function clean_array($param,$input){
    foreach($input as $key => $val){
        if(!in_array($key, $param))
                unset($input[$key]);            
    }
    return $input;
}

function wrap_response($status,$info=null,$data=null){
    $obj['status'] = (bool) $status;
    if(!is_null($info))
        $obj['info'] = $info;
    if(!is_null($data))
        $obj['data'] = $data;
    return (object) $obj;
}

    function response_error($errorcode = 1, $returndata = '') {
        $CI = &get_instance();
        $data[$CI->config->item('status_param')]= $this->request_error[$errorcode];
        if(!empty($returndata))
            $data[$CI->config->item('data_param')] = $returndata;
        $CI->response($data, 200);
    }

    function response_ok($returndata = '') {
        $CI = &get_instance();
        $data[$CI->config->item('status_param')]= 0;
        if(!empty($returndata)){
            $data[$CI->config->item('data_param')] = $returndata;
        }
        $CI->response($data, 200);
    }

?>
