<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');



//MAIN NODE LABEL
$config['user_type'] = "USER";
$config['event_type'] = "USER_EVENT";


//RELATIONSHIP NAME
$config['relation_type'] = array(
    'relation_event_create' => array(
                                "name" => "CREATE_EVENT",
                                "minimum_data" => array(),
                                "allowed_data" => array(),
                                "valid_start"  => array("USER"),
                                "valid_end"    => array("USER_EVENT"),
                                "is_create"    => TRUE,
                               ),
    'relation_group_create' => array(
                                "name" => "CREATE_GROUP",
                                "minimum_data" => array("name"),
                                "allowed_data" => array(),
                                "valid_start"  => array("USER"),
                                "valid_end"    => array("USER_GROUP"),
                                "is_create"    => TRUE,
                               ),
    'relation_friendship'   => array(
                                "name" => "FRIEND_WITH",
                                "minimum_data" => array(),
                                "allowed_data" => array("status"),
                                "valid_start"  => array("USER"),
                                "valid_end"    => array("USER"),
                                "force_value"   => array(
                                                "status"  => "REQUESTED",
                                              ),
                                "validate_value"=> array(
                                                "status"  => array("CONFIRMED","REQUESTED"),
                                              ),
                                "is_create"    => FALSE,
                               ),
    'relation_rate'         => array(
                                "name" => "RATE",
                                "minimum_data"  => array("rate"),
                                "allowed_data"  => array(),
                                "valid_start"   => array("USER"),
                                "valid_end"     => array("USER_EVENT","USER_GROUP"),
                                "force_value"   => array(),
                                "validate_value"=> array(
                                                "rate"  => array(1,2,3,4,5),
                                              ),
                                "is_create"     => FALSE,
                                ),
    'relation_like'         => array(
                                "name" => "LIKE",
                                "minimum_data" => array(),
                                "allowed_data" => array(),
                                "valid_start"  => array("USER"),
                                "valid_end"    => array("USER_EVENT","USER_GROUP"),
                                "is_create"    => FALSE,
                                ),
    'relation_attend'       => array(
                                "name" => "ATTEND",
                                "minimum_data" => array(),
                                "allowed_data" => array(),
                                "valid_start"  => array("USER"),
                                "valid_end"    => array("USER_EVENT"),
                                "is_create"    => FALSE,
                                ),
    'relation_comment'      => array(
                                "name" => "COMMENT",
                                "minimum_data" => array('comment'),
                                "allowed_data" => array(),
                                "valid_start"  => array("USER"),
                                "valid_end"    => array(),
                                "is_create"    => FALSE,
                                ),
);
//merging allowed data to allowed+minimum
foreach ($config['relation_type'] as $key => $value) {
    $config[$key] = $value["name"];
    $config['relation_type'][$key]['allowed_data']=  array_merge($config['relation_type'][$key]['minimum_data'],$config['relation_type'][$key]['allowed_data']);
}

//NODE NAME
$config['subnode_type'] = array(
        "subnode_group" => array(
                            "label" => "USER_GROUP",
                            "minimum_data" => array("name"),
                            "allowed_data" => array(),
                            "force_value"   => array(),
                            "validate_value"=> array(),
                        ),
        "subnode_event" => array(
                            "label" => "USER_EVENT",
                            "minimum_data"  => array("name","category"),
                            "allowed_data"  => array("description"),
                            "force_value"   => array(),
                            "validate_value"=> array(
                                                "category"  => array(1,2,3),
                                              ),
            
                        ),
);

//merging allowed data to allowed+minimum
foreach($config['subnode_type'] as $key => $value){
    $config['subnode_type'][$key]['allowed_data']=  array_merge($config['subnode_type'][$key]['minimum_data'],$config['subnode_type'][$key]['allowed_data']);
}

//RELATIONSHIP STATUS
$config['relation_friendship_value'] = array(
    'confirmed' => 'CONFIRMED',
    'requested' => 'REQUESTED'
);
$config['relation_friendship_confirmed'] = "CONFIRMED";
$config['relation_friendship_requested'] = "REQUESTED";
