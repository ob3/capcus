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
class m_event extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
        $this->type = $this->config->item("event_type");
        $this->minimum_data = array('creator', 'name', 'category', 'description',
            'time_start', 'time_end', 'event_type');
        $this->minimum_data = array('creator', 'name');
        $this->allowed_data_input = array_merge($this->minimum_data, array(
            'location', 'latitude', 'longitude', 'poster', 'contact_person'
                ));
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
            $event = $this->graph->getNodeById($_id);
            
            if($event->type!=$this->type)
                return wrap_response(false, "No data Found");
            
            foreach ($data as $key => $val) {
                $event->$key = $val;
            }
            
            $date = new DateTime();
            $event->last_update = $date->getTimestamp();

            $result = $event->save();
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

        try {
            //validate creator is exsist
            $creator = $this->graph->getNodeById($data['creator']);

            //create event node
            $newevent = $this->graph->createNode();
//            $newevent->type = $this->type;
            $newevent->setLabel($this->type);
            $newevent->setLabel("CREATED_BY_".$data['creator']);
            $date = new DateTime();
            $newevent->createddate = $date->getTimestamp();
            foreach ($data as $key => $val) {
                $newevent->$key = $val;
            }
            $result = $newevent->save();

            $relation = $creator->createRelationshipTo($newevent, $this->config->item("relation_event_create"));
            $relation->createddate = $date->getTimestamp();
            $relation->save();

            return wrap_response(true);
        } catch (NotFoundException $e) {
            return wrap_response(false, "No Creator Found");
        }
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
            $event = $this->graph->getNodeById($_id);

            $data['_id'] = $event->getId();
            $data = array_merge($data,$event->getProperties());
//            $event = extract_node_data($event->_data);
            return wrap_response(true, null, array($data));
        } catch (NotFoundException $e) {
            return wrap_response(false, "No data Found");
        }
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

    function delete($criteria) {
        
    }

}

?>
