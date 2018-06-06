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
  // Create entity
  try {
    switch($entity_type){
      case 'node':
        $entity = new stdClass();
        $entity->type = $bundle;
        node_object_prepare($entity);
        $entity->language = language_default('language');
        break;
      case 'taxonomy_term':
        $vocabulary = taxonomy_vocabulary_machine_name_load($bundle);
        $entity = new stdClass();
        $entity->type = $bundle;
        $entity->vid = $vocabulary->vid;
        $entity->vocabulary_machine_name = $vocabulary->machine_name;
        break;
      case 'user':
        $entity = new stdClass();
        break;
      default:
        return false;
        break;
    }
    
    // Prepare data
    foreach($value as $field_name => $field_value){
      if($field_value || ($field_name == 'status')){
        if(in_array($field_name,['name','title','mail','roles', 'status'])){
          $entity->{$field_name} = $field_value;
        }else{
          $field_settings = _drupal_get_field_data_value_type($entity_type, $field_name, $bundle);
          $field_type = $field_settings['type'];
          if($field_settings['multiple_value'] && is_array($field_value)){
            foreach ($field_value as $value) {
              $entity->{$field_name}[LANGUAGE_NONE][][$field_type] = $value;
            }
          }else{
            $entity->{$field_name}[LANGUAGE_NONE][][$field_type] = $field_value;
          }
        }
      }
    }
    
    // save
    $entity->save();
    return $entity->id();  
  } catch(Exception $e) {}
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
  $entity = false;
  // load entity
  switch($entity_type){
    case 'node':
      $entity = node_load($id);
      break;
    case 'taxonomy_term':
      $entity = taxonomy_term_load($id);
      break;
    case 'user':
      $entity = user_load($id);
      break;
    default:
      return false;
      break;
  }
  if(!$entity){return false;}

  // Prepare data
  foreach ($data as $field_name => $field_value) {
    if(in_array($field_name,['name','title','mail','roles', 'status'])){
      $entity->{$field_name} = $field_value;
    }else{
      $field_settings = _drupal_get_field_data_value_type($entity_type, $field_name, $bundle);
      $field_type = $field_settings['type'];
      if($field_settings['multiple_value'] && is_array($field_value)){
        foreach ($field_value as $value) {
          $entity->{$field_name}[LANGUAGE_NONE][][$field_type] = $value;
        }
      }else{
        $entity->{$field_name}[LANGUAGE_NONE][][$field_type] = $field_value;
      }
    }
  }
  // Save
  try {
    $entity->save();
    return $entity->id();
  } catch(Exception $e) {}
}