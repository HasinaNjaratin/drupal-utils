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

/**
 *  Script to create entity element
 * 
 *  @param $entity_type (node, user, taxonomy_term)
 * 
 *  @param $bundle type
 * 
 *  @param $value array [fieldname => fieldvalue]
 *  
 *  @return id
 */
function _drupal_create_entity($entity_type, $bundle, $value = []){
  
}

/**
 *  Script to update entity element
 * 
 *  @param $entity_type (node, user, taxonomy_term)
 * 
 *  @param $bundle type
 * 
 *  @param $id (int) entity id
 * 
 *  @param $data array [fieldname => fieldvalue]
 *  
 *  @return id
 */
function _drupal_update_entity($entity_type, $bundle, $id, $data){

}

/**
 * Function to get the data_type value of the field
 *
 * @param $entity_type
 *  type of the entity
 *
 * @param $bundle_name
 *  entity bundle name
 *
 * @param $field_name
 *  name of the field to get the data types
 *
 * @return $field_type
 */
function _drupal_get_field_data_value_type($field_name) {
  switch (field_info_field($field_name)['type']) {
    case 'entityreference':
      $field_type = "target_id";
      break;
    case 'taxonomy_term_reference':
      $field_type = "tid";
      break;
    case 'file':
      $field_type = "fid";
      break;
    default:
      $field_type = "value";
      break;
  }

  return $field_type;
}