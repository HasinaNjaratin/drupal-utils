<?php

/**
 *  Check if value is in field of specific node
 *  
 *  @param $nid 
 *  node ID
 *  
 *  @param $field (string)
 *  field_name
 *   
 *  @param $value (string)
 *  field_value
 * 
 *  @param $type (string)
 *  {value, target_id, tid}
 *  
 *  @return boolean
 */
function _drupal_is_value_in_node_field($nid, $field, $value, $type = 'value'){
  $query = db_select('node__' . $field, 'f');
  $query->addField('f', 'entity_id');
  $query->condition('f.entity_id', $nid);
  $query->condition('f.' . $field . '_' . $type, $value);
  $record = $query->execute()->fetchCol();
  return $record ? TRUE : FALSE;
}

/**
 *  Get http request (Webservices)
 * 
 *  @param $url (string)
 *  
 *  @return data
 */
function _drupal_get_data_webservice($url){
  $response = drupal_http_request($url);
  return $response->data;
}