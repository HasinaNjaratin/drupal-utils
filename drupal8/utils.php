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
 *  {value, target_id}
 *  
 *  @return boolean
 */
function _drupal_is_value_in_node_field($nid, $field, $value, $type = 'value'){
  $query = \Drupal::database()->select('node__' . $field, 'f');
  $query->addField('f', 'entity_id');
  $query->condition('f.entity_id', $nid);
  $query->condition('f.'.$field . '_' . $type, $value);
  $record = $query->execute()->fetchCol();
  return $record ? TRUE : FALSE;
}

/**
 *  Get http request (Webservices)
 * 
 *  @param $url (string)
 * 
 *  @param $auth (array) ['username' => '', 'password' => '']
 *  
 *  @return data
 */
function _drupal_get_data_webservice($url, $auth = FALSE){
  $client = \Drupal::httpClient();
  try {
    if($auth){
      $response = $client->get($url, [
        'auth' => [$auth['username'], $auth['password']]
      ]);
    }else{
      $response = $client->get($url);
    }
    return ($response->getStatusCode() == 200) ? $response->getBody()->getContents() : FALSE;
  }
  catch (RequestException $e) {}
  return FALSE;
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
function _drupal_get_field_data_value_type($entity_type, $field_name, $bundle_name) {
  $field_type = "string";
  $field_target_type = "value";
  $field_isMultiple = FALSE;
  if(!empty(\Drupal\field\Entity\FieldStorageConfig::loadByName($entity_type, $field_name))) { 
    $settings = \Drupal\field\Entity\FieldStorageConfig::loadByName($entity_type, $field_name);
    $field_isMultiple = $settings->get('cardinality') == -1 ? TRUE : FALSE;
    $field_type = $settings->get('type');
    $field_target_type = ($field_type == 'entity_reference') ? 'target_id' : 'value';
  } 
  return [
    'type' => $field_type,
    'target_type' => $field_type,
    'multiple_value' => $field_isMultiple
  ];
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
  $entity = [];
  // Prepare data
  foreach($value as $field_name => $field_value){
    if($field_value || ($field_name == 'status')){
      $field_settings = _drupal_get_field_data_value_type($entity_type, $field_name, $bundle);
      $field_target_type = $field_settings['target_type'];
      if(in_array($field_name,['name','title','mail','roles', 'status'])){
        $entity[$field_name] = $field_value;
      }elseif($field_settings['multiple_value'] && is_array($field_value)){
        $field_values = [];
        foreach ($field_value as $item_value) {
          $field_values[] = [$field_target_type => $item_value];
        }
        $entity[$field_name] = $field_values;
      }else{
        $entity[$field_name][$field_target_type] = $field_value;
      }
    }
  }
  // Create entity
  try {
    switch($entity_type){
      case 'node':
        $entity['type'] = $bundle;
        $entity['langcode'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $entity = \Drupal\node\Entity\Node::create($entity);
        break;
      case 'taxonomy_term':
        $entity['vid'] = $bundle;
        $entity = \Drupal\taxonomy\Entity\Term::create($entity);
        $entity->enforceIsNew();
        break;
      case 'user':
        $entity =  \Drupal\user\Entity\User::create($entity);
        break;
      default:
        $keyLog = '';
        break;
    }
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
  // load entity
  switch($entity_type){
    case 'node':
      $entity = \Drupal\node\Entity\Node::load($id);
      $keyLog = $data['title'];
      break;
    case 'taxonomy_term':
      $entity = \Drupal\taxonomy\Entity\Term::load($id);
      $keyLog = $data['name'];
      break;
    case 'user':
      $entity =  \Drupal\user\Entity\User::load($id);
      $keyLog = $data['name'];
      break;
    default:
      break;
  }
  // Prepare data
  foreach ($data as $field_name => $field_value) {
    if(!$field_value && ($field_name != 'status')){
      $entity->set($field_name, NULL);
    }else{
      $field_settings = _drupal_get_field_data_value_type($entity_type, $field_name, $bundle);
      $field_target_type = $field_settings['target_type'];
      if($field_settings['multiple_value'] && is_array($field_value)){
        // Current values
        $current_values = [];
        foreach ($entity->get($field_name)->getValue() as $key => $value) {
          $current_values[] = $value[$field_target_type];
        }
        // New values
        foreach ($field_value as $item_value) {
          if(!in_array($item_value,$current_values)){
            $current_values[] = [$field_target_type => $item_value];
          }
        }
        // Set value
        $field_value = $current_values;
        $entity->set($field_name, $field_value);
      }else{
        $entity->set($field_name, $field_value);
      }
    }
  }
  // Save
  try {
    $entity->save();
    return $entity->id();
  } catch(Exception $e) {}
}