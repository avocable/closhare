<?php

/**
 * index
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: index.php UTF-8 , 21-Jun-2013 | 22:54:11 nwdo ε
 */
define("_SECURE_PHP", true);
?>
<?php

if(isset($_GET['setup'])){
   
   include_once 'application/setup/run.php';
   
}elseif(isset($_GET['login'])){
   include_once 'init.php';
   $user->login($_SESSION['d_first_em'], $_SESSION['d_first_pa']);
   unset($_SESSION['d_first_em']);
   unset($_SESSION['d_first_pa']);
   redirectPage_to("/#page=settings");
}else{
   include_once 'init.php';
   require_once APP_PATH.'view'.DS.'main.php';
}