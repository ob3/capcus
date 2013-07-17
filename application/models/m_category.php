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
class m_category extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
        $this->type = "CATEGORY";
        $this->minimum_data = array('creator', 'name', 'description', 'time_start', 'time_end', 'event_type');
        $this->minimum_data = array('creator', 'name');
        $this->allowed_data_input = array(
            'creator', 'name', 'description', 'time_start', 'time_end',
            'location', 'latitude', 'longitude',
            'poster', 'event_type', 'contact_person',
            'category'
        );
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
            foreach ($data as $key => $val) {
                $user->$key = $val;
            }
            $date = new DateTime();
            $user->last_update = $date->getTimestamp();

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

        try {
            //validate creator is exsist
            $creator = $this->graph->getNodeById($data['creator']);

            //create event node
            $newevent = $this->graph->createNode();
            $newevent->type = $this->type;
            $date = new DateTime();
            $newevent->created_date = $date->getTimestamp();
            foreach ($data as $key => $val) {
                $newevent->$key = $val;
            }
            $result = $newevent->save();

            $relation=$creator->createRelationshipTo($newevent, 'CREATE');
            $relation->created_date=$date->getTimestamp();
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
            $user = $this->graph->getNodeById($_id);

//            var_dump($user->_data);die;
            $user = extract_node_data($user->_data);
            return wrap_response(true, null, $user);
        } catch (NotFoundException $e) {
            return wrap_response(false, "No data Found");
        }
    }

    function retrive($criteria) {
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
