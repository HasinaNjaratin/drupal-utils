<?php

/**
 * Get full data of entity
 *
 * @param $entity
 *  obj
 *
 * @param $entity_reference_term
 *  array to list field referencing taxonomy_term
 *
 * @param $entity_reference_node
 *  array to list field referencing node
 *
 * @param $field_collection
 *  array to list field collection
 * 
 * @return array
 * 
 */
function _drupal_get_entity_full_data($entity, $entity_reference_term=null, $entity_reference_node=null, $field_collection=null){
  $full = [];
  if (is_object($entity)) {
    $data = (array) $entity;
    if(isset($data['nid'])){
      $full['nid'] = $data['nid'];
      $full['title'] = $data['title'];
    }
    foreach ($data as $field_name => $field_value) {
      if ((strpos($field_name, 'field_') !== false) && empty($field_value)) {
        foreach ($field_value[LANGUAGE_NONE] as $key => $value) {
          switch (key($value)) {
            //  ---- ENTITY REFERENCE
            case 'target_id':
              /* taxonomy */
              if(!is_null($entity_reference_term) && in_array($field_name, $entity_reference_term)){
                $g_val = [
                  'tid' => $value['target_id'],
                  'name' => taxonomy_term_load($value['target_id'])->name,
                ];
              }
              /* node */
              elseif(!is_null($entity_reference_node) && in_array($field_name, $entity_reference_node)){
                $g_val = [
                  'nid' => $value['target_id'],
                  'title' => node_load($value['target_id'])->title
                ];
              }
              break;
            
            // ---- TERM REFERENCE
            case 'tid':
              $g_val = [
                'tid' => $value['tid'],
                'name' => taxonomy_term_load($value['tid'])->name,
              ];
              break;

            // ---- FILE
            case 'fid':
              $g_val = [
                'fid' => $value['fid'],
                'url' => file_create_url($value['uri'])
              ];
              break;
          
            // ---- DEFAULT VALUE
            default:
              $g_val = (!is_null($field_collection) && in_array($field_name, $field_collection)) ? _drupal_get_field_collection_value(array($value[key($value)])) : $value[key($value)];
              break;
          }
          if(count($field_value[LANGUAGE_NONE]) > 1){ 
            $full[$field_name][] = $g_val; 
          }else{
            $full[$field_name] = $g_val;
          }
        }
      }
    }
  }
  return $full;
}

/**
 * Get data from field collection
 *
 * @param $fc_id
 *  array id(s) of the field collection
 *
 * @return array
 * 
 */
function _drupal_get_field_collection_value($fc_id, $entity_reference_term=null, $entity_reference_node=null){
  $data = [];
  $field_collections = entity_load('field_collection_item', $fc_id);
  foreach($field_collections as $id => $field_collection){
    $data[] = _drupal_get_entity_full_data($field_collection, $entity_reference_term, $entity_reference_node);
  }
  return $data;
}

/**
 * Get fields value without entity load
 * 
 * @param $nid
 * 
 * @param $fields
 * array field_name -> {value, target_id, fid}
 *
 * @return obj
 */
function _drupal_get_fields_value($nid, $fields=[]){
  $query = db_select('node', 'n');
  $query->addField('n', 'nid');
  $query->addField('n', 'type');
  $query->addField('n', 'title');
  if(!empty($fields)){
    foreach ($fields as $field_name => $type) {
      $query->addField($field_name, $field_name . '_' . $type);
      $query->addJoin('left', 'field_data_' . $field_name, $field_name, $field_name.'.entity_id = n.nid');
    }
  }
  $query->condition('n.nid', $nid);
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
  $query = $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', $entity_type);
  if($bundle){
    $query->entityCondition('bundle', array($bundle));
  }
  $execute_query = FALSE;
  foreach ($fields as $field_name => $field) {
    if($field['value']){
      $execute_query = TRUE;
      if (in_array($field_name, $base_field)) {
        $query->propertyCondition($field_name, $field['value']);
      } else {
        if($field['op'] == 'like'){
          $query->fieldCondition($field_name, $field['type'], '%' . db_like($field['value']) . '%', 'like');
        }else {
          $query->fieldCondition($field_name, $field['type'], $field['value'], $field['op']);
        }
      }
    }
  }
  $result = $execute_query ? $query->execute() : [];
  if(empty($result)){ return NULL;}

  $result = array_keys($result[$entity_type]);
  if(!$force_all_result){
    return $result[0];
  }
  return sizeof($result) == 1 ? $result[0]  : $result;
}

?>