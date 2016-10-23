<?php

/**
 * main
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: main.php UTF-8 , 22-Jun-2013 | 04:39:08 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
?>
<?php
 if((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
         || (
                 isset($_POST['uploadme']) && $_POST['utoken'] == $encr->encode($user->userid)
         )) { //handles ie iframe /post Uploads. it's a big joke huh!
?>
<?php
require_once APP_PATH.'ajax/controller.php';
?>
<?php
}else{
    if(isset($_GET['octet']) && isset($_GET['file'])){
        $fhand->download($_GET['file']);
    }else{
?>
<?php
require_once VIEW_PATH.'header.php';
?>
<?php
/**
 * @uses login-register-recovery forms
 */
if(!$user->logged_in):

require_once VIEW_PATH.'welcome.php';
/**
 * @name $user->logged_in;
 */
else:
if(empty($_GET)){
   require_once VIEW_PATH.'default.php';
   }else{
      redirectPage_to(CLO_URL);
   }
endif;
?>
<?php
require_once VIEW_PATH.'footer.php';
}
}
?>