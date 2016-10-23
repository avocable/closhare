<?php

/**
 * runtime
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: runtime.php UTF-8 , 06-Jun-2013 | 21:02:46 nwdo ε
 */
?>
<?php
  if (!defined("_SECURE_PHP"))
      die('Direct access to this location is not allowed.');

/**
 * 
 * @param type $icon
 * @return type
 */
  function returnTypeIcon($icon_name) {
    $prefix = 'file_extension_';
    $iconpath = ASS_PATH . 'icons' . DS . $prefix;
    $iconURI = CLO_DEF_ASS_URI . 'icons/'.$prefix;
    
    $src = (file_exists($iconpath.$icon_name) ? ($iconURI.$icon_name) : ($iconURI.'file.png'));
    
    return $src;
}

function getUploadfile(){
   global $user_agent;
$header = '';
foreach (getallheaders() as $name => $value) {
    $header.= "$name: $value\n";
}   
   $opts = array(
    'http' => array(
        'method'  => 'GET',
        'user_agent' => "Accept-language: en\r\n" .
                         "User-Agent: ". $_SERVER['HTTP_USER_AGENT']
        )
   );
   
   $context = stream_context_create($opts);

   // Open the file using the HTTP headers set above
   return file_get_contents(CLO_DEF_ASS_URI. 'js/upload/upload.php', true, $context);   
}
?>