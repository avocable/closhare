<?php

/**
 * class.filehandler
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: class.filehandler.php UTF-8 , 06-Jul-2013 | 18:53:08 nwdo ε
 */
namespace Nedo;
if (!defined("_SECURE_PHP"))
   die('Direct access to this location is not allowed.');

use Nedo\Image\Image;
use Nedo\Mobile_Detect;
use ZipArchive;

class FileHandler{
     

   var $f_err;  // Error for the upload methods
   var $lastname = null;
   var $dpfi = 'file_'; //prefix for columns of "uploads"
   var $dpfo = 'folder_'; //prefix for columns of "uploads"
   public $static_folder_mime_Arr = array();
   var $foldersArr, $subdirs, $parents, $boundedFoldersUp = array();
   var $folder_name, $folderPath, $folderLevel = '';
   var $filesum = 0;
   var $viewableTypesArr = array("jpg", "jpeg", "gif", "png", "mp4", "ogv", "ogg", "mov", "mp3", "wav", "wmv", "wma", "webm", "acc"); //etc...
   var $prohibitedTypesArr = array("php", "php3", "asp", "aspx", "py", "jsf");
   public $allViewableMimeArr = array(
       "image" => "plus",
       "audio" => "play-circle",
       "video" => "play-sign"
   );
   public $countUserFolders;
   var $archiveFoldersArr = array();
   var $archive;

