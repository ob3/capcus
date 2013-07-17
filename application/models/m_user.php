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
class m_user extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
        $this->type = $this->config->item("user_type");
        $this->minimum_data = array('name', 'email');
        $this->allowed_data_input = array('name', 'email', 'phone');
    }

    function update($data) {
        //Checking minimum insert requirement
        $minimum = array('_id');

        //check minimum data
        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));

        $_id = $data["_id"];
        unset($data["_id"]);

        $data = clean_array($this->allowed_data_input, $data);

        //No data to update
        if (count($data) < 1)
            return wrap_response(false, "No data To update");
        
        

        try {
            $user = $this->graph->getNodeById($_id);
            
           if(array_search($this->config->item("user_type"), $user->getLabel())===false)
                   throw new NotFoundException();
            
            foreach ($data as $key => $val) {
                $user->$key = $val;
            }
            $date = new DateTime();
            $user->lastupdate = $date->getTimestamp();
            
            $result = $user->save();
            return wrap_response(true, "Data Updated");
        } catch (NotFoundException $e) {
            return wrap_response(false, "No data Found");
        }
    }

    function create($data) {
        //Checking minimum insert requirement
        if (!is_array_satisfied($this->minimum_data, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $this->minimum_data));

        //clean data
        $data = clean_array($this->allowed_data_input, $data);
        
        //No data to add
        if (count($data) < 1)
            return wrap_response(false, "No data To update");
        
        
        $newuser = $this->graph->createNode();
        $newuser->setLabel($this->type);
        
        $date = new DateTime();
        $newuser->createddate = $date->getTimestamp();
        
        foreach ($data as $key => $val) {
            $newuser->$key = $val;
        }        
        print_r($newuser);
        $result = $newuser->save();
        print_r($newuser);die;
        return wrap_response(true);
    }
    
    function get($data) {
        $minimum = array('_id');
        //Checking minimum insert requirement
        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));

        
        $_id = $data["_id"];
        unset($data["_id"]);
        //clean data
        
        try {
            $user = $this->graph->getNodeById($_id);
            
           if(array_search($this->config->item("user_type"), $user->getLabel())===false)
                   throw new NotFoundException();
            
            $data['_id'] = $user->getId();
            $data = array(array_merge($data,$user->getProperties()));
            return wrap_response(true,null, $data);
        } catch (NotFoundException $e) {
            return wrap_response(false, "No data Found");
        }
    }
    
    function notification($data){
        $minimum = array('_id');
        //check minimum data
        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));
        
        $script = "
                start root=node(".$data["_id"].")
                match from-[r]->root
                //where not has(r.ack)
                return from, r
                //return count(root)
            ";
        $res = $this->graph->performCypherQuery($script);
        echo "<pre>";
//        var_dump($res);
        print_r($res);
        echo "</pre>";
        die;
    }
   
    function lists($criteria) {
        
        $limit = isset($param['limit'])? $param['limit']: 10;
        $page = isset($param['page'])? $param['page']: 1;

        if(!is_numeric($page))
            return wrap_response(false, "Wrong Page");
        
        if(!is_numeric($limit))
            return wrap_response(false, "Wrong Limit");
        $skip = ($page-1)*$limit;
        
        
        $where = 'WHERE ';
        $loop = 0;
        foreach ($criteria as $key => $value) {
            $loop++;
            $where .= "has(root.$key) and root.$key=~'(?i)$value.*'";
            if ($loop < count($criteria)) {
                $where .=" and ";
            }
        }

        $script = "
            MATCH root:$this->type
            $where
            return root
            ORDER BY root.name
                ;
            ";


        $res = $this->graph->performCypherQuery($script);

        $out = extract_node_data($res['data']);
        return wrap_response(true, null, $out);
//        return $out;
    }
    
    function delete($criteria) {
        
    }

}

?>
