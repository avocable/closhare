<?php

/**
 * controller
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: controller.php UTF-8 , 21-Jun-2013 | 23:02:02 nwdo ε
 */
if (!defined("_SECURE_PHP"))
  die('Direct access to this location is not allowed.');

 if( isset($_FILES['avatar']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {

  /**
  * @return profile screen Content
  */
 if (isset($_POST["cli_resolution"])):
    $_SESSION['CLO_RES'] = $_POST['cli_resolution'];
 endif;    
 /**
  * get ajax validations
  */
 if (isset($_GET["check"])):
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        $val = $_GET["value"];
        $field = $_GET["field"];
        switch ($field):
            case "register-email":
                $core->jsonE["message"] = "";
                $core->jsonE["value"] = $val;
                //$core->jsonE["valid"] = (isValidEmail($val) ? 1 : 0);
                //if ($core->jsonE["valid"]):
                $core->jsonE["valid"] = ($user->emailExists($val) ? 1 : 0);
                //endif;
                break;
            case "reset-email":
                $core->jsonE["message"] = "";
                $core->jsonE["value"] = $val;
                //$core->jsonE["valid"] = isValidEmail($val);
                //if ($core->jsonE["valid"]):
                $existance = $user->emailExists($val);
                if ($existance == true):
                    $core->jsonE["valid"] = false;
                    $core->jsonE["message"] = 'I couldn\'t find email that you have entered.';
                else:
                    $core->jsonE["valid"] = true;
                endif;
                //endif;
                break;
        endswitch;
        echo $core->returnJson(true);
    endif;

 /**
  * @return upload screen Content
  */

 if (isset($_GET["upload"])):
        checkauth();
        $core->jsonE = $content->UploadScreenHTML();
        echo $core->returnJson();
    endif;

 /**
  * @return list screen Content
  */
 if (isset($_GET["list"])):
        checkauth();
        $spf = explode(',',$_GET['list']);
        $current_folder = isset($spf[1]) ? $spf[1] : 1;
        $parent_id = isset($spf[2]) ? $spf[2] : 1;
        
        $page = isset($_GET['pg']) ? $_GET['pg'] : 1;
        
        $core->jsonE = $content->ListScreenHTML($parent_id , $current_folder, $page);
        echo $core->returnJson();
    endif;
 
 /**
  * @return user list screen Content
  */
 if (isset($_GET["users"])):
        checkauth('admin');
        $page = isset($_GET['pg']) ? $_GET['pg'] : 1;
        $itemsperpage = isset($_GET['ipp']) ? $_GET['ipp'] : $core->items_per_page;
        
        $core->jsonE = $content->userListHTML($page, $itemsperpage, 'users');
        echo $core->returnJson();
    endif;
    
 /**
  * @return settings screen Content
  */
 if (isset($_GET["settings"])):
        checkauth('admin');
        $edit = !empty($_GET['edit']) ? $_GET['edit'] : 'upsettings';
        switch ($edit) {
            case 'uisettings':
                $core->jsonE = $content->getSettings("uisettings");
                break; 
            case 'sysettings':
                $core->jsonE = $content->getSettings("sysettings");
                break;
            case 'shsettings':
                $core->jsonE = $content->getSettings("shsettings");
                break;
            case 'serverinfo':
                $core->jsonE = $content->getSettings("serverinfo");
                break;     
            default:
                $core->jsonE = $content->getSettings("upsettings");
                break;              
            } 
        echo $core->returnJson();
    endif;   
    
 /**
  * @return profile screen Content
  */
 if (isset($_GET["profile"])):
        checkauth();
        $core->jsonE = $content->getProfile();
        echo $core->returnJson();
    endif;
###########CONTENT###########################################################################################
    
if (isset($_GET["content"])):
   $type = explode("_", $_GET["content"]);
   switch ($type[1]) {
         case 'terms-of-use':
            $core->jsonE = $content->getTermsOfUSE();
            break;
      }
   echo $core->returnJson();
endif;

###########SEARCH###########################################################################################
    
if (isset($_GET["search"])):
   checkauth();
   $type = $_GET["search"];
   switch ($type) {
         case 'key':
            $core->jsonE = $fhand->search($_GET['q']);
            break;         
      }
   echo $core->returnJson();
endif;

###########SHARE#############################################################################################
    
if (isset($_POST["shareit"])):
   $element = $_POST["shareit"];
   $core->jsonE = $share->getShareElementParams($_POST['shareit'], false, $_POST['uihash']);
   echo $core->returnJson();
endif;
    
###########ACTIONS###########################################################################################

if (isset($_POST["action"])):
      checkauth();
      $type = explode("_", $_POST["action"]);
      $dir = $files = $elements = (post('value') ? $_POST['value'] : 0); //id of requested folder or file
      switch ($type[0]) {
         case "fo": //its a folder

            switch ($type[1]) {

               case 'create': //folder create request has been detected!
                  $foldername = (post('name') ? $_POST['name'] : '');
                  $description = (post('desc') ? $_POST['desc'] : '');
                  $icon = (post('icon') ? $_POST['icon'] : '');
                  $fhand->createFolder($foldername, $description, $icon, $dir);
                  break;

               case 'edit': //folder edit request has been detected!
                  $newfoldername = (post('name') ? $_POST['name'] : '');
                  $newdescription = (post('desc') ? $_POST['desc'] : '');
                  $newicon = (post('icon') ? $_POST['icon'] : '');
                  $fhand->editFolder($newfoldername, $newdescription, $newicon, $dir);
                  break;

               case 'move': //folder delete request has been detected!
                  $target = (post('folist') ? $_POST['folist'] : 1);
                  $olddir = (post('odir') ? $_POST['odir'] : 1);
                  $fhand->moveFolder($dir, $target, false, $olddir);
                  break;

               case 'delete': //folder delete request has been detected!
                  $fhand->deleteFolder($dir);
                  break;

               case 'dropdown': //folder dropdown request has been detected!
                  $core->jsonE = $content->returnAllFoldersDropdownArray($fhand->getUserFolders(false), 1, $dir);
                  //print_r($fhand->getUserFolders(false));
                  break;

               case 'share': //folder share request has been detected!
                  $core->jsonE = $content->getShareBox($elements, $type[0]);
                  break;

               case 'downloadzip': //folder zip request has been detected!
                  $core->jsonE = $fhand->ArchiveFolderZip($dir);
                  break;
            }

            break;

         case 'uf':
            switch ($type[1]) {
               case 'delete':
                  $user->deleteUser($elements);
                  break;
               case 'status':
                  $user->toggleStatus($elements, $_POST['toggle']);
                  break;
            }
            break;

         case 'settings':
            $core->jsonE = $core->setSettings($type[1]);
            break;
         case 'mail':
            switch ($type[1]) {
               case "recovery":
                  $core->jsonE = $core->defaultRecoverMailTemplate();
                  break;

               case "welcome":
                  $core->jsonE = $core->defaulWelcometMailTemplate();
                  break;
            }

            break;
         case 'profile':
            $core->jsonE = $user->updateProfile($type[1]);
            break;

         default: // file(s)

            switch ($type[1]) {

               case 'move': //file move request has been detected!
                  $current_folder = (post('odir') ? $_POST['odir'] : 1);
                  $target = (post('folist') ? $_POST['folist'] : 1);
                  $fhand->moveFiles($files, $target, $current_folder);
                  break;

               case 'delete': //file delete request has been detected!
                  $current_folder = (post('odir') ? $_POST['odir'] : 1);
                  $fhand->deleteFileorFiles($files,false, $current_folder);
                  break;

               case 'share': //file share request has been detected!
                  $core->jsonE = $content->getShareBox($elements, $type[0]);
                  break;
            }

            break;
      }
      echo $core->returnJson();
   endif;
 /**
  * @return meter
  */
 if (isset($_GET["meter"])):
        checkauth();
        $core->jsonE = $content->getUserLimitBR();
        echo $core->returnJson();
    endif;
    
 /**
 * recieve only ajax requests.
 */
 }
?>