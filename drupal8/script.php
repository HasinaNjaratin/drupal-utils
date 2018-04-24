<?php

/**
 * Manipulating from ftp server
 */
function _ftp_manipulating(){
  // Config
  $config = [
    'host' => '185.145.32.22',
    'port' => '21',
    'username' => 'planotheque',
    'password' => '.zqpNKI2pJlrM',
    'remote_directory' => 'documents',
  ];

  try {
    // Set up basic connection
    $conn_id = ftp_connect($config['host']);
    // Login with username and password
    $login_result = ftp_login($conn_id, $config['username'], $config['password']);
    // Test fail connect ftp.
    if ((!$conn_id) || (!$login_result)) {
      die("error connexion!");
    } else {
      ftp_pasv($conn_id, TRUE);
      $files = @ftp_nlist($conn_id, $config['remote_directory']);
      foreach ($files as $file) {
        $remote_file = $config['remote_directory'] . '/' . $file;
        $local_dir = '/documents/';
        $local_file = drupal_realpath('public://') . $local_dir . DIRECTORY_SEPARATOR . $file;
        $uri = 'public://' . $local_dir;
        // create local directory if not exist
        file_prepare_directory($uri, FILE_CREATE_DIRECTORY);
        // download file
        if(ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY)){
          // manage file => return file object
          $file_saved = system_retrieve_file($local_file, NULL, TRUE, FILE_EXISTS_REPLACE);
          // upload file to archive directory on the server
          $dest = '/archives/' . $file;
          $cp = ftp_put($conn_id, $dest, $local_file, FTP_BINARY);
          // delete old file
          $del = ftp_delete($conn_id, $remote_file);
          if ($cp && $del) {
            echo 'OK' . PHP_EOL . PHP_EOL;
          }
        }
      }
      @ftp_close($login_result);
    }
  } catch (Exception $e) {
    die("error!");
  }
}

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