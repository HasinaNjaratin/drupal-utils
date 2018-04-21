<?php

/**
 * Function to check if url headers return 200 OK
 *
 * @param $url
 * @return boolean
 */
function url_is_ok($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $c = curl_exec($ch);
  $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  return ($info==200);
}