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