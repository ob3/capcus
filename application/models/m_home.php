<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of graph_user
 *
 * @author kcm
 */
class m_home extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    function news_feed($_id){
        $limit = isset($param['limit'])? $param['limit']: 10;
        $page = isset($param['page'])? $param['page']: 1;

        if(!is_numeric($page))
            return wrap_response(false, "Wrong Page");
        
        if(!is_numeric($limit))
            return wrap_response(false, "Wrong Limit");
        
        $skip = ($page-1)*$limit;


        $script = "start usr=node($_id)
            match usr-[:FRIEND_WITH]-friend-[activity]-acara
            return type(activity), activity.createddate, friend, acara
            order by activity.createddate desc
                skip $skip
                limit $limit
                ;
            ";
        
        $res = $this->graph->performCypherQuery($script);
        
        $out=array();
        foreach($res['data'] as $key => $val){
            $out[]=array(
                $val[0] => array(
                        "createddate"   => $val[1],
                        "from"          => $val[2]->_data,
                        "to"            => $val[3]->_data,
                    )
            );
        }
        
        return wrap_response(true,null, $out);
    }
    
    function user_activity($_id){
        $limit = isset($param['limit'])? $param['limit']: 10;
        $page = isset($param['page'])? $param['page']: 1;

        if(!is_numeric($page))
            return wrap_response(false, "Wrong Page");
        
        if(!is_numeric($limit))
            return wrap_response(false, "Wrong Limit");
        
        $skip = ($page-1)*$limit;


        $script = "
            start usr=node($_id)
            match usr-[activity]->node
            return type(activity), activity.createddate, node
            order by activity.createddate desc
                skip $skip
                limit $limit
                ;
            ";
        $res = $this->graph->performCypherQuery($script);
//        print_r($res);die;
        $out=array();
        foreach($res['data'] as $key => $val){
            $out[]=array(
                $val[0] => array(
                    "createddate"   => $val[1],
                    "to"            => $val[2]->_data
                    )
            );
        }

        return wrap_response(true,null, $out);
    }
}

?>