   function __construct() {
      $this->arrangeIdStaticFoldersType(true);
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @global Paginator $pager
    * @param type $current_folder
    * @return type
    */
   function getUserFiles($current_folder = 1) {
      global $db, $core, $user, $pager;
      $fdata = array();
      //preserve items per page for scrolling on big screens.
      $ipp = $core->items_per_page;
      $ipp = ($ipp > 10 ? ($ipp > 30 ? $ipp : $ipp * 1.5) : $ipp * 3);
      $page = isset($_GET['pg']) ? $_GET['pg'] : 1;

      $pager = new \Nedo\Paginator($page, $ipp);

      $counter = countDataDB($core->fTable, false, false, 'file_user_id = ' . $user->userid . ' AND file_folder = ' . $current_folder . ' ');
      $pager->items_total = $counter;
      $pager->default_ipp = $ipp;
      $pager->paginate();

      if ($counter == 0) {
         $pager->limit = null;
      }

      $sql = "SELECT f.*, o.* FROM " . $core->fTable . " as f"
              . "\n LEFT JOIN " . $core->dTable . " as o ON o.{$this->dpfo}id = f.file_folder"
              . "\n WHERE "
              . "\n ({$this->dpfi}folder = " . $current_folder
              . "\n OR {$this->dpfi}mime_folder = " . $current_folder . ")"
              . "\n AND {$this->dpfi}user_id = " . $user->userid
              . "\n ORDER BY {$this->dpfi}date DESC" . ($page ? $pager->limit : "");

      if (($page - 1 != $pager->num_pages && $counter >= $ipp) || $counter < $ipp) {
         $fdata['files'] = $db->fetch_all($sql);
         $fdata['fetch'] = true;
      }
      if ($page >= $pager->num_pages) {
         $fdata['fetch'] = false;
      }

      //check for the physical file existance
      foreach ($fdata['files'] as $key => $file) {
         if (!file_exists($this->getSingleFilePath($file))) {
            $this->deleteFileorFiles('f' . $file['file_id'], false);
            unset($fdata['files'][$key]);
         }
      }

      return (!empty($fdata)) ? $fdata : 0;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $dir
    * @return type
    */
   function getFolderFiles($dir, $user_id = false) {
      global $db, $core, $user;
      $sql = "SELECT * FROM " . $core->fTable
              . "\n WHERE "
              . "\n {$this->dpfi}folder = " . $dir
              . "\n AND {$this->dpfi}user_id = " . ($user_id ? $user_id : $user->userid)
              . "\n ORDER BY {$this->dpfi}id DESC";

      $row = $db->fetch_all($sql);

      return ($row) ? $row : 0;
   }

   /**
    * 
    * @param type $staticFolders
    * @param type $userFolders
    * @return type
    */
   function getAllFolders($staticFolders, $userFolders) {
      if ($userFolders != null)
         foreach ($userFolders as $key => $val) {
            $staticFolders[$userFolders[$key]['folder_id']] = $val;
         }
      return $staticFolders;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $folder_id
    * @return type
    */
   function getUserFolders($folder_id = false, $user_id = false) {
      global $db, $core, $user;
      $query = $db->query("SELECT "
              . "\n *"
              . "\n FROM " . $core->dTable . ""
              . "\n WHERE {$this->dpfo}user_id = " . ($user_id ? $user_id : $user->userid)
              . "\n ORDER BY {$this->dpfo}parent_id ASC"
      );
      $this->countUserFolders = $db->numrows($query);
      $children = array();
      while ($result = $db->fetch($query)) {
         foreach ($result as $key => $val) {
            $children[$result['folder_id']][$key] = $val;
         }
      }

      return ($children) ? ($folder_id ? $children : $this->folderHierarchy($children)) : null;
   }

   /**
    * 
    * @global type $core
    * @param type $data
    * @return type
    */
   function folderHierarchy($data) {
      global $core;
      $folders = $data;
      foreach ($data as $key => $row) {

         if (isset($folders[$row['folder_parent_id']])) {

            $folders = $core->push_after($row['folder_parent_id'], $folders, $key, $row);
         }
         unset($key);
      }
      return $folders;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @return type
    */
   function getstaticFolders() {
      global $db, $core;
      $query = $db->query("SELECT "
              . "\n *"
              . "\n FROM " . $core->dTable
              . "\n WHERE {$this->dpfo}static = 1"
      );
      $row = array();
      while ($result = $db->fetch($query)) {

         foreach ($result as $key => $val) {
            $row[$result['folder_id']][$key] = $val;
         }
      }
      return ($row) ? $row : null;
   }

   /**
    * 
    * @param type $data
    * @param type $folder_id
    * @param type $append_parent
    * @return type
    */
   function getSubFoldersOfFolder($data, $folder_id, $append_parent = false, $numerickey = false, $fresh = false) {

      if ($fresh) {
         $this->subdirs = array();
      }
      foreach ($data as $key => $row) {
         if ($append_parent && !isset($this->subdirs[($numerickey ? $numerickey : "current")])) {
            $this->subdirs[($numerickey ? $numerickey : "current")] = $data[$folder_id];
         }

         if ($row['folder_parent_id'] == $folder_id) {
            $this->subdirs[$key] = $row;
            $this->getSubFoldersOfFolder($data, $row['folder_id']);
         }
         unset($row);
      }


      return is_array($this->subdirs) ? $this->subdirs : false;
   }

   function countSubFoldersOfFolder($allfolders, $folder_id) {

      return count($this->getSubFoldersOfFolder($allfolders, $folder_id, false, false, true));
   }

   /**
    * 
    * @param type $data
    * @param type $folder_id
    * @return type
    */
   function getParentFoldersOfFolder($data, $parent_id) {

      foreach ($data as $key => $row) {

         if ($row['folder_id'] == $parent_id) {
            $this->parents[] = $row;
            $this->getParentFoldersOfFolder($data, $row['folder_parent_id']);
         }
      }
      unset($row);

      return is_array($this->parents) ? array_reverse($this->parents) : false;
   }

   /**
    * 
    * @global type $user
    * @param type $bytes
    * @param type $precision
    * @param type $unit
    * @return string|int
    */
   
   function formatBytes($bytes, $precision = 2, $unit = false) {
      global $user;
      
      if(!$bytes) return "N/A";
      
      $units = array('B', 'KB', 'MB', 'GB', 'TB');
      
      $bytes = max($bytes, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);

      $bytes /= pow(1024, $pow); //or
      //$bytes /= (1 << (10 * $pow));

      return round($bytes, $precision) . '' . ($unit ? $units[$pow] : "");
   }

   function formatToBytes($sizeString) {
      global $core;
      $size = $core->getNumbersOnly($sizeString);
      $unit = str_replace($size, "", $sizeString);

      switch ($unit) {
         case "GB":
            $multiplier = 1073741824;
            break;
         case "TB":
            $multiplier = 1099511627776;
            break;
         case "KB":
            $multiplier = 1024;
            break;
         case "B":
            $multiplier = 1;
            break;
         default: //MB
            $multiplier = 1048576;
            break;
      }
      return ($size * $multiplier);
   }

   function getUnitFromBytes($bytes) {
      $i = 0;
      $units = array('B', 'KB', 'MB', 'GB', 'TB');
      if ($bytes > 1024) {

         $i = floor(log($bytes, 1024));
      }

      return sanitise($units[$i]);
   }
   
   
   function getSystemDiskSize() {

      if (function_exists("disk_free_space") && !@ini_get("open_basedir")) {
         if (disk_free_space("/")) {
            return disk_free_space("/");
         } else {
            return false;
         }
      }
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $user_id
    * @param type $format
    * @return type
    */
   function userTotalSpace($user_id = false, $format = true, $ubytes=false) {
      global $db, $core, $user;
      //requested from other user
      if($user_id){
      $query = $db->query("SELECT "
              . "\n user_limit as bytes"
              . "\n FROM " . $core->fTable . ""
              . "\n WHERE {$this->dpfi}user_id = " . ($user_id ? $user_id : $user->userid));
      $row = $db->fetch($query);
      $bytes = $row['bytes'];
      }else{
         if($ubytes == 0){
            $bytes = $this->getSystemDiskSize();
         }else{
            $bytes = $ubytes ? $ubytes : $user->disk_limit;
         }
      }
      return sanitise(!$format ? $bytes : $this->formatBytes($bytes, 2, true));
   }
   
   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $user_id
    * @param type $format
    * @return type
    */
   function userSpaceInUse($user_id = false, $format = true) {
      global $db, $core, $user;
      $sum = 0;
      $query = $db->query("SELECT "
              . "\n COALESCE(SUM(file_size),0) as bytes"
              . "\n FROM " . $core->fTable . ""
              . "\n WHERE {$this->dpfi}user_id = " . ($user_id ? $user_id : $user->userid));
      $row = $db->fetch($query);
      $bytes = $row['bytes'];
      return sanitise(!$format ? $bytes : $this->formatBytes($bytes, 2, true));
   }

   /**
    * 
    * @param type $user_id
    * @param type $total
    * @param type $precalculated
    * @return int
    */
   function calcUserUsagePercentage($user_id, $total, $precalculated = false) {
      
      if ($total == "N/A" || $total == 0){
         $total = $this->userTotalSpace(false, false, $total);
      }
      if (!$precalculated) {
         $usage = $this->userSpaceInUse($user_id, false);
      } else {
         $usage = $precalculated;
      }
      //sprint_r($usage.'-total: '.$total.'-division: '.($usage / $total) * 100);
      
      $calc = ($usage / $total) * 100;
      return floor($calc);
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @return type
    */
   function countUserFolders() {

      global $db, $core, $user;
      $query = $db->query("SELECT "
              . "\n {$this->dpfo}id"
              . "\n FROM " . $core->dTable . ""
              . "\n WHERE {$this->dpfo}user_id = " . $user->userid);
      $total = $db->numrows($query);

      return $total ? $total : 0;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $folder_id
    * @return type
    */
   function countUserFiles($folder_id = false) {
      global $db, $core, $user;
      $query = $db->query("SELECT "
              . "\n {$this->dpfi}id"
              . "\n FROM " . $core->fTable . ""
              . "\n WHERE {$this->dpfi}user_id = " . $user->userid
              . ( ($folder_id) ? ("\n AND {$this->dpfi}folder = " . $folder_id) : ""));
      $total = $db->numrows($query);

      return $total ? $total : 0;
   }
   
   
   function search($query = false){
      global $core;
      $files = $this->searchFiles($query);
      $folders = $this->searchFolders($query);
      
      if(is_array($files) && is_array($folders))
         $result = array_merge($files, $folders);
      elseif(is_array($files) && !is_array($folders))
         $result = $files;
      elseif(is_array($folders) && !is_array($files))
         $result = $folders;
      else
         $result = 0;
      
      return $result;
   }

   function searchFiles($query = false) {
      global $db, $core, $user;
      $sql = "SELECT "
              . "\n f.{$this->dpfi}id,"
              . "\n f.{$this->dpfi}key,"
              . "\n f.{$this->dpfi}title,"
              . "\n f.{$this->dpfi}extension,"
              . "\n f.{$this->dpfi}name,"
              . "\n f.{$this->dpfi}size,"
              . "\n f.{$this->dpfi}title as title"
              . "\n FROM " . $core->fTable . " as f"
              . "\n WHERE "
              . "\n f.{$this->dpfi}user_id = " . $user->userid
              . "\n AND f.{$this->dpfi}title LIKE '%" . $db->escape($query) . "%'"
              . "\n OR f.{$this->dpfi}note LIKE '%" . $db->escape($query) . "%' LIMIT 12";
              
      $rows = $db->fetch_all($sql);
      
      unset($core->jsonE);
      
      foreach($rows as $key => $row){
         $core->jsonE[$key]['viewable'] = ($this->isViewable($row) ? true : false);
         $core->jsonE[$key]['id'] = $row['file_id'];
         $core->jsonE[$key]['name'] = $row['file_title'];
         $core->jsonE[$key]['icon'] = $this->getTypeIcon(false,$row['file_extension']);
         $core->jsonE[$key]['size'] = $row['file_size'];
         $core->jsonE[$key]['url'] = $this->createPureDirectFileUrl($row, false);
         $core->jsonE[$key]['file'] = $this->getFileName($row);
         
         if($this->isImage($row['file_extension'])){
            $core->jsonE[$key]['thumbnail'] = $this->createPureDirectFileUrl($row, true);
         }
         if($this->isViewable($row)){
            
            $mime = $this->getMimeFromExtension($row['file_extension']);
            $type = $this->getFileTypeFromMimeType($mime);          
            $core->jsonE[$key]['mime'] = $type;
            
         }         
      }
              
      return ($rows) ? $core->jsonE : 0;              
   }
   
   function searchFolders($query = false) {
      global $db, $core, $user;
      $sql = "SELECT "
              . "\n d.{$this->dpfo}id,"
              . "\n d.{$this->dpfo}parent_id,"
              . "\n d.{$this->dpfo}v_path,"
              . "\n d.{$this->dpfo}name,"
              . "\n d.{$this->dpfo}description"
              . "\n FROM " . $core->dTable . " as d"
              . "\n WHERE "
              . "\n ( d.{$this->dpfo}user_id = " . $user->userid
              . "\n AND d.{$this->dpfo}static != 1 )"
              . "\n AND ( d.{$this->dpfo}name LIKE '%" . $db->escape($query) . "%'"
              . "\n OR d.{$this->dpfo}description LIKE '%" . $db->escape($query) . "%' ) LIMIT 12";
              
              
      $rows = $db->fetch_all($sql);
      
      unset($core->jsonE);
      
      foreach($rows as $key => $row){
         $core->jsonE[$key]['type'] = "folder";
         $core->jsonE[$key]['name'] = $row['folder_name'];
         $core->jsonE[$key]['url'] = $row['folder_v_path'].','.$row['folder_id'].','.$row['folder_parent_id'];
         $core->jsonE[$key]['icon'] = 'icon-folder-close';
      }
              
      return ($rows) ? $core->jsonE : 0;              
              
   }   
   

   /**
    * 
    * @param type $file_path
    * @return boolean
    */
   function deleteFile($file_path) {
      if (@is_file($file_path)) {
         if (@file_exists($file_path)) {
            if (@unlink("$file_path")) {
               $this->f_err = null;
               return true;
            } else if (@exec("del $file_path")) {
               $this->f_err = null;
               return true;
            } else if (@system("del $file_path")) {
               $this->f_err = null;
               return true;
            } else {
               $this->f_err = 'Cannot delete File: Permission denied.';
               return false;
            }
         } else {
            $this->f_err = 'File does not exists.';
            return false;
         }
      } else {
         $this->f_err = 'File does not exists.';
         return false;
      }
   }

   /**
    * 
    * @global type $encr
    * @global type $user
    * @param type $file
    * @param type $userid
    * @return boolean
    */
   function download($file, $userid = false) {
      global $encr, $detect;
      if (!$userid) {
         global $user;
         $userid = $user->userid;
      }

      ignore_user_abort(true);
      set_time_limit(0);

      if (strpos($file, "__") !== false) {
         $part = explode("__", $file);
         $user_dir = $encr->decode($part[0]);
         $file = $part[1];
      } else {
         $user_dir = return6charsha1($userid);
      }

      $server_file = UPLOAD_PATH . $user_dir . '/' . $file;
      $client_file = $file;

      $size = @filesize($server_file);
      if (is_file($server_file)) {
         if (@file_exists($server_file)) {
            if (ini_get('zlib.output_compression')){
                @ini_set('zlib.output_compression', 'On');
            }
            
            $file = $this->getFileInfoFromDBwithKey($this->getFileKeyFromName($client_file, 0));
            
            $path_parts = pathinfo($server_file);
            $file_extension = $file['extension'];

            $types = $this->allFileTypes();
            $ctype = 'application/octet-stream';
            
            
            $ctype = (isset($types[$file_extension]) ?  $types[$file_extension] : $ctype);
            
            @header("Pragma: public");
            @header("Expires: 0");
            @header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            @header("Cache-Control: private", false);
            @header("Content-Type: $ctype");
            @header("Content-Disposition: attachment; filename=\"" . $this->getFileName($file, true, true). "\";");
            @header("Content-Length: $size");
            @header("Content-Transfer-Encoding: binary");
            @header("X-Frame-Options: SAMEORIGIN GOFORIT");
            
            set_time_limit(0);
            $file = @fopen($server_file,"rb");
            while(!feof($file))
            {
                  print(@fread($file, 1024*8));
                  ob_flush();
                  flush();
            } 
            
            @exit;
         } else {
            redirectPage_to("/"); // for after use...
            return false;
         }
      } else {
         redirectPage_to("/"); // for after use...
         return false;
      }
   }

    /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $folder_id
    * @param type $userid
    * @return type
    */
   function getFolderInfoDB($folder_id, $userid = FALSE) {
      global $db, $core, $user;
      $sql = "SELECT * FROM " . $core->dTable . ""
              . "\n WHERE {$this->dpfo}id = " . $folder_id . " AND {$this->dpfo}user_id = " . (($userid) ? $userid : $user->userid) . " ";
      $row = $db->first($sql);

      return $row ? $row : 0;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $file_id
    * @param type $userid
    * @return type
    */
   function getFileInfoFromDB($file_id, $userid = FALSE) {
      global $db, $core, $user;
      
      if(!is_numeric($file_id)) return 0; // for unwanted results like guessing ID of an item. ;;
      
      $sql = "SELECT *, DATE(file_date) as date FROM " . $core->fTable . ""
              . "\n WHERE {$this->dpfi}id = " .(int)$file_id . " AND {$this->dpfi}user_id = " . (($userid) ? $userid : $user->userid) . " ";
      $row = $db->first($sql);

      return $row ? $row : 0;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $file_key
    * @param type $userid
    * @return type
    */
   function getFileInfoFromDBwithKey($file_key) {
      global $db, $core, $user;
      $sql = "SELECT * FROM " . $core->fTable . ""
              . "\n WHERE {$this->dpfi}key = '".$file_key. "'";
      $row = $db->first($sql);

      return $row ? $row : 0;
   }

   /**
    * 
    * @global type $core
    * @global type $user
    * @param type $name
    * @return string
    */
   function createFileName($name) {
      global $core, $user;
      $partRand = $core->getUniqueCode(9);
      $key = $partRand . '_';

      if (isset($_SESSION[$name]) && $_SESSION['ups'] == $name) {
         $return = $_SESSION[$name];
      } else {

         if ($this->checkfilexistDB($key) == 0) {
            $return = $_SESSION[$name] = $key . makeSlug($name);
            $_SESSION['ups'] = $_SESSION['filetitle'] = $name;
         } else {
            $this->createFileName($name);
            $_SESSION['filetitle'] = $name;
         }
      }

      return $return;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $key
    * @return type
    */
   function checkfilexistDB($key) {
      global $db, $core;
      $sql = $db->query("SELECT "
              . "\n {$this->dpfi}key"
              . "\n FROM " . $core->fTable
              . "\n WHERE {$this->dpfi}key = '" . $key . "'");
      return $db->numrows($sql);
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $file
    * @param type $onfile
    * @return type
    */
   function insertfileDataDB($file, $onfile = false) {
      global $db, $core, $user;
      $data = $onfile ? $file : array(
          $this->dpfi . 'user_id' => $user->userid,
          $this->dpfi . 'note' => NULL,
          $this->dpfi . 'name' => $file->name,
          $this->dpfi . 'key' => $file->key,
          $this->dpfi . 'extension' => $file->extension,
          $this->dpfi . 'folder' => $file->folder,
          $this->dpfi . 'mime_folder' => $file->mime,
          $this->dpfi . 'size' => $file->size,
          $this->dpfi . 'date' => "NOW()"
      );

      if (isset($_SESSION['filetitle']) && !empty($_SESSION['filetitle'])) {
         $data['file_title'] = $_SESSION['filetitle'];
         unset($_SESSION['filetitle']);
      }

      $result = $db->insert($core->fTable, $data);
      
      if ($db->affected()) {
         
         //erase completed file session
         unset($_SESSION[$_SESSION['ups']]);
         unset($_SESSION['ups']);
         $_SESSION['ups'] = '';
         $_SESSION[$_SESSION['ups']] = '';
         
         $fileID = $db->insertid();
         
         if ($data['file_folder'] > 6)//update folder_files number
            $this->setUserFolderFiles($data['file_folder'], false);

         unset($data);
         return $fileID;
         
      }else{
         
         $core->jsonE['error_upload_db_insert'] = "Sorry! I encountered an error.<br>Error Code: " . mysql_errno($result);
         return $core->jsonE;
      }
   }

   /**
    * 
    * @param type $name
    * @param type $key
    * @return type
    */
   function getFileKeyFromName($name, $key = 0) {
      $arr = explode("_", $name);
      return $arr[$key];
   }

   /**
    * 
    * @param type $file
    * @return type
    */
   function getFileName($file, $ext=true, $original = false) {
       
       if($original){
           return $file['file_name'] . (!$ext ? '' : ('.' .$file['file_extension']));
       }else{
           return urldecode($file['file_key'] . '_' . makeSlug($file['file_name']) . (!$ext ? '' : ('.' .$file['file_extension'])));
       }
           
   }

   /**
    * 
    * @global type $user
    * @param type $file
    * @param type $tn
    * @param type $user_dir
    * @return type
    */
   function createPureDirectFileUrl($file, $tn, $user_dir = false) {
      global $user;
      
      $filename = $this->getFileName($file);
      
      return CLO_URL . '/' . ($user_dir ? $user_dir : $user->user_dir) . '/' . ($tn ? ('thumbnail/') : '') . $filename;
   }
   
   /**
    * 
    * @param type $file
    * @param type $user_dir
    * @return type
    */
   function createImageRequestUrl($file, $user_dir=false, $userid=false, $custom_resolution=false){
      global $user, $core;
      
      $user_dir = ($user_dir ? $user_dir : $user->user_dir);
      
      $_SESSION['ctprID']  = ($userid ? $userid : $user->userid);
      $_SESSION['ctprDIR'] = $user_dir;
      
      $filename = $this->getFileName($file, true);
      
      $resolution = $core->client_resolution($custom_resolution ? $custom_resolution : false);
      
      return CLO_URL.'/'.$user_dir.'/view/'.($custom_resolution ? $custom_resolution.'/' : '').$filename;
      
   }
   
   function createImageViewUrl($file_name, $user_dir=false, $custom_resolution=false) {
      global $core;
      $file = $this->getFileInfoFromDBwithKey($this->getFileKeyFromName($file_name));
      
      $filename = $this->getFileName($file);
      
      $resolution = $core->client_resolution($custom_resolution ? $custom_resolution : false);

      $user_dir = (isset($_SESSION['ctprDIR']) ? $_SESSION['ctprDIR'] : (($user_dir) ? $user_dir : $user->user_dir));
      $path = UPLOAD_PATH . $user_dir;
      $imag_path = $path . DS . $filename;
      $res_image_name = $this->getFileName($file, false);
      $image = Image::open($imag_path);
      
      if($custom_resolution){
         $image->cropResize($resolution[0], $resolution[1]);
      }else{
         $image->scaleResize($resolution[0], $resolution[1]);
      }
      
      $image->setCacheDir(UPLOAD_PATH . $user_dir . DS.'view'.DS.$resolution[2])
            ->setPrettyName($res_image_name, false)
            ->guess();
      //unset($_SESSION['ctprID']);
      return $this->returnImagePath($image, $path, $user_dir);
   }
   
   /**
    * 
    * @param type $items
    */
   function createMultipleImageViewUrl($items){
      
      foreach ($items as $key => $item){
         
         $this->createImageViewUrl($item['name'], $item['user_dir'], $item['resolution']);
         
      }
   }   
   /**
    * 
    * @param type $cacheURI
    * @param type $path
    * @param type $user_dir
    * @return type
    */
   
   function returnImagePath($cacheURI, $path, $user_dir){
      
      return str_replace($path, $user_dir, $cacheURI);
      
   }
   
   /**
    * 
    * @global type $user
    * @param type $file
    * @return type
    */
   function createViewUrI($file) {
      global $user;
      if (in_array($file['file_extension'], $this->prohibitedTypesArr)) {
         return $this->createDownloadUrI($this->getFileName($file), false);
      }
      else{
         if($this->isImage($file['file_extension'])){
            return $this->createImageRequestUrl($file);
         }
         return CLO_URL . '/' . $user->user_dir . '/' . $this->getFileName($file);
      }
   }

   /**
    * 
    * @global type $user
    * @param type $file_name
    * @param type $user_id
    * @return type
    */
   function createDownloadUrI($file_name, $user_id = false) {
      global $user;
      if (!$user_id) {
         return CLO_URL . '/?octet&file=' . $file_name;
      }
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $newfoldername
    * @param type $newdescription
    * @param type $newicon
    * @param type $dir
    */
   function editFolder($newfoldername, $newdescription, $newicon, $dir) {
      global $db, $core, $user;

      if ($newfoldername == "") {
         $core->jsonE["result"] = 0;
         $core->jsonE["message"] = '<strong>Error!</strong> Please enter a folder name.';
      }

      if (empty($core->jsonE["message"])):
         $data = array(
             $this->dpfo . 'user_id' => $user->userid,
             $this->dpfo . 'v_path' => sanitise(makeSlug($newfoldername)),
             $this->dpfo . 'name' => sanitise($newfoldername),
             $this->dpfo . 'description' => sanitise($newdescription),
             $this->dpfo . 'icon' => ($newicon ? $newicon : NULL)
         );
         $result = $db->update($core->dTable, $data, "{$this->dpfo}id='" . (int) $dir . "' AND {$this->dpfo}user_id =" . $user->userid . "");

         if ($db->affected()) {
            $core->jsonE["result"] = 1;
            $core->jsonE["message"] = '<strong>Folder features changed successfully.</strong>';
         } else {
            $core->jsonE["result"] = 0;
            $core->jsonE['message'] = "Sorry! I couldn't find any changes to edit.";
         }
      endif;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $foldername
    * @param type $description
    * @param type $icon
    * @param type $dir
    */
   function createFolder($foldername, $description, $icon, $dir) {
      global $db, $core, $user;

      if ($foldername == "") {
         $core->jsonE["result"] = 0;
         $core->jsonE["message"] = '<strong>Error!</strong> Please enter a folder name.';
      }

      if (empty($core->jsonE["message"])):

         if ($this->checkFolderExistDB($foldername, $dir)) {
            $core->jsonE["result"] = 0;
            $core->jsonE["message"] = '<strong>Error!</strong> This directory already has a folder named <i>' . $foldername . '</i>';
         } else {
            $data = array(
                $this->dpfo . 'user_id' => $user->userid,
                $this->dpfo . 'v_path' => sanitise(makeSlug($foldername)),
                $this->dpfo . 'name' => sanitise($foldername),
                $this->dpfo . 'parent_id' => sanitise($dir),
                $this->dpfo . 'level' => ($this->getFolderLevel($dir) + 1),
                $this->dpfo . 'description' => sanitise($description),
                $this->dpfo . 'icon' => ($icon ? $icon : NULL),
                $this->dpfo . 'static' => 0
            );
            $result = $db->insert($core->dTable, $data);

            if ($db->affected() && $result) {
               $core->jsonE["result"] = 1;
               $core->jsonE["message"] = '<strong>New folder :<br /><i>' . $foldername . '</i> has been created successfully.</strong>';
            } else {
               $core->jsonE['error_upload_db_insert'] = "Sorry! I encountered an error.<br>Error Code: " . mysqli_errno($result);
            }
         }
      endif;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $dir
    * @param type $target
    * @param type $sub
    */
   function moveFolder($dir, $target, $olddir = false) {
      global $db, $core, $user;

      $dirlevel = $this->getFolderLevel($dir);

      $data = array(
          $this->dpfo . 'parent_id' => $target,
          $this->dpfo . 'level' => ($this->getFolderLevel($target) + 1)
      );

      $db->update($core->dTable, $data, "{$this->dpfo}id='" . (int) $dir . "' AND {$this->dpfo}user_id =" . $user->userid . "");

      $newDirlevel = $this->getFolderLevel($dir);
      $levelChange = ($newDirlevel - $dirlevel);

      $folders = $this->getUserFolders(false);

      $subdirs = $this->getSubFoldersOfFolder($folders, $dir, false);

      if (is_array($subdirs) && !empty($subdirs)) {
         foreach ($subdirs as $key => $val) {

            $subdirlevel = $val['folder_level'];

            $newLevel = ($subdirlevel + $levelChange);
            $sdata = array(
                $this->dpfo . 'parent_id' => $val['folder_parent_id'],
                $this->dpfo . 'level' => (int) $newLevel
            );
            $db->update($core->dTable, $sdata, "{$this->dpfo}id='" . (int) $val['folder_id'] . "' AND {$this->dpfo}user_id =" . $user->userid . "");
            $folders = $this->getUserFolders(false);
         }
      }

      if ($db->affected()) {
         $this->setUserFolderFiles($target, $olddir);
         $core->jsonE["result"] = 1;
         $core->jsonE["message"] = '<strong>Folder moved successfully.</strong>';
      } else {
         $core->jsonE["result"] = 0;
         $core->jsonE['message'] = "Sorry! I couldn't find any changes to edit.";
      }
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $dir
    */
   function deleteFolder($dir) {
      global $db, $core;
      $allfolders = $this->getUserFolders(false);
      $result = 0;

      if ($this->deleteFolderProc($allfolders, $dir)) {
         //delete the files inside this $dir
         if ($this->countUserFiles($dir) > 0)
            $this->deleteFileorFiles(false, $dir);

         $this->setUserFolderFiles($dir, false);
         //finally delete this $dir
         $result = $db->delete($core->dTable, $this->dpfo . 'id = ' . $dir);

         if ($result) {
            $core->jsonE["result"] = 1;
            $core->jsonE["message"] = '<strong>Delete folder : <i>' . $this->folder_name . '</i> and all its files and sub-directories deleted successfully.</strong>';
         } else {
            $core->jsonE["result"] = 0;
            $core->jsonE["message"] = '<strong>Sorry! I encountered an error.<br>This Error shows that I can not perform as i have to! You might want to contact to the <a href="mailto:' . $core->site_email . '">administrator</a>.</strong>';
         }
      } else {
         $core->jsonE["result"] = 0;
         $core->jsonE["message"] = '<strong>Sorry! I encountered an error.<br>This Error shows that I can not perform as i have to! You might want to contact to the <a href="mailto:' . $core->site_email . '">administrator</a>.</strong>';
      }
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $data
    * @param type $current_folder
    * @return int
    */
   function deleteFolderProc($data, $current_folder) {
      global $db, $core;
      $result = 0;
      foreach ($data as $key => $row) {
         if ($current_folder == $row['folder_id']) {
            $this->folder_name = $row['folder_name'];
         }
         if ($row['folder_parent_id'] == $current_folder) {

            $this->deleteFileorFiles(false, $row['folder_id']);

            $result = $db->delete($core->dTable, $this->dpfo . 'id = ' . $row['folder_id']);

            $this->deleteFolderProc($data, $row['folder_id']);
         }
      }
      unset($row);
      return 1;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $files
    * @param type $target
    * @param type $olddir
    */
   function moveFiles($files, $target, $olddir = false) {
      global $db, $core, $user;

      $requestedFiles = $this->getRequestedFilesArr($files);
      $result = $ttl = false;

      foreach ($requestedFiles as $file) {

         $data = array(
             $this->dpfi . 'folder' => $target
         );
         $result = $db->update($core->fTable, $data, "{$this->dpfi}id='" . (int) $file . "' AND {$this->dpfi}user_id =" . $user->userid . "");
      }
      if ($db->affected()) {

         //update new folder item count
         $this->setUserFolderFiles($target, $olddir);

         $core->jsonE["result"] = 1;
         $core->jsonE["message"] = '<strong>Selected file(s) moved successfully.</strong>';
      } else {
         $core->jsonE["result"] = 0;
         $core->jsonE['message'] = "Sorry! I couldn't find any changes to edit.";
      }
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $files
    * @param type $dir
    * @return boolean
    */
   function deleteFileorFiles($files = false, $dir = false, $cdir = false) {
      global $db, $core, $user;
      if ($dir) {
         $cdir = $dir;
         $files_to_delete = $this->getFolderFiles($dir);
         if ($files_to_delete)
            foreach ($files_to_delete as $key => $file) {
               $file_real_path = $this->getSingleFilePath($file, false);

               $deleted = $db->delete($core->fTable, $this->dpfi . 'folder = ' . $file['file_folder']) || 0;

               //delete file physically.
               if ($deleted) {
                  if (is_file($file_real_path))
                     $this->deleteFile($file_real_path);
                  if ($this->isImage($file)) {
                     if (is_file($this->getSingleFilePath($file, true)))
                        $this->deleteFile($this->getSingleFilePath($file, true)); //thumbnail
                  }
               }
               else
                  return false;
            }
      }else {
         $err = 0;
         //its an array of files.
         $files_to_delete = $this->getRequestedFilesArr($files);

         if ($files_to_delete)
            foreach ($files_to_delete as $id) {
               $file = $this->getFileInfoFromDB($id, false);
               if (!$file) {
                  $err = 1;
               }

               $file_real_path = $this->getSingleFilePath($file);
               $deleted = $db->delete($core->fTable, $this->dpfi . 'id = ' . $id) || 0;

               //delete single file physically.
               if ($deleted) {
                  if (is_file($file_real_path))
                     $this->deleteFile($file_real_path);
                  else
                     $err = 0;

                  if ($this->isImage($file)) {
                     if (is_file($this->getSingleFilePath($file, true)))
                        $this->deleteFile($this->getSingleFilePath($file, true)); //thumbnail
                     else
                        $err = 0;
                  }
               }
               else {
                  $err = 1;
               }
            }
         if ($err == 0) {
            //update folder item count
            $this->setUserFolderFiles($cdir, false);

            $core->jsonE["result"] = 1;
            $core->jsonE["message"] = '<strong>Selected file(s) deleted successfully.</strong>';
         } else {
            $core->jsonE["result"] = 0;
            $core->jsonE['message'] = "Sorry! I encountered an error. Please try again.";
         }
      }

      //return $deleted;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @param type $dir
    * @param type $olddir
    * @return type
    */
   function updateUserFolderFileCount($dir, $allfolders) {
      global $db, $core, $user;

      $dirArr = array();

      $dirArr[] = $tardir = isset($allfolders[$dir]) ? $allfolders[$dir] : false;

      $parents = $tardir['folder_parent_id'] > 6 ? $this->getParentFoldersOfFolder($allfolders, $tardir['folder_parent_id']) : null;

      $dirs = $parents != null ? array_merge($parents, $dirArr) : $dirArr;

      $dirs = array_reverse($dirs);

      foreach ($dirs as $d => $line) {

         $sum = $this->countFolderFilesProc($line['folder_id'], $allfolders);

         $data = array(
             $this->dpfo . 'files' => $sum
         );

         $db->update($core->dTable, $data, "{$this->dpfo}id='" . (int) $line['folder_id'] . "' AND {$this->dpfo}user_id =" . $user->userid . "");
         $sum = 0;

         unset($line);
      }
   }

   /**
    * 
    * @param type $folder_id
    * @param type $allfolders
    * @param type $cursum
    * @return type
    */
   function countFolderFilesProc($folder_id, $allfolders, $cursum = false) {

      $this->subdirs = array();
      $subdirs = $this->getSubFoldersOfFolder($allfolders, $folder_id, true, $folder_id);

      //count and sum child folders files of current folder
      $sum = 0;
      foreach ($subdirs as $key => $row) {

         $total = $this->countUserFiles($row['folder_id']);

         $sum = $sum + $total;
      }

      return $sum;
   }

   /**
    * 
    * @param type $target
    * @param type $old
    */
   function setUserFolderFiles($target, $old) {

      $allfolders = $this->getUserFolders(false);

      if ($target > 6)
         $this->updateUserFolderFileCount($allfolders[$target]['folder_id'], $allfolders);
      if ($old > 6)
         $this->updateUserFolderFileCount($allfolders[$old]['folder_id'], $allfolders);
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $userid
    * @return type
    */
   function deleteUserAllFilesDB($userid) {
      global $db, $core;
      return $db->delete($core->fTable, $this->dpfi . 'user_id = ' . $userid) || 0;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $userid
    * @return type
    */
   function deleteUserAllFoldersDB($userid) {
      global $db, $core;
      return $db->delete($core->dTable, $this->dpfo . 'user_id = ' . $userid) || 0;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $user
    * @global type $encr
    * @param type $dir
    * @return type
    */
   function ArchiveFolderZip($dir) {
      global $db, $core, $user, $encr;

      $allfolders = $data = $this->getUserFolders($dir);

      $subdirs = $this->getSubFoldersOfFolder($allfolders, $dir, true);

      $folderName = $subdirs['current']['folder_name'];
      //create random(hash) zip file name from requested folder name
      $encodedname = sanitise(makeSlug($folderName)) . '_' . date("d.m.Y-Hs");

      $randname = $encodedname . '.zip';

      $newfilename = $this->createFileName($randname);

      $zipname = 'files' . DS . $user->user_dir . DS . $newfilename;
      if (!is_file('files' . DS . $user->user_dir . DS . $newfilename)) {
         //call zipclass
         $zip = new ZipArchive;
         //open zip file
         if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {

            foreach ($subdirs as $key => $row) {

               $this->folderPath = '';
               $folderFiles = $this->getFolderFiles($row['folder_id']);

               $this->folderPath = $this->getVirtualPath($subdirs, $row['folder_id'], $row['folder_id']);

               if ($folderFiles)
                  foreach ($folderFiles as $k => $file) {

                     $fname = $file['file_name'] . '.' . $file['file_extension'];

                     $fileto = $this->getSingleFilePath($file, false);

                     $zip->addFile($fileto, $this->folderPath . $fname);
                  }
               unset($file);
            }

            $zip->close();

            //insert zip file to DB
            $filekey = $this->getFileKeyFromName($newfilename, 0);

            $filename = $this->getFileKeyFromName($newfilename, 1);

            $info = pathinfo($zipname);
            //$name = basename($zipname, '.zip');


            $zipFile = array(
                $this->dpfi . 'user_id' => $user->userid,
                $this->dpfi . 'note' => "Zip File created by you",
                $this->dpfi . 'name' => $encodedname,
                $this->dpfi . 'key' => $filekey,
                $this->dpfi . 'extension' => $info['extension'],
                $this->dpfi . 'folder' => 1,
                $this->dpfi . 'mime_folder' => 6,
                $this->dpfi . 'size' => filesize($zipname),
                $this->dpfi . 'date' => "NOW()"
            );

            $this->insertfileDataDB($zipFile, true);
         }
      }
      $core->jsonE['file'] = $newfilename;
      return $core->jsonE;
   }

   function getVirtualPath($data, $current_dir, $cdir) {

      foreach ($data as $key => $row) {

         if ($row['folder_id'] == $current_dir) {

            $this->folderPath = $row['folder_name'] . DS . $this->folderPath;

            $this->getVirtualPath($data, $row['folder_parent_id'], $cdir);
         }
         unset($row);
      }

      return $this->folderPath;
   }

   function getSubfoldersToZip($data, $current_folder, $current_level) {
      foreach ($data as $key => $row) {

         if ($row['folder_parent_id'] == $current_folder) {

            if ($row['folder_level'] > ($current_level + 1)) {
               $this->folderLevel.= $row['folder_name'] . DS;
            } else {
               $this->folderLevel = $data[$current_folder]['folder_name'] . DS . $row['folder_name'];
            }

            $this->getSubfoldersToZip($data, $row['folder_id'], $current_level);
         }
         unset($row);
      }

      return $this->folderLevel;
   }

   function getSingleFilePath($file, $thumb = false) {
      global $user;
      return UPLOAD_PATH . return6charsha1($user->userid) . DS . ($thumb ? ('thumbnail' . DS) : '') . $file['file_key'] . '_' . $file['file_name'] . '.' . $file['file_extension'];
   }

   function getRequestedFilesArr($files) {

      if (!$files)
         return false;

      $files = str_replace("f", "", $files);

      if (strpos($files, ",") !== false) { //comma is here so multiple files was requested explode it to create an array;
         $arr = explode(",", $files);
      } else {//its a single file so retur single item array;
         $arr = array($files);
      }
      return $arr;
   }

   function fileOrFolder($val) {
      if (strpos($val, "f") !== false) {
         return 'file';
      }
      return 'folder';
   }

   function checkFolderExistDB($folder_name, $parent_id) {
      global $db, $core, $user;
      $sql = $db->query("SELECT "
              . "\n {$this->dpfo}name"
              . "\n FROM " . $core->dTable
              . "\n WHERE {$this->dpfo}name = '" . $folder_name . "' AND {$this->dpfo}parent_id = " . $parent_id . "");
      return $db->numrows($sql);
   }

   function getFolderLevel($parent_id) {
      global $db, $core, $user;
      $sql = "SELECT {$this->dpfo}level FROM " . $core->dTable . ""
              . "\n WHERE {$this->dpfo}user_id = " . $user->userid . " AND {$this->dpfo}id = " . $parent_id . "";
      $row = $db->first($sql);

      return $row[$this->dpfo . 'level'];
   }
   
   /**
    * 
    * @param type $mime
    * @return string
    */
   function getFileTypeFromMimeType($mime) {
      if ($mime && strpos($mime, "/") !== false) {
         $temp = explode("/", $mime);
         return rtrim($temp[0], " "); //ie image/jpeg -> returns image or jpeg.
      }
      else
         return '';
   }
   
   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $flip
    * @return type
    */
   function arrangeIdStaticFoldersType($flip = false) {
      global $db, $core;

      $sql = "SELECT {$this->dpfo}id, {$this->dpfo}mime FROM " . $core->dTable . ""
              . "\n WHERE {$this->dpfo}static = 1 AND {$this->dpfo}id != 1"; //exclude root folder

      $rows = $db->fetch_all($sql);
      foreach ($rows as $key => $row) {
         $this->static_folder_mime_Arr[$row['folder_mime']] = $row['folder_id'];
      }
      if ($flip)
         array_flip($this->static_folder_mime_Arr);

      return $this->static_folder_mime_Arr;
   }

   function setfilemimeFolderId($mime, $ext) {

      //return $this->static_folder_mime_Arr[$this->getDocOrApp($ext)];
      $document = $this->isDocument($ext);
      if (!$document && $mime) {

         $type = $this->getFileTypeFromMimeType($mime, false);

         $return = (isset($this->static_folder_mime_Arr[$type]) ? $this->static_folder_mime_Arr[$type] : $this->static_folder_mime_Arr['other']);
      } elseif (!$document && !$mime) {
         $return = $this->getMimeFromExtension($ext);
      }

      if ($document) {
         $return = $this->static_folder_mime_Arr[$document];
      }
      return $return;
   }

   function getMimeFromExtension($ext) {
      $types = $this->allFileTypes();

      $extension = strtolower($ext);

      return (isset($types[$extension]) ? $types[$extension] : 'unknown/file');
   }

   function isDocument($ext) {
      //documents like ms office, txt etc...
      $documentTypes = array(
          'pdf',
          'doc',
          'rtf',
          'docx',
          'xls',
          'xlsx',
          'xltx',
          'potx',
          'ppsx',
          'sldx',
          'ppt',
          'rtf',
          'docx',
          'dotx',
          'xlam',
          'xlsb',
          'txt',
          'odt',
          'ods'
      );
      $isDocument = in_array($ext, $documentTypes);
      return $isDocument ? 'document' : 0;
   }

   function isImage($ext) {
      //inline image files.
      if (is_array($ext)) {
         $ext = $ext['file_extension'];
      }
      $imageTypes = array(
          'jpg',
          'jpeg',
          'gif',
          'png'
      );

      $isImage = in_array(strtolower($ext), $imageTypes);
      return $isImage ? 'image' : false;
   }
   
   function isAudio($ext){
      //sound files (.aif etc...)
      if (is_array($ext)) {
         $ext = $ext['file_extension'];
      }
      $alltypes = $this->allFileTypes();
      
      $audioArr = array();
      
      foreach($alltypes as $key => $mime){
         if (strpos($mime, "audio") !== false) {
            $audioArr[] = $key;
         }
      }
      
      $isSound = in_array(strtolower($ext), $audioArr);
      return $isSound ? 'audio' : false;
   }
   
   function isVideo($ext){
      //video files (.m4p etc...)
      if (is_array($ext)) {
         $ext = $ext['file_extension'];
      }
      $alltypes = $this->allFileTypes();
      
      $videoArr = array();
      
      foreach($alltypes as $key => $mime){
         if (strpos($mime, "video") !== false) {
            $videoArr[] = $key;
         }
      }
      
      $isVideo = in_array(strtolower($ext), $videoArr);
      return $isVideo ? 'video' : false;
   }   
   
   function getTypeIcon($file, $byext = false){
      $ext = ($byext ? $byext : $file['file_extension']);
      
      if(!$file && !$byext){
         return 'icon-frown';
      }
      return $this->isImage($ext) ? $this->isImage($ext) : (
              $this->isDocument($ext) ? $this->isDocument($ext) : (
                      $this->isAudio($ext) ? $this->isAudio($ext) : (
                              $this->isVideo($ext) ? $this->isVideo($ext) : 'other'
                              )
                      )
              );
   }
   
   function createJSTypesArrays(){
      $types = $this->allFileTypes();
      $typesArray = array();
      
      foreach($types as $key => $val){
         if($this->isImage($key)){
            
            $tkey = 'image-Types';
            
            
         }else if($this->isVideo($key)){
            
            $tkey = 'video-Types';
            
         }else if($this->isAudio($key)){
            
            $tkey = 'audio-Types';
            
         }else if($this->isDocument($key)){
            
            $tkey = 'document-Types';
            
         }else{
            $tkey = 'other-Types';
         }
         
         $typesArray[$tkey][] = $key;
         
      }
      
      return json_encode($typesArray);
   }
   
   function allFileTypes() {
      return array(
          "323" => "text/h323",
          "7z" => "application/x-7z-compressed",
          "aac" => "audio/x-aac",
          "acx" => "application/internet-property-stream",
          "ai" => "application/postscript",
          "aif" => "audio/x-aiff",
          "aifc" => "audio/x-aiff",
          "aiff" => "audio/x-aiff",
          "asf" => "video/x-ms-asf",
          "asr" => "video/x-ms-asf",
          "asx" => "video/x-ms-asf",
          "au" => "audio/basic",
          "avi" => "video/x-msvideo",
          "axs" => "application/olescript",
          "bcpio" => "application/x-bcpio",
          "bin" => "application/octet-stream",
          "bmp" => "image/bmp",
          "cat" => "application/vnd.ms-pkiseccat",
          "cdf" => "application/x-cdf",
          "cer" => "application/x-x509-ca-cert",
          "class" => "application/octet-stream",
          "clp" => "application/x-msclip",
          "cmx" => "image/x-cmx",
          "cod" => "image/cis-cod",
          "cpio" => "application/x-cpio",
          "crd" => "application/x-mscardfile",
          "crl" => "application/pkix-crl",
          "crt" => "application/x-x509-ca-cert",
          "csh" => "application/x-csh",
          "css" => "text/css",
          "dcr" => "application/x-director",
          "der" => "application/x-x509-ca-cert",
          "dir" => "application/x-director",
          "dll" => "application/x-msdownload",
          "dms" => "application/octet-stream",
          "doc" => "application/msword",
          "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
          "dvi" => "application/x-dvi",
          "dxr" => "application/x-director",
          "eot" => "application/vnd.ms-fontobject",
          "eps" => "application/postscript",
          "etx" => "text/x-setext",
          "evy" => "application/envoy",
          "exe" => "application/octet-stream",
          "fif" => "application/fractals",
          "flr" => "x-world/x-vrml",
          "gif" => "image/gif",
          "gtar" => "application/x-gtar",
          "gz" => "application/x-gzip",
          "hdf" => "application/x-hdf",
          "hlp" => "application/winhlp",
          "hqx" => "application/mac-binhex40",
          "hta" => "application/hta",
          "htc" => "text/x-component",
          "htm" => "text/html",
          "html" => "text/html",
          "htt" => "text/webviewhtml",
          "ico" => "image/x-icon",
          "ief" => "image/ief",
          "iii" => "application/x-iphone",
          "ini" => "text/plain",
          "ins" => "application/x-internet-signup",
          "isp" => "application/x-internet-signup",
          "jfif" => "image/pipeg",
          "jpe" => "image/jpeg",
          "jpeg" => "image/jpeg",
          "jpg" => "image/jpeg",
          "js" => "application/x-javascript",
          "latex" => "application/x-latex",
          "lha" => "application/octet-stream",
          "log" => "text/plain",
          "lsf" => "video/x-la-asf",
          "lsx" => "video/x-la-asf",
          "lzh" => "application/octet-stream",
          "m13" => "application/x-msmediaview",
          "m14" => "application/x-msmediaview",
          "m2v" => "video/mpeg",
          "m3u" => "audio/x-mpegurl",
          "man" => "application/x-troff-man",
          "mdb" => "application/x-msaccess",
          "me" => "application/x-troff-me",
          "mht" => "message/rfc822",
          "mhtml" => "message/rfc822",
          "mid" => "audio/mid",
          "mkv" => "video/x-matroska",
          "mny" => "application/x-msmoney",
          "mov" => "video/quicktime",
          "movie" => "video/x-sgi-movie",
          "mp2" => "video/mpeg",
          "mp3" => "audio/mpeg",
          "mp4" => "video/mp4",
          "mpa" => "video/mpeg",
          "mpe" => "video/mpeg",
          "mpeg" => "video/mpeg",
          "mpg" => "video/mpeg",
          "mpp" => "application/vnd.ms-project",
          "mpv2" => "video/mpeg",
          "ms" => "application/x-troff-ms",
          "mvb" => "application/x-msmediaview",
          "nws" => "message/rfc822",
          "oda" => "application/oda",
          "oga" => "audio/ogg",
          "ogg" => "audio/ogg",
          "ogv" => "video/ogg",
          "p10" => "application/pkcs10",
          "p12" => "application/x-pkcs12",
          "p7b" => "application/x-pkcs7-certificates",
          "p7c" => "application/x-pkcs7-mime",
          "p7m" => "application/x-pkcs7-mime",
          "p7r" => "application/x-pkcs7-certreqresp",
          "p7s" => "application/x-pkcs7-signature",
          "pbm" => "image/x-portable-bitmap",
          "php" => "application/php",
          "pdf" => "application/pdf",
          "pfx" => "application/x-pkcs12",
          "pgm" => "image/x-portable-graymap",
          "pko" => "application/ynd.ms-pkipko",
          "pma" => "application/x-perfmon",
          "pmc" => "application/x-perfmon",
          "pml" => "application/x-perfmon",
          "pmr" => "application/x-perfmon",
          "pmw" => "application/x-perfmon",
          "png" => "image/png",
          "pnm" => "image/x-portable-anymap",
          "pot" => "application/vnd.ms-powerpoint",
          "ppm" => "image/x-portable-pixmap",
          "pps" => "application/vnd.ms-powerpoint",
          "ppt" => "application/vnd.ms-powerpoint",
          "prf" => "application/pics-rules",
          "ps" => "application/postscript",
          "pub" => "application/x-mspublisher",
          "qt" => "video/quicktime",
          "ra" => "audio/x-pn-realaudio",
          "ram" => "audio/x-pn-realaudio",
          "ras" => "image/x-cmu-raster",
          "rar" => "application/x-rar-compressed",
          "rgb" => "image/x-rgb",
          "rmi" => "audio/mid",
          "roff" => "application/x-troff",
          "rtf" => "application/rtf",
          "rtx" => "text/richtext",
          "scd" => "application/x-msschedule",
          "sct" => "text/scriptlet",
          "setpay" => "application/set-payment-initiation",
          "setreg" => "application/set-registration-initiation",
          "sh" => "application/x-sh",
          "shar" => "application/x-shar",
          "sit" => "application/x-stuffit",
          "snd" => "audio/basic",
          "spc" => "application/x-pkcs7-certificates",
          "spl" => "application/futuresplash",
          "sql" => "text/plain",
          "src" => "application/x-wais-source",
          "srt" => "text/plain",
          "sst" => "application/vnd.ms-pkicertstore",
          "stl" => "application/vnd.ms-pkistl",
          "stm" => "text/html",
          "svg" => "image/svg+xml",
          "sv4cpio" => "application/x-sv4cpio",
          "sv4crc" => "application/x-sv4crc",
          "swf" => "application/x-shockwave-flash",
          "t" => "application/x-troff",
          "tar" => "application/x-tar",
          "tcl" => "application/x-tcl",
          "tex" => "application/x-tex",
          "texi" => "application/x-texinfo",
          "texinfo" => "application/x-texinfo",
          "tgz" => "application/x-compressed",
          "tif" => "image/tiff",
          "tiff" => "image/tiff",
          "tr" => "application/x-troff",
          "torrent" => "application/x-bittorrent",
          "trm" => "application/x-msterminal",
          "tsv" => "text/tab-separated-values",
          "ttf" => "application/x-font-ttf",
          "txt" => "text/plain",
          "uls" => "text/iuls",
          "ustar" => "application/x-ustar",
          "vcf" => "text/x-vcard",
          "vrml" => "x-world/x-vrml",
          "wav" => "audio/x-wav",
          "wcm" => "application/vnd.ms-works",
          "wdb" => "application/vnd.ms-works",
          "wks" => "application/vnd.ms-works",
          "wma" => "audio/x-ms-wma",
          "wmf" => "application/x-msmetafile",
          "wmv" => "video/x-ms-wmv",
          "woff" => "application/x-font-woff",
          "wps" => "application/vnd.ms-works",
          "wri" => "application/x-mswrite",
          "wrl" => "x-world/x-vrml",
          "wrz" => "x-world/x-vrml",
          "xaf" => "x-world/x-vrml",
          "xbm" => "image/x-xbitmap",
          "xml" => 'application/xml',
          "xla" => "application/vnd.ms-excel",
          "xlc" => "application/vnd.ms-excel",
          "xlm" => "application/vnd.ms-excel",
          "xls" => "application/vnd.ms-excel",
          "xlt" => "application/vnd.ms-excel",
          "xlw" => "application/vnd.ms-excel",
          "xof" => "x-world/x-vrml",
          "xpm" => "image/x-xpixmap",
          "xwd" => "image/x-xwindowdump",
          "z" => "application/x-compress",
          "zip" => "application/zip",
          "file" => "unknown/File"
      );
   }

   function checkFile($file_path) {

      if (file_exists($file_path)) {
         return true;
      } else {
         return false;
      }
   }

   function isViewable($file) {

      return in_array(strtolower($file['file_extension']), $this->viewableTypesArr);
   }
   
   /**
    * 
    * @param type $exts
    * @param type $mode
    * @return type
    */
   public function getAllowedTypesRegex($exts, $mode) {

      $exts = preg_replace("/\s/", "", $exts);

      if ($mode && ($mode == 'js' || $mode == 'php')) {
         $result = str_replace(",", "|", $exts);
      } else if (!$mode) {
         $result = str_replace("|", ",", $exts);
      } else {
         $result = str_replace(",", ", ", $exts);
      }
      return $result;
   }
   
   /**
    * @description Creates user sample data on registration.
    * @global \Nedo\type $db
    * @global \Nedo\type $core
    * @param type $user_id
    */
   public function createSampleData($user_id) {
      global $db, $core;

      $data = array(
          $this->dpfo . 'user_id' => $user_id,
          $this->dpfo . 'v_path' => 'sample_folder',
          $this->dpfo . 'name' => 'Sample Folder',
          $this->dpfo . 'parent_id' => 1,
          $this->dpfo . 'level' => 1,
          $this->dpfo . 'description' => 'This is your first folder.',
          $this->dpfo . 'icon' => NULL,
          $this->dpfo . 'static' => 0
      );
      $result = $db->insert($core->dTable, $data);
      $userdir = UPLOAD_PATH . return6charsha1($user_id);
      //copy simple images
      $this->full_copy(ASS_PATH . 'img' . DS . 'sample', $userdir, $user_id);
   }

   function full_copy($source, $target, $user_id) {
      global $db, $core;
      if ((!isset($_SESSION['fnames']))) {
         $_SESSION['fnames'] = array();
      }
      if (is_dir($source)) {
         $d = dir($source);
         while (FALSE !== ( $entry = $d->read() )) {
            if ($entry == '.' || $entry == '..') {
               continue;
            }
            $Entry = $source . '/' . $entry;
            
             if (strpos($entry, ".php") !== false || $entry[0] == '_') {
                   continue;
             }             
            
            if (is_dir($Entry)) {
               @mkdir($Entry, 755, true);
               $this->full_copy($Entry, $target . '/' . $entry, $user_id);
               continue;
            }
            
            if (is_file($Entry)):
               $info = pathinfo($Entry);
               $name = basename($Entry, '.jpg');

               if (isset($_SESSION['fnames'][$name])) {
                  $filekey = $_SESSION['fnames'][$name][0];
                  $newfilename = $_SESSION['fnames'][$name][1];
               } else {
                  $newfilename = $this->createFileName($entry);

                  $filekey = $this->getFileKeyFromName($newfilename, 0);

                  $_SESSION['fnames'][$name][0] = $filekey;
                  $_SESSION['fnames'][$name][1] = $newfilename;
               }

               $file = array(
                   $this->dpfi . 'user_id' => $user_id,
                   $this->dpfi . 'note' => NULL,
                   $this->dpfi . 'title' => $info['basename'],
                   $this->dpfi . 'name' => $name,
                   $this->dpfi . 'key' => $filekey,
                   $this->dpfi . 'extension' => $info['extension'],
                   $this->dpfi . 'folder' => 1,
                   $this->dpfi . 'mime_folder' => 2,
                   $this->dpfi . 'size' => filesize($Entry),
                   $this->dpfi . 'date' => "NOW()"
               );
               $_SESSION['ups'] = "auto";
               if (basename($source) == 'thumbnail') {
                  $this->insertfileDataDB($file, true);
               }
            endif;
            copy($Entry, $target . '/' . $newfilename);
         }

         $d->close();
      } else {
         copy($source, $target);
      }
   }

   function rmfolderR($path) {
      $path = rtrim($path, '/') . '/';
      $handle = opendir($path);
      while (false !== ($file = readdir($handle))) {
         if ($file != '.' and $file != '..') {
            $fullpath = $path . $file;
            if (is_dir($fullpath))
               $this->rmfolderR($fullpath);
            else{
               if($file != 'index.php')
                  unlink($fullpath);
            }
         }
      }
      closedir($handle);
      rmdir($path);
   }

}

?>
