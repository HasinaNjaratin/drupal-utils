<?php

/**
 * Function to check if url headers return 200 OK
 *
 * @param $url
 * @return boolean
 */
function _url_is_ok($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $c = curl_exec($ch);
  $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  return ($info==200);
}

/**
 * Function to simplify string    eg. Je suis à la HAUTEUR  =>  je-suis-a-la-hauteur
 *
 * @param $str string to simplify
 * 
 * 
 * @param $separator char
 * If separator is FALSE   eg. Je suis à la HAUTEUR  =>  je suis a la hauteur
 * If separator is '_'   eg. Je suis à la HAUTEUR  =>  je_suis_a_la_hauteur
 * 
 * @return string
 */
function _simplify_string($str, $separator = '-'){
  $str = trim($str,' ');
  $str = preg_replace('#Ç#', 'c', $str);
  $str = preg_replace('#ç#', 'c', $str);
  $str = preg_replace('#è|é|ê|ë#', 'e', $str);
  $str = preg_replace('#È|É|Ê|Ë#', 'e', $str);
  $str = preg_replace('#à|á|â|ã|ä|å#', 'a', $str);
  $str = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'a', $str);
  $str = preg_replace('#ì|í|î|ï#', 'i', $str);
  $str = preg_replace('#Ì|Í|Î|Ï#', 'i', $str);
  $str = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $str);
  $str = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'o', $str);
  $str = preg_replace('#ù|ú|û|ü#', 'u', $str);
  $str = preg_replace('#Ù|Ú|Û|Ü#', 'u', $str);
  $str = preg_replace('#ý|ÿ#', 'y', $str);
  $str = preg_replace('#Ý#', 'y', $str);
  $str = str_replace(array(",",";","'","_",'"'), "", $str);
  if($separator){
    $str = str_replace(' ', $separator, $str);
  }
  return strtolower($str);
}

/**
 * Create subdir FTP eg. ftp_mksubdirs($ftpcon, '/', '/archive/2018/02'); it will create sub directories {archive, 2018,02}
 * 
 * @param type $ftpcon
 * @param type $ftpbasedir
 * @param type $ftpath
 */
function _ftp_mksubdirs($ftpcon, $ftpbasedir, $ftpath) {
  $success = FALSE;

  @ftp_chdir($ftpcon, $ftpbasedir); 
  $parts = explode('/', $ftpath);
  foreach ($parts as $part) {
    if (!@ftp_chdir($ftpcon, $part)) {
      $dir = ftp_mkdir($ftpcon, $part);
      $ch = ftp_chdir($ftpcon, $part);
      ftp_chmod($ftpcon, 0777, $part);
      if ($dir != FALSE OR $ch) {
        $success = TRUE;
      }
    }
  }

  return $success;
}

/**
 * Copy file from directory to another on the same ftp server
 * @param type $ftpcon
 * @param type $src  :: source path
 * @param type $dest :: destination path
 * 
 * @param boolean 
 */
function _ftp_copy($ftpcon, $src, $dest){
  if(ftp_rename($ftpcon, $src, $dest)) {
    echo "SOURCE :: " . $src . PHP_EOL . "DESTINATION :: " . $dest;
    echo PHP_EOL;
    echo "ARCHIVE OK" . PHP_EOL;
    return true;
  }
  return false;
}

/**
 * Get closest value from array
 * 
 * @param type $search
 * @param type $arr
 * 
 * @param value
 */
function _getClosest($search, $arr) {
  if(count($arr)==0){return 0;}
  $search =0;
  $closest = null;
  foreach ($arr as $item) {
      if ($closest === null || abs($search - $closest) > abs($item - $search)) {
          $closest = $item;
      }
  }
  return array_search(abs($closest), $arr) ? abs($closest) : $closest;
}