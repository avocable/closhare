<?php

/**
 * core
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: class.core.php UTF-8 , 22-Jun-2013 | 01:45:22 nwdo ε
 */
namespace Nedo;
use Nedo\Image\GarbageCollect;
use Nedo\Upgrade;

use Nedo\Content;

if (!defined("_SECURE_PHP"))
   die('Direct access to this location is not allowed.');

class Core {

   public $msgs = array();
   public $showMsg;
   public $jsonE = array();
   public $jsonEA = array(); //errors to show for only admin. "feature".
   public $jsonMsg = array();
   public $sTable = "settings";
   public $cTable = "content";
   public $uTable = "users";
   public $gTable = "user_groups";
   public $fTable = "uploads";
   public $dTable = "folders";
   public $pTable = "shared";
   public $sID = null;
   public $ajaxReq = null;
   public $settings = null;
   private $implod = array("share_options");

   /**
    * __construct()
    * 
    * @return
    */
   function __construct() {
      $this->sTable = DB_PFX . $this->sTable;
      $this->cTable = DB_PFX . $this->cTable;
      $this->uTable = DB_PFX . $this->uTable;
      $this->gTable = DB_PFX . $this->gTable;
      $this->fTable = DB_PFX . $this->fTable;
      $this->dTable = DB_PFX . $this->dTable;
      $this->pTable = DB_PFX . $this->pTable;
      //get default settings from DB
      $this->settings = $this->getSettings(false);
   }

   /**
    * getSettings()
    *
    * @return

    */
   public function getSettings($filter = false) {
      global $db;
      $sql = "SELECT * FROM " . $this->sTable;
      $rows = $db->first($sql);

      /**
       * loop and retrieve all settings into $core object by using their default keys
       */
      foreach ($rows as $setting => $value) {
         if ($filter) {
            if (strpos($setting, $filter) !== false) {
               $this->$setting = $value;
               $this->settings[$setting] = $value;
            }
         } else {
            $this->$setting = $value;
            $this->settings[$setting] = $value;
         }
      }
      return $this->settings;
   }

   /**
    * 
    * @global \Nedo\type $db
    * @global type $fhand
    * @param type $prefix
    * @return type
    */
   public function setSettings($prefix) {
      global $db, $fhand;
      $data = array();
      $insettings = $this->getSettings();
      foreach ($_POST as $key => $value) {
         $keyo = $key;
         if (array_key_exists($keyo, $insettings) === true) {
            $use = is_array($value) ? ( (count($value) > 1) ? $value[0] . $value[1] : $value[0]) : $value;
            if (in_array($keyo, $this->implod)) {
               $use = implode(',', $value);
            }
            if (strpos($keyo, 'types') !== false) {
               $use = $fhand->getAllowedTypesRegex($use, false);
            }
            if (strpos($keyo, 'limit') !== false) {
               $use = $fhand->formatToBytes($use);
            }
            if (strpos($keyo, 'template') !== false) {
               $data[$keyo] = htmlspecialchars($use);
            } else {
               $data[$keyo] = sanitise($use);
            }
         }
      }
      $db->update($this->sTable, $data, "id=" . (int) 1);

      if ($db->affected()) {
         if (isset($_POST['changeSkin']) && $_POST['changeSkin']){
             $this->clearAssetCache(); //for possible skin changes.
         }
         
         $this->jsonE["result"] = 1;
         $this->jsonE["message"] = '<strong>Configurations saved successfully.</strong>';
      }else {
         $this->jsonE["result"] = 0;
         $this->jsonE["message"] = "Sorry! I couldn't find any changes to update.";
      }

      return $this->jsonE;
   }
   
   
   /**
    * 
    * @global \Nedo\type $db
    * @global type $user
    * @param type $type
    * @param type $to
    * @param type $username
    * @param type $pass
    * @param type $hash
    * @return type
    */
   public function sendSingleMail($type, $to = false, $username, $pass, $hash = false) {
      global $db, $user;
      require_once(LIB_PATH . "mail/class.mail.php");


      if ($type == "welcome") {
         //prepare welcome mail.
         $template = htmlspecialchars_decode($this->register_welcome_mail_template);

         $body = str_replace(
                 array('[NAME]', '[USERPASS]', '[USERMAIL]', '[SITEURL]', '[SITENAME]'), array($username, $pass, $to, $this->site_url, $this->site_name), $template);
         $subject = 'Hi , ' . $username . '. Welcome to ' . $this->site_name;
      } else if ($type == 'reset') {

         $template = htmlspecialchars_decode($this->recover_mail_template);

         $body = str_replace(
                 array('[NAME]', '[HASHV]', '[USERMAIL]', '[SITEURL]', '[SITENAME]'), array($username, $hash, $to, $this->site_url, $this->site_name), $template);
         $subject = 'Hi, ' . $username . ' you have requested a password reset on ' . $this->site_name;
      } else if ($type == 'resetResult') {

         $template = htmlspecialchars_decode($this->recover_mail_template_res);

         $body = str_replace(
                 array('[NAME]', '[USERPASS]', '[USERMAIL]', '[SITEURL]', '[SITENAME]'), array($username, $pass, $to, $this->site_url, $this->site_name), $template);
         $subject = 'Hi again , ' . $username . ' your new login details ' . $this->site_name;
      }


      $mailer = $mail->_prepare();
      $message = Swift_Message::newInstance()
              ->setSubject($subject)
              ->setTo(array($to => $username))
              ->setFrom(array($this->site_email => $this->site_name))
              ->setBody($body, 'text/html');

      $mailer->send($message);

      return ($mailer) ? true : false;
   }

