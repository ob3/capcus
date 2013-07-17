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
class m_freenode extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
//        $this->type = $this->config->item("event_type");
        $this->minimum_data = array('creator', 'name', 'category', 'description',
            'time_start', 'time_end', 'event_type');
        $this->minimum_data = array('creator', 'name');
        $this->allowed_data_input = array_merge($this->minimum_data, array(
            'location', 'latitude', 'longitude', 'poster', 'contact_person'
                ));
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
            START root=node:node_auto_index(type='" . $this->type . "')
            $where
            return root
            ORDER BY root.time_start
                limit $limit
                skip $skip
                ;
            ";
//        die($script);
        $res = $this->graph->performCypherQuery($script);
        $out = extract_node_data($res['data']);
        return $out;
    }

    function rate($label,$data) {
        $minimum = array('_id','target_id','rate');

        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));

        //clean data
        $data = clean_array($minimum, $data);
        
        //No data to add
        if (count($data) < 1)
            return wrap_response(false, "No data To update");
        
        try{ //validate user id
           $user = $this->graph->getNodeById($data["_id"]);
           if(array_search($this->config->item("user_type"), $user->getLabel())===false)
                   throw new NotFoundException();

        } catch (NotFoundException $e) {
            return wrap_response(false, $this->config->item("user_type")." not found");
        }

        try{ //validate target id
           $target = $this->graph->getNodeById($data["target_id"]);
           if(array_search($label, $target->getLabel())===false)
               throw new NotFoundException();
           
           $current_rate=isset($target->rate)?$target->rate:$data['rate'];
           $target->rate = ($target->rate + $data['rate'])/2;
           $target->save();
           
        } catch (NotFoundException $e) {
            return wrap_response(false, $label." not found");
        }
        
        
        unset($data['_id']);
        unset($data['target_id']);
        
        $relation = $user->createRelationshipTo($target, $this->config->item("relation_rate"));
        
        foreach ($data as $key => $value){
            $relation->$key = $value;
        }
        
        $date = new DateTime();
        
        $relation->createddate = $date->getTimestamp();
        $relation->save();
        return wrap_response(true);   
    }
    
    function like($label,$data) {
        $minimum = array('_id','target_id');

        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));

        //clean data
        $data = clean_array($minimum, $data);
        
        //No data to add
        if (count($data) < 1)
            return wrap_response(false, "No data To update");
        
        try{ //validate user id
           $user = $this->graph->getNodeById($data["_id"]);
           if(array_search($this->config->item("user_type"), $user->getLabel())===false)
                   throw new NotFoundException();

        } catch (NotFoundException $e) {
            return wrap_response(false, $this->config->item("user_type")." not found");
        }

        try{ //validate friend id
           $target = $this->graph->getNodeById($data["target_id"]);
           if(array_search($label, $target->getLabel())===false)
               throw new NotFoundException();
        } catch (NotFoundException $e) {
            return wrap_response(false, $label." not found");
        }
        
        
        unset($data['_id']);
        unset($data['target_id']);
        
        $relation = $user->createRelationshipTo($target, $this->config->item("relation_like"));
        
        foreach ($data as $key => $value){
            $relation->$key = $value;
        }
        
        $date = new DateTime();
        
        $relation->createddate = $date->getTimestamp();
        $relation->save();
        return wrap_response(true);
    }
    
    function get_relation_summary($_id){
        $script = "
        START root=node($_id)
        match node-[rel]-root
        RETURN type(rel) as name, count(root) as count
        ";
        
        $res = $this->graph->performCypherQuery($script);
        $out=array();
//        print_r($res);
        foreach($res['data'] as $key => $val){
//            $out[]=array(
//                    $res['columns'][0] => $val[0],
//                    $res['columns'][1] => $val[1]
//                );
            $out[$val[0]] = $val[1];
        }
        return $out;
    }
    
    function create_relation($data){
        $required = array('from','to','relation_data');
        extract($data);
        
        foreach ($required as $val){
            if(!isset($$val)){
                return wrap_response (false, "required data not satisfied: ".  implode(", ", $required));   
                
            }
        }
        

//-----------validating node from and to

        //validate node minimum field
        $minimum_node=array("_id");


        //validate node from
        if(is_array($from)){
            if (!is_array_satisfied($minimum_node, $from))
                return wrap_response(false, "Minimum data not satisfied: from " . implode(", ", $minimum_node));                        
        }else{
            if(get_class($from) != "Node"){
                    $from = json_decode($from,true);
                    return wrap_response(false, "Minimum data not satisfied: from " . implode(", ", $minimum_node));                        
            }            
        }
        
        //validate node to
        if(is_array($to)){
            if (!is_array_satisfied($minimum_node, $to))
                return wrap_response(false, "Minimum data not satisfied: from " . implode(", ", $minimum_node));                        
        }else{
            if(get_class($from) != "Node"){
                    $to = json_decode($to,true);
                    return wrap_response(false, "Minimum data not satisfied: from " . implode(", ", $minimum_node));                        
            }            
        }

        

        if(is_array($from)){
            try{ //validate user id
                $nodefrom = $this->graph->getNodeById($from['_id']);
                if(isset($from['label'])){
                    if(array_search($from['label'], $nodefrom->getLabel())===false)
                            throw new NotFoundException();               
                }
            } catch (NotFoundException $e) {
                return wrap_response(false, "_id ". $from['_id']." Not found");
            }
        }else{
            $nodefrom=$from;
            unset($from);
        }

        if(is_array($to)){
            try{ //validate to id
                $target = $this->graph->getNodeById($to['_id']);
                if(isset($to['label'])){
                    if(array_search($to['label'], $target->getLabel())===false)
                            throw new NotFoundException();               
                }
            } catch (NotFoundException $e) {
                return wrap_response(false, "_id ". $to['_id']." Not found");
            }
        }else{
            $target = $to;
        }
        
//------------validate node ends here --------------------
        
        
//------------start validating relation to create---------
        //validate relation type

        $relation = $this->is_relation_valid($relation_data);
        if(!$relation->status)
            return $relation;
        
        $relation=$relation->data;
        
        
        // match relation for new subnode or not
        if($relation['is_create'] === $target->isSaved()){
            if($relation['is_create']){
                return wrap_response(false, "Relation only allowed for new subnode");
            }else{
                return wrap_response(false, "Relation only allowed for exsist subnode");
            }
        }

        //force value for configured attributes
        if(isset($relation['force_value'])){
            foreach($relation['force_value'] as $key => $value){
                $relation_data[$key]=$value;
            }            
        }
        
        //validate valid value for some attributes
        if(isset($relation['validate_value'])){
            foreach($relation_data as $key => $val){
                if(isset($relation['validate_value'][$key])){
                    if(array_search($val, $relation['validate_value'][$key])===false)
                        return wrap_response(false, $val." is Not Valid value for $key. Choose one of: ". implode(", ", $relation['validate_value'][$key]));
                }
            }            
        }
        
//        if(!is_array($relation_data))
//            $relation_data = json_decode($relation_data,true);
        //validate minimum field for relation
//        if (!is_array_satisfied($relation['minimum_data'], $relation_data))
//            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $relation['minimum_data']));
        
        $relation_data = clean_array($relation['allowed_data'], $relation_data);




        if (count($relation_data) < 0)
            return wrap_response(false, "No data To update");

        
        //validate relationship allowed start node
        if(count($relation['valid_start'])>0){
            $diff = array_diff($relation['valid_start'],$nodefrom->getLabel());
            if(count($diff) === count($relation['valid_start']))
                return wrap_response(false, "relation start only allowed for: ".implode(", ",$relation['valid_start']));            
        }
        
        //validate relationship allowed end node        
        if(count($relation['valid_end'])>0){
            $diff = array_diff($relation['valid_end'],$target->getLabel());
            if(count($diff) === count($relation['valid_end']))
                return wrap_response(false, "relation end only allowed for: ".implode(", ",$relation['valid_end'])." found: ". implode(", ",$target->getLabel()));
        }
        
        if(!$target->isSaved()){
            $target->save();
        }

        $relation = $nodefrom->createRelationshipTo($target, $relation['name']);

        foreach ($relation_data as $key => $value){
            $relation->$key = $value;
        }
        
        $date = new DateTime();
        
        $relation->createddate = $date->getTimestamp();
        $relation->save();

        return wrap_response(true);
    }
    
    function is_valid_node($data){
        $minimum = array('_id');
        if (!is_array_satisfied($minimum, $data))
            return wrap_response(false, "Minimum data not satisfied: " . implode(", ", $minimum));
        
        try{ //validate user id
            if(!is_array($data))
                $data = json_decode ($data,true);

           $node = $this->graph->getNodeById($data["_id"]);
           if(isset($data["label"])){
            if(array_search($data["label"], $node->getLabel())===false)
                    return wrap_response(false, $data["_id"]." Not Valid ".$data["label"]);
           }
           return wrap_response(true,null,$node);
           
        } catch (NotFoundException $e) {
            return wrap_response(false, $data["_id"]." Not Valid");
        }
        
    }
    

    function is_relation_valid($relation_data){
        if(!is_array($relation_data))
            return wrap_response(false, "invalid relation data relation data should be array");
        
        $minimum = array('relation_type');
        if (!is_array_satisfied($minimum, $relation_data))
            return wrap_response(false, "Minimum relation_data not satisfied: " . implode(", ", $minimum));
        
        $label=$this->config->item("relation_type");
        if(!isset($label[$relation_data['relation_type']])){
            $valid_relation_type="";
            foreach($label as $key => $val){
                $valid_relation_type.=$key.", ";
            }
            $valid_relation_type = substr($valid_relation_type, 0,-2);
            return wrap_response(false, "relation_type not one of: ".$valid_relation_type);
        }


        $label=$label[$relation_data['relation_type']];
//        if(!is_array($relation_data))
//            $relation_data = json_decode ($relation_data,true);

        
        if (!is_array_satisfied($label['minimum_data'], $relation_data))
            return wrap_response(false, "Minimum relation_data not satisfied: " . implode(", ", $label['minimum_data']));
        
//        return wrap_response(true, null, $label[$relation_type]);
        return wrap_response(true,null, $label);
    }
    
    function create_subnode($data_full){
        
        //validate relation type & data
//        $relation = $this->is_relation_valid($data_full['relation_data']);
//        if(!$relation->status){
//            return $relation;
//        }
//        
//        if(!$relation->data['is_create']){
//            return wrap_response(false, "{$data_full['relation_data']['relation_type']} not allowed on create mode");
//        }


        //validate relationship allowed start node
//        if(count($relation->data['valid_start'])>0){
//            $diff = array_diff($relation->data['valid_start'],$data_full['from']->getLabel());
//            if(count($diff) === count($relation->data['valid_start']))
//                return wrap_response(false, "relation start only allowed for: ".implode(", ",$relation->data['valid_start']));            
//        }
        
        $data = $data_full['to'];
        $required = array('subnode_type');

        if(!is_array($data))
            $data = json_decode($data,true);
//        extract($data);

        foreach ($required as $val){

            if(!isset($data[$val]))
                return wrap_response (false, "required data on subnode not satisfied: ".  implode(", ", $required));   
        }
        
        $avaliable_subnode=$this->config->item("subnode_type");

        // VALIDATE SUBNODE TYPE EXSIST IN CONFIG
        if(!isset($avaliable_subnode[$data['subnode_type']])){
            $valid_subnode_type="";
            foreach($avaliable_subnode as $key => $val){
                $valid_subnode_type.=$key.", ";
            }
            $valid_subnode_type = substr($valid_subnode_type, 0,-2);
            return wrap_response (false, "subnode_type not one of: ".  $valid_subnode_type);
        }
        
        $subnode_type=$avaliable_subnode[$data['subnode_type']];
        unset($avaliable_subnode);
        

        //validate relationship allowed end node        
//        if(count($relation->data['valid_end'])>0){
//            $diff = array_diff($relation->data['valid_end'],array($subnode_type['label']));
//            
//            if(count($diff) === count($relation->data['valid_end']))
//                return wrap_response(false, "relation end only allowed for: ".implode(", ",$relation->data['valid_end'])." found: ". $subnode_type['label']);
//        }
        

        
        $creator = $data_full['from']->getId();
        if (!is_array_satisfied($subnode_type['minimum_data'], $data))
            return wrap_response(false, "Minimum subnode data not satisfied: " . implode(", ", $subnode_type['minimum_data']));

        $data = clean_array($subnode_type['allowed_data'], $data);
        
        //validate valid value for some attributes
        if(isset($subnode_type['validate_value'])){
            foreach($data as $key => $val){
                if(isset($subnode_type['validate_value'][$key])){
                    if(array_search($val, $subnode_type['validate_value'][$key])===false)
                        return wrap_response(false, $val." is Not Valid value for $key. Choose one of: ". implode(", ", $subnode_type['validate_value'][$key]));
                }
            }            
        }

        //force value for configured attributes
        if(isset($subnode_type['force_value'])){
            foreach($subnode_type['force_value'] as $key => $value){
                $data[$key]=$value;
            }            
        }
        

        $subnode = $this->graph->createNode();
        $subnode->setLabel($subnode_type['label']);
        $subnode->setLabel("CREATED_BY_$creator");
        
        
        foreach ($data as $key => $val) {
            $subnode->$key = $val;
        }        
        $date = new DateTime();
        $subnode->createddate = $date->getTimestamp();
//        $result = $subnode->save();

        
        $data_full['to']=$subnode;

        $ret = $this->create_relation($data_full);

        return $ret;
        
    }
    
    function delete_relation(){
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
