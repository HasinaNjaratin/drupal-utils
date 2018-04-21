<?php

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
  $query = \Drupal::database()->select('node_field_data', 'n');
  $query->addField('n', 'nid');
  $query->addField('n', 'type');
  $query->addField('n', 'title');
  if(!empty($fields)){
      foreach ($fields as $field_name => $type) {
          $query->addField($field_name, $field_name.'_'.$type);
          $query->addJoin('left', 'node__'.$field_name, $field_name, $field_name.'.entity_id = n.nid');
      }
  }
  $query->condition('n.nid', $nid);
  $results = $query->execute()->fetchAll();
  return $results ? $results[0] : [];
}