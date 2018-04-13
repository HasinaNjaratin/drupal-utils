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
function _drupal_get_node_full_data($entity, $entity_reference_term=null, $entity_reference_node=null, $field_collection=null){
  $full = [];
  if (is_object($entity)) {
    $data = (array) $entity;
    $full = [
      'nid' => $data['nid'],
      'title' => $data['title'],
    ];
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
              /* field collection */
              if(!is_null($field_collection) && in_array($field_name, $field_collection)){
                $g_val = _drupal_get_field_collection_value(key($value));
              }else{
                $g_val = $value[key($value)];
              }
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
 *  id of the field collection
 *
 * @return array
 * 
 */
function _drupal_get_field_collection_value($fc_id){
  return [];
}

?>