   /**
    * formatDate()
    * 
    * @param mixed $date
    * @return
    */
   function formatDate($date) {
      return dodate($format, $date);
   }

   /**
    * push_before()
    * 
    * @param type $key
    * @param array $array
    * @param type $new_key
    * @param type $new_value
    * @return array|boolean
    */
   function push_before($key, $array, $new_key, $new_value) {
      if (array_key_exists($key, $array)) {
         $new = array();
         foreach ($array as $k => $value) {
            if ($k === $key) {
               $new[$new_key] = $new_value;
            }
            $new[$k] = $value;
         }
         return $new;
      }
      return false;
   }

   /**
    * push_after()
    * 
    * @param type $key
    * @param array $array
    * @param type $new_key
    * @param type $new_value
    * @return boolean
    */
   function push_after($key, $array, $new_key, $new_value) {
      if (array_key_exists($key, $array)) {
         $new = array();
         foreach ($array as $k => $value) {
            $new[$k] = $value;
            if ($k == $key) {
               $new[$new_key] = $new_value;
            }
         }
         return $new;
      }
      return false;
   }

   /**
    * getUniqueCode()
    * 
    * @param string $length
    * @return
    */
   public function getUniqueCode($length = "") {
      $code = sha1(uniqid(rand(), true));
      if ($length != "") {
         return substr($code, 0, $length);
      }
      else
         return $code;
   }

   /**
    * generateRandID()
    * 
    * @return
    */
   public function generateRandID() {
      global $encr;
      return $encr->encode($this->getUniqueCode(24));
   }

   /**
    * getRowById()
    * 
    * @param mixed $table
    * @param mixed $id
    * @param bool $and
    * @param bool $is_admin
    * @return
    */
   public function getRowById($table, $id, $and = false, $is_admin = true) {
      global $db;
      $id = sanitise($id, 8, true);
      $sql = "SELECT * FROM " . (string) $table . " WHERE id = '" . $db->escape((int) $id) . "'";
      $row = $db->first($sql);
      if ($row) {
         return $row;
      } else {
         if ($is_admin)
            $this->error("You have selected an Invalid Id - #" . $id, "Core::getRowById()");
      }
   }

   /**
    * returnJson()
    * 
    * @return
    */
   public function returnJson() {
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Content-type: application/json');
      return json_encode($this->jsonE);
   }

   /**
    * ucfirstStr()
    * 
    * @return
    */
   public function ucfirstStr($string) {
      if (strpos($string, ",") != false) {
         $temp = explode(",", $string);
         $str = '';
         foreach ($temp as $val) {
            $str.= ',' . mb_convert_case(str_replace(array("i", "I"), array("İ", "ı"), $val), MB_CASE_TITLE, "UTF-8");
         }
         $str = ltrim($str, ",");
      } else {
         $str = mb_convert_case(str_replace(array("i", "I"), array("İ", "ı"), $string), MB_CASE_TITLE, "UTF-8");
      }

      return $str;
   }

