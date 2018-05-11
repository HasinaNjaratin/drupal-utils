<?php

/**
 * Get fields value without entity load
 * 
 * @param $entity_type
 * {node,user}
 * 
 * @param $entity_id
 * 
 * @param $fields
 * array field_name -> {value, target_id, fid}
 *
 * @return obj
 */
function _drupal_get_fields_value($entity_type, $entity_id, $fields=[]){
  switch ($entity_type) {
    case 'node':
      $p = 'node__';
      $i = 'nid';
      $query = \Drupal::database()->select('node_field_data', 'e');
      $query->addField('e', 'nid');
      $query->addField('e', 'type');
      $query->addField('e', 'title');
      $query->addField('e', 'status');
      break;
    case 'user':
      $p = 'user__';
      $i = 'uid';
      $query = \Drupal::database()->select('users_field_data', 'e');
      $query->addField('e', 'uid');
      $query->addField('e', 'mail');
      $query->addField('e', 'name');
      $query->addField('e', 'status');
      break;
    default:
      return [];
      break;
  }

  if(!empty($fields)){
    foreach ($fields as $field_name => $type) {
      $query->addField($field_name, $field_name.'_'.$type);
      $query->addJoin('left', $p.$field_name, $field_name, $field_name.'.entity_id = e.'.$i);
    }
  }
  $query->condition('e.'.$i, $entity_id);
  $results = $query->execute()->fetchAll();
  return $results ? $results[0] : [];
}

/**
 * Function to get existing entity from specific field
 *
 * @param $entity_type
 *  type of the entity {node, taxonomy_term, user}
 *
 * @param $bundle
 *  entity bundle name
 *
 * @param $fields
 *  [field_name => ['value' => field_value, 'type' => field_type, 'op' => op]]
 *
 * @param $base_field
 *  array containing the base field of the entity
 *
 * @param $force_all_result (bool)
 *  get all result if several
 *
 * @return entity id
 */
function _drupal_get_entity_by_field($entity_type, $bundle = FALSE, $fields, $base_field, $force_all_result = TRUE) {
  $query = \Drupal::entityQuery($entity_type);
  // bundle
  if($bundle){
    switch ($entity_type) {
      case 'taxonomy_term':
        $query->condition('vid', $bundle);
        break;
      case 'node':
        $query->condition('type', $bundle);
        break;
      default:
        break;
    }
  }

  $execute_query = FALSE;
  // query fields
  foreach ($fields as $field_name => $value) {
    if($value){
      $execute_query = TRUE;
      if (in_array($field_name, $base_field)) {
        $query->condition($field_name, $value);
      }else {
        $field_settings = get_field_data_value_type($entity_type, $field_name, $bundle);
        $field_type = $field_settings['type'];
        $query->condition($field_name, $value);
      }
    }
  }
  // execute query
  $result = $execute_query ? $query->execute() : [];
  if(empty($result)){ return NULL;}
  // prepare return
  $result = array_values($result);
  if(!$force_all_result){
      return $result[0];
  }
  return sizeof($result) == 1 ? $result[0]  : $result;
}

/**
 *  Get url by fid
 * 
 *  @param $fid (int)
 *  create url
 *
 * @return url
 */
function _drupal_get_url_by_fid($fid){
  $query = \Drupal::database()->select('file_managed', 'f');
  $query->addField('f', 'uri');
  $query->condition('f.fid', $fid);
  $result = $query->execute()->fetchAll();
  return !empty($result) ? file_create_url($result[0]->uri) : '';
}

/**
 *  Get tid by name
 *  
 *  @param $name (string)
 *  term name
 *  
 *  @param $force_all_result (bool)
 *   
 *  @param $vocabulary_name (str)
 *  
 *  @return array/int (tid)
 */
function _drupal_get_tid_by_name($name, $force_all_result = TRUE, $vocabulary_name = FALSE){
  return _drupal_get_entity_by_field('taxonomy_term', $vocabulary_name, ['name' => ['value' => $name]], ['name'], $force_all_result);
}