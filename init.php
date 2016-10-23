<?php

/**
 * init
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: init.php UTF-8 , 21-Jun-2013 | 23:07:15 nwdo ε
 */
if (!defined("_SECURE_PHP"))
   die('Direct access to this location is not allowed.');

date_default_timezone_set ("UTC");

//Save POST_GET_COOKIE from magic!!! - thanks to Alice
if (ini_get('magic_quotes_gpc')) {

   function clean($data) {
      if (is_array($data)) {
         foreach ($data as $key => $value) {
            $data[clean($key)] = clean($value);
         }
      } else {
         $data = stripslashes($data);
      }
      return $data;
   }

   $_GET = clean($_GET);
   $_POST = clean($_POST);
   $_COOKIE = clean($_COOKIE);
}

//find the real path of the init.php file.
$NEDOX = str_replace("init.php", "", realpath(__FILE__));
//Base path of CloShare.
define("NEDOXROOT", $NEDOX);
//Directory_separator
define("DS", DIRECTORY_SEPARATOR);
//Application directory path / default is "application"
define("APP_PATH", NEDOXROOT . "application" . DS);
//Application cache path / default is "application/cache"
define("CACHE_PATH", NEDOXROOT . "application" . DS. "cache");
//Application library path / default is "lib"
define("LIB_PATH", APP_PATH . "lib" . DS);
//Application include path / default is "inc"
define("VIEW_PATH", APP_PATH . "view" . DS);
//Application controller path / default is "controller"
define("CON_PATH", APP_PATH . "controller" . DS);
//Application ASSET path / default is "assets"
define("ASS_PATH", APP_PATH . "assets" . DS);
//Application THEME path / default is "views/themes/"
define("THEME_ROOT_PATH", APP_PATH . "views" . DS . "themes" . DS);
//Upload path / default is "files/"
define("UPLOAD_PATH", NEDOXROOT . "files" . DS);
//Upload thumbnails path / default is "files/thumbnails/"
define("THUMB_PATH", NEDOXROOT . "files" . DS . "thumbnails" . DS);

$configFile = LIB_PATH . "config.ini.php";
if (file_exists($configFile)) {
   require_once($configFile);
}else {
   header("Location: /?setup");
}

if(file_exists(NEDOXROOT."maintenance.php") && !defined("_MAINTENANCE") && !$DEBUG){
    header("Location: /maintenance.php");
}

define("CLO_DEBUG", $DEBUG);
//To send errors-warnings to browser - set on/off in "/application/lib/config.ini.php" dev: on, prod: off
if (CLO_DEBUG) {
//set php.ini ready for detailed error_reporting
error_reporting(E_ALL);    
ini_set('display_errors', 'On');
}
#################################################################
//try to connect DB  - set in "/application/lib/config.ini.php"
include_once(LIB_PATH . "class.mysql.php");
$db = new Nedo\Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
$db->connect();
#################################################################
//translator file
require_once(LIB_PATH . "lang/translations_".$lang.".php"); //required for future translations. in v2.0.0  current : En
//prepare headers for redirecting pages in need.
include(LIB_PATH . "redirect.php");
//User Class
require_once(LIB_PATH . "class.paginate.php"); // pagination.
//Image Class
require_once(LIB_PATH ."image".DS."autoload.php"); //crop /resize /create versions /cache images etc...
//Encryption Class
require_once(LIB_PATH . "class.encryption.php"); //security and other stuff
$encr = new Nedo\Encryption();
//Include Functions
require_once(LIB_PATH . "functions.php"); // general global functions
//Core Class
require_once(LIB_PATH . "class.core.php"); // core functions settings-system etc...
$core = new Nedo\Core;
//User Class
require_once(LIB_PATH . "class.user.php"); // user management functions.
$user = new Nedo\Users();
//Mobile Detector Class
include(LIB_PATH . "class.mobile.php"); // to detect mobility.
$detect = new Nedo\Mobile_Detect;
$phone = $detect->isMobile() && !$detect->isTablet();
$tablet = $detect->isTablet();
//Content Class
require_once(LIB_PATH . "class.share.php"); // sharing functions.
$share = new Nedo\Share();
//Content Class
include_once(LIB_PATH . "class.content.php"); // printing html contents.
$content = new Nedo\Content();
//FileHandler Class
include_once(LIB_PATH . "file.handler".DS."class.filehandler.php"); // handling files/folders all stuff.
$fhand = new Nedo\FileHandler();
//Upload Class
if(isset($_POST["uploadme"]) || isset($_REQUEST['cancelme'])): //require only while upload request is detected... else nope.
require_once(LIB_PATH . "file.handler".DS."class.upload.php");
$uploader = new Nedo\UploadHandler(null, true);
endif;
/**
 * Define other constants after core.class,user.class etc.. loaded.
 * Do not edit values below!
 */
//CloShare URI / default is "http://example.com"
define("CLO_URL", $core->site_url); //without trailing slash /
//CloShare Content Delivery Network (CDN) / default is "http://cdn.example.com"
define("CLO_CDN", $core->site_cdn); //without trailing slash /
define("CLO_CDN_ON", $core->use_site_cdn);
//CloShare SITE NAME / default is "Clo&Share"
define("CLO_SITE_NAME", $core->site_name);
//CloShare SITE_SLOGAN / default is "Optional"
define("CLO_SITE_SLOGAN", $core->site_slogan);
//CloShare DEFAULT ASSETS URI-path preparation & for security reasons and pretty URI it uses .htaccess to handle requests
$CLO_ASSETS_ROOT_URI = (CLO_CDN_ON ? CLO_CDN : CLO_URL);
//CloShare DEFAULT ASSET URI / default is "/application/assets/js/" it uses .htaccess
define("CLO_DEF_ASS_URI", $CLO_ASSETS_ROOT_URI . '/assets/');
//CloShare DEFAULT ASSET URI / default is "/application/assets/js/" it uses .htaccess
define("CLO_DEF_ASS_MIN_URI", $CLO_ASSETS_ROOT_URI . '/assets/build/');
//CloShare DEFAULT CSS URI / default is "/application/assets/js/" it uses .htaccess
define("CLO_DEF_CSS_URI", $CLO_ASSETS_ROOT_URI . '/assets/css/');
//CloShare DEFAULT JS URI / default is "/application/assets/js/" it uses .htaccess
define("CLO_DEF_JS_URI", $CLO_ASSETS_ROOT_URI . '/assets/js/');

//$user_agent = $_SERVER['HTTP_USER_AGENT'];
//Include Runtime Functions
require_once(LIB_PATH . "runtime.php");
?>