   /**
    * getNumbersOnly()
    * 
    * @return
    */
   public function getNumbersOnly($string) {
      // This regex pattern means anything that is not a number
      $pattern = '/[^0-9]/';
      // preg_replace searches for the pattern in the string and replaces all instances with an empty string
      return sanitise(preg_replace($pattern, '', $string));
   }

   /**
    * getDateOnly()
    * 
    * @return
    */
   public function getDateOnly($date) {
      $datetime = strtotime($date);
      return date("d.m.Y", $datetime);
   }

   /**
    * 
    * @global type $db
    * @return type
    */
   public function defaultRecoverMailTemplate() {
      global $db;
      $data = array("recover_mail_template" => 'Hello, <b>[NAME]</b><br><br>You have just requested a password reset from [SITENAME].<br>If you think this is on you. Please <a href="[SITEURL]/?reset=[HASHV]">click here</a> to go on.<br>If not please ignore this mail and do nothing.<br><p align="right">[SITENAME].<br></p>');

      $db->update($this->sTable, $data, "id=" . (int) 1);
      if ($db->affected()) {
         $this->jsonE["result"] = 1;
         $this->jsonE["message"] = '<strong>Recovery mail template was returned to its defaults successfully.</strong>';
      } else {
         $this->jsonE["result"] = 0;
         $this->jsonE["message"] = "Sorry! I couldn't find any changes to update.";
      }
      return $this->jsonE;
   }

   /**
    * 
    * @global type $db
    * @return type
    */
   public function defaulWelcometMailTemplate() {
      global $db;
      $data = array("register_welcome_mail_template" => 'Hello, <b>[NAME]</b><p></p> <br>  We want to say that we are pleased to see you among us!<br> <br>  Now you can easily store/share your files with simple clicks on <a href="[SITEURL]">[SITENAME]</a>.<br>  You can login to your [SITENAME] account using details below.<br> <br>  E-mail: [USERMAIL]<br>  Password: [USERPASS]<p></p><br> <a href="[SITEURL]" target="_blank">Click here</a> to go to your account.<p></p><p align="right">[SITENAME].<br></p>');

      $db->update($this->sTable, $data, "id=" . (int) 1);
      if ($db->affected()) {
         $this->jsonE["result"] = 1;
         $this->jsonE["message"] = '<strong>Welcome mail template was returned to its defaults successfully.</strong>';
      } else {
         $this->jsonE["result"] = 0;
         $this->jsonE["message"] = "Sorry! I couldn't find any changes to update.";
      }
      return $this->jsonE;
   }

   public function clearAssetCache() {
      $files = glob(APP_PATH . 'tmp/*'); // get all file names
      foreach ($files as $file) { // iterate files
         if (is_file($file) && $file != "index.php")
             unlink($file); // delete file
      }
   }

   /**
    * 
    * @return type
    */
   public function isShareUrI() {
      $URI = $_SERVER['REQUEST_URI'];
      $parts = explode('/', $URI);
      $parts = explode('?', $parts[1]);
      return ($parts[0] == 'share') ? true : false;
   }

   /**
    * 
    * @return array
    */
   public function getServerInfo() {

      $Arr = array(
          "file_uploads" => array("required" => 1, "name" => "File Uploads", "value" => ini_get("file_uploads") ? 'on' : 'off'),
          //"allow_url_fopen" => array(),
          "upload_tmp_dir" => array("required" => ">0755", "name" => "Upload Temporary Directory"),
          "upload_max_filesize" => array("required" => $this->upload_max_file_size_limit, "name" => "Upload Maximum File Size"),
          "max_execution_time" => array("required" => 300, "name" => "Maximum Execution Time"),
          "max_input_time" => array("required" => 300, "name" => "Maximum Input Time"),
          "post_max_size" => array("required" => 300, "name" => "Post Max Time"),
          "memory_limit" => array("required" => 300, "name" => "Memory Limit")
      );
      return $Arr;
   }

