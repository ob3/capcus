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
class m_friendship extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
        $type=$this->config->item("relation_type");
//        $type = $type["relation_friendship"]['name'];

        $this->type = $type["relation_friendship"]['name'];
//        $this->minimum_data = $type["relation_friendship"]['minimum_data'];
//        $this->allowed_data_input = $type["relation_friendship"]['allowed_data'];
        $this->minimum_data = array('_id', 'friend_id', 'status');
        $this->allowed_data_input = array('_id','friend_id','status');
    }

    function update($data) {
        //Checking minimum insert requirement
        if (!is_array_satisfied($this->minimum_data, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $this->minimum_data));

        //clean data
        $data = clean_array($this->allowed_data_input, $data);
        
        //No data to add
        if (count($data) < 1)
            return wrap_response(false, "No data To update");
        
        try{ //validate user id
           $user = $this->graph->getNodeById($data["_id"]);
           
           if(array_search($this->config->item("user_type"), $user->getLabel())===false)
                   throw new NotFoundException();

        } catch (NotFoundException $e) {
            return wrap_response(false, "Wrong _id");
        }

        try{ //validate friend id
           $friend = $this->graph->getNodeById($data["friend_id"]);
           if(array_search($this->config->item("user_type"), $friend->getLabel())===false)
                   throw new NotFoundException();
        } catch (NotFoundException $e) {
            return wrap_response(false, "Wrong friend_id");
        }
        
        

        $script = "
            START to=node(".$data["_id"]."), from=node(".$data["friend_id"].")
                match to<-[r]-from
                set r.status='".$data['status']."';
            ";
        
        $res = $this->graph->performCypherQuery($script);
        return wrap_response(true);

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
        
        try{ //validate user id
           $user = $this->graph->getNodeById($data["_id"]);

           if(array_search($this->config->item("user_type"), $user->getLabel())===false)
                   throw new NotFoundException();

        } catch (NotFoundException $e) {
            return wrap_response(false, "Wrong _id");
        }

        try{ //validate friend id
           $friend = $this->graph->getNodeById($data["friend_id"]);
           if(array_search($this->config->item("user_type"), $friend->getLabel())===false)
               throw new NotFoundException();
        } catch (NotFoundException $e) {
            return wrap_response(false, "Wrong friend_id");
        }
        
        
        unset($data['_id']);
        unset($data['friend_id']);
        
        $relation = $user->createRelationshipTo($friend, $this->type);
        
        foreach ($data as $key => $value){
            $relation->$key = $value;
        }
        
        $date = new DateTime();
        $relation->createddate = $date->getTimestamp();
        $relation->save();
        return wrap_response(true);
    }
   
    function lists($param) {
        
        $minimum = array('_id');
        $limit = isset($param['limit'])? $param['limit']: 10;
        $page = isset($param['page'])? $param['page']: 1;

        if(!is_numeric($page))
            return wrap_response(false, "Wrong Page");
        
        if(!is_numeric($limit))
            return wrap_response(false, "Wrong Limit");
        
        $skip = ($page-1)*$limit;
        
        if (!is_array_satisfied($minimum, $param))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));

        //clean data
        $data = clean_array($this->allowed_data_input, $param);
        
        //No data to add
        if (count($data) < 1)
            return wrap_response(false, "No Parameter");

        
        $script = "
                    start r=node(".$param['_id'].")
                    match friend<-[rela:FRIEND_WITH]->r
                    return friend
                skip $skip
                limit $limit
                ;
            ";

        try {
            $res = $this->graph->performCypherQuery($script);

            $out = extract_node_data($res['data']);
            if($out){
                return wrap_response(true, null, $out);
            }else{
                return wrap_response(false, "No data Found");
            }            
        } catch (Exception $exc) {
            return wrap_response(false, $exc->getMessage());
        }        
    }
    
    function delete($data) {
        //Checking minimum insert requirement
        $minimum = array('_id', 'friend_id');

        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));

        //clean data
        $data = clean_array($this->allowed_data_input, $data);
        
        //No data to add
        if (count($data) < 1)
            return wrap_response(false, "No data To update");

        try{ //validate user id
           $user = $this->graph->getNodeById($data["_id"]);
           if($user->type!=$this->config->item("user_type"))
               throw new NotFoundException();
        } catch (NotFoundException $e) {
            return wrap_response(false, "Wrong _id");
        }

        try{ //validate friend id
           $friend = $this->graph->getNodeById($data["friend_id"]);
           if($friend->type!=$this->config->item("user_type"))
               throw new NotFoundException();
        } catch (NotFoundException $e) {
            return wrap_response(false, "Wrong friend_id");
        }
        

        

        $script = "
            START to=node(".$data["_id"]."), from=node(".$data["friend_id"].")
                match to<-[r]-from
                delete r
            ;";
//        die($script);
        $res = $this->graph->performCypherQuery($script);
        return wrap_response(true);

    }
    

}

?>
