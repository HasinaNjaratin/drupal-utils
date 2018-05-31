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