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