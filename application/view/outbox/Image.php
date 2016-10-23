<?php

/**
 * share
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: share.php UTF-8 , 02-Jun-2013 | 00:54:11 nwdo ε
 */

define("_SECURE_PHP", true);
include_once '../../../init.php';
$res = $core->client_resolution(false);
$resolution = isset($_GET['reso']) ? $_GET['reso'] : ($res ? $res[2] : '1024x768');
//print_r($resolution);
$image = rtrim(sanitise($_GET['image']), "/");

//print_r($_GET); echo 'r><br><br><br><br><br>';
$headers    = apache_request_headers(); 

$ImageFile = UPLOAD_PATH.$_GET['uidir'].'/view/'.$resolution.'/'.$image;

if(!is_file($ImageFile) && !file_exists($ImageFile)){
  $ImageFile = UPLOAD_PATH.$fhand->createImageViewUrl($image, $_GET['uidir'], $resolution);
}

if($ImageFile){
$file_time  = filemtime($ImageFile);

header('Cache-Control: must-revalidate');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $file_time).' GMT');
  $extension = strtolower(pathinfo($ImageFile, PATHINFO_EXTENSION));
  $headImg = array('jpg', 'png', 'gif', 'jpeg');
  if($extension == 'jpg'){
     $imghead = 'jpeg';
  }else{
     $imghead = $extension;
  }
  header("Content-Type: image/".$imghead);
  
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $file_time)) {
   
    header('HTTP/1.1 304 Not Modified');
    header('Connection: close');

}else{   
  @readfile($ImageFile);
  exit();
}

}
?>