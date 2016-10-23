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

if(get("item")){
    $page = $content->createSharedPage($_GET['item']);
    
}else{
    //shared item is not set so show an "Opps! Page"
}
$meta = $page['meta'];
//$content->inlineASS['js'][] = ''; in close future!

include_once '../header.php';

print $page['content'];

include_once '../footer.php';
?>