   /**
    * 
    * @return type
    */
   public function resolutions() {
      $resolution = array(
          "176x144",
          "352x288",
          "320x200",
          "320x240",
          "640x480",
          "720x480",
          "720x486",
          "720x540",
          "720x576",
          "768x576",
          "854x480",
          "864x486",
          "960x720",
          "1024x576",
          "1024x768",
          "1152x768",
          "1280x720",
          "1280x800",
          "1280x854",
          "1280x960",
          "1280x1024",
          "1366x768",
          "1280x1080",
          "1400x1050",
          "1440x900",
          "1440x960",
          "1440x1080",
          "1440x1080",
          "1440x1024",
          "1600x1200",
          "1680x1050",
          "1920x1200",
          "1920x1080",
          "2048x1536",
          "2560x1600",
          "2560x2048",
          "2880x2048",
          //mobile
          "128x128" => "160x160",
          "160x128" => "160x160",
          "160x160" => "160x160",
          "208x176",
          "208x208",
          "220x176",
          "240x180",
          "256x256",
          "280x220",
          "288x160",
          "320x240",
          "320x256",
          "320x320" => "320x320",
          "345x240",
          "384x234",
          "400x240",
          "416x352",
          "480x234",
          "480x272",
          "480x320",
          "640x480",
          "800x480",
          "800x600",
          "960x540"
      );
   }
   
   /**
    * 
    * @return type
    */
   public function client_resolution($nocookie = false) {
      $resolution = array();
      $resolution_cook = (($nocookie) ? $nocookie : ( (isset($_SESSION['CLO_RES']) ? $_SESSION['CLO_RES'] : (isset($_COOKIE['CLO_RES']) ? $_COOKIE['CLO_RES'] : false))) );
      $res = explode("x", $resolution_cook);
      if ($resolution_cook) {
         $resolution[0] = sanitise($this->getNumbersOnly($res[0]));
         $resolution[1] = sanitise($this->getNumbersOnly($res[1]));
         $resolution[2] = $resolution_cook ? sanitise($resolution[0] . 'x' . $resolution[1]) : '';
      }
      return $resolution;
   }   
   
   public function current_page_url($p=true) {
      $pageURL = $p ? 'http' : '';
      if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
         $pageURL .= "s";
      }
      $pageURL .= $p ? "://" : "";
      if ($_SERVER["SERVER_PORT"] != "80") {
         $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
      } else {
         $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
      }
      return $pageURL;
   }
   
   public function getSkinArray(){
      return array("default", "metro", "mini", "flat", "ubuntu", "amelia", "dark", "silver", "lightblue");
      
   }
   
   
   public function versionCheck() {
       
       require_once(APP_PATH.'include'.DS.'/class.upgrade.php');
       
       $file = $tar = APP_PATH.'include'.DS.'vfch.clo';
       if(file_exists($file)){
           $filelastmodified = filemtime($file);
       }else{
           $filelastmodified = 536457600;
       }
       //$filelastmodified = 536457600;
       if((time() - $filelastmodified) > 24*3600){
                     
           $vch = new \Nedo\Upgrade();           
           
           $response = $vch->check("version");
                      
           @file_put_contents($tar, "success");
           @chmod($tar, 0664);
           
           if($response){
               
               if($response['result'] == 1){
               $this->msgs['title'] = $response['title'];
               $this->msgs['message'] = $response['message'];
               echo \Nedo\Content::notifyMessage("info", true, true, true);
               }
               
           }else{
               $this->msgs['message'] = "error while trying to check version.";
               echo \Nedo\Content::notifyMessage("error", true, true, true);
           }
               
       }
       
   }
   
   public function fdirect() {
        $host = $_SERVER['HTTP_HOST'];
        $newURI = false;

        if ((strpos($host, "www.") === false) && (strpos($this->site_url, "www.") !== false )) {
            $newURI = 'http://www.' . $this->current_page_url(false);
        } else if ((strpos($this->site_url, "www.") === false) && (strpos($host, "www.") !== false )) {

            $parse = parse_url($this->current_page_url(true));
            $newURI = "http://" . preg_replace('#^www\.(.+\.)#i', '$1', $parse['host']) . $parse['path'];
        }
        if ($newURI) {
            redirectPage_to($newURI);
        }
    }

}
?>