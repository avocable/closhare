<?php 

/**
 * user
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: user.class.php UTF-8 , 22.AGU.2011 | 04:24:37 nwdo ε
 */ ?>
<?php
namespace Nedo;
use Nedo\Content;
use Nedo\FileHandler;
use Nedo\Paginator;

if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');

 class Users {

   private $uTable = "users";
   public $logged_in = null;
   public $userid = 0;
   public $user_email;
   public $user_name; //user name and surname
   public $user_rights = array();
   public $user_role;
   public $user_dir;
   private $lastlogin = "NOW()";
   public $uilogin = null;
   public $uiregister = null;
   public $loggedout = null;
   private $dpfu = "user_";

   /**
    * Users::__construct()
    * 
    * @return none
    */
   function __construct() {
      include_once(LIB_PATH . "class.content.php");
      include_once(LIB_PATH . "file.handler" . DS . "class.filehandler.php");
      $this->startSession();
      /**
       * Handle user get-post-cookie requests!
       */
      if (post("loginme") && post("login-email")):
         $this->uilogin = $this->login();
      endif;
      if (post("registerme") && post("register-email")):
         $this->uiregister = $this->register();
      endif;
      if (post("resetme") && post("reset-email")):
         $this->uireset = $this->reset();
      endif;
      if (post("resetme") && post("reset-passw")):
         $this->uireset = $this->resetProcess();
      endif;
      if (get("logout")):
         $this->loggedout = $this->logout();
         if ($this->loggedout){
            if(get("return_to")){
               redirectPage_to($_GET['return_to']);
            }else{
               redirectPage_to("/#page=login");
            }
         }
      endif;
      
   }

   /**
    * startSession()
    * 
    * @return
    */
   private function startSession() {
      session_start();
      $this->logged_in = $this->checkLogin();

      if (!$this->logged_in) {
         $this->userid = $_SESSION['user_id'] = 0;
         $this->user_role = 0;
         
         $this->checkCookie();
      }
      return true;
   }
   
   
   private function checkCookie(){
      global $encr;
      
      if(!$this->logged_in && !post("loginme") && !post("registerme") && !post("resetme") && !isset($_GET["logout"])){
         $auth = (isset($_COOKIE['CLO_UINF0']) && !empty($_COOKIE['CLO_UINF0'])) ? 
         array(
             "email" => $encr->decode($encr->decode($_COOKIE['CLO_UINF0'])),
             "pass" => $encr->decode($encr->decode($_COOKIE['CLO_UINF1']))
             ) : false;
      if($auth){
         $this->login($auth['email'], $auth['pass'], true);
         $this->startSession();
         return true;
      }
      }
      return false;
   }
   /**
    * Users::checkLogin()
    * 
    * @return
    */
   private function checkLogin() {
      if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != 0) {

         $row = $this->getUserInfo($_SESSION['user_id']);
         $this->userid = $_SESSION['user_id'];
         $this->user_email = $row['user_email'];
         $this->user_name = $row['user_name'];
         $this->user_role = $row['user_role'];
         $this->user_limit = $row['user_limit'];
         $this->user_dir = return6charsha1($row['user_id']);
         $this->user_created = $_SESSION['user_created'] = date("d F, Y / H:s:i", strtotime($row['user_created']));
         $this->last_logged_on = $_SESSION['user_lastloginTime'] = date("d F, Y / H:s:i", strtotime($row['user_lastloginTime']));
         $this->last_logged_from = $_SESSION['user_lastloginIP'] = $row['user_lastloginIP'];
         $this->user_dir = return6charsha1($row['user_id']);
         return true;
      } else {
         return false;
      }
   }

   /**
    * Users::login()
    * 
    * @param mixed $user_name
    * @param mixed $pass
    * @return
    */
   public function login($email = false, $pass = false, $cookie = false) {
      global $db, $core, $content, $encr;
      $core->msgs = array();
      
      
      $user_email = $email ? $email : $_POST["login-email"];
      $password = $pass ? $pass : $_POST["login-passw"];
      
      if ((isset($_POST["loginme"]) && $encr->decode($_POST["loginme"]) == date("Ymd")) || ($email && $pass)) {
         if ($user_email != "" && $password != "") {
            $check = $this->checkUserStatus($user_email, $password);

            switch ($check["status"]) {
               case 0:
                  $core->msgs = array("title" => "Error! ", "message" => "Incorrect email/password combination!");
                  break;
               case 11:
                  $core->msgs = array();
                  break;
               case 22:
                  $core->msgs = array("title" => "Warning! ", "message" => "Your account has been suspended by the system administrator.");
                  break;
            }
         }
         if ($check['status'] == 11) {
            $row = $this->getUserInfo($check['user_id']);
            $this->userid = $_SESSION['user_id'] = $check['user_id'];
            $this->user_name = $_SESSION['user_name'] = $row['user_name'];
            $this->user_role = $_SESSION['user_role'] = $row['user_role'];
            $this->user_limit = $_SESSION['user_limit'] = ($row['user_limit']);
            $this->user_dir = $_SESSION['user_dir'] = return6charsha1($row['user_id']);
            $this->user_created = $_SESSION['user_created'] = date("d F, Y / H:s:i", strtotime($row['user_created']));
            $this->last_logged_on = $_SESSION['user_lastloginTime'] = date("d F, Y / H:s:i", strtotime($row['user_lastloginTime']));
            $this->last_logged_from = $_SESSION['user_lastloginIP'] = $row['user_lastloginIP'];
            $this->user_dir = return6charsha1($row['user_id']);

            $data = array(
                'user_lastloginTime' => $this->lastlogin,
                'user_lastloginIp' => sanitise($_SERVER['REMOTE_ADDR'])
            );
            $db->update($core->uTable, $data, "user_id='" . (int) $check['user_id'] . "'");
            
            //remember him/her
            if(isset($_POST['remember-me']) && !empty($_POST['remember-me'])){
               
               setcookie("CLO_UINF0", $encr->encode($encr->encode($user_email)), time()+3600*24*7);//encode $user_email twice
               setcookie("CLO_UINF1", $encr->encode($encr->encode($password)), time()+3600*24*7);//encode $password twice
            }
               
            $return = true;
         }
      } else {
         $core->msgs = array("title" => false, "message" => "Something wrong with your session!
                ###refresh this page and try again.###always use usual ways.");
      }

      if (isset($check['status']))
         unset($check['status']);

      if (!$email || ($cookie && $email))
         return (empty($core->msgs) ? redirectPage_to("/#page=list") : Content::alertMessage("error", true, true, true));
      else
         return true;
   }

   /**
    * Users::logout()
    * 
    * @return
    */
   public function logout() {
      unset($_SESSION['user_id']);
      unset($_SESSION['user_dir']);
      unset($_SESSION['user_name']);
      unset($_SESSION['user_role']);
      unset($_SESSION['email']);
      if (isset($_COOKIE['CLO_UINF0']) || isset($_COOKIE['CLO_UINF1'])) {
         unset($_COOKIE['CLO_UINF0']);
         unset($_COOKIE['CLO_UINF1']);
         setcookie('CLO_UINF0', null, -1, '/');
         setcookie('CLO_UINF1', null, -1, '/');
         } 
      
      session_destroy();
      session_regenerate_id();

      $this->logged_in = false;
      $this->user_id = 0;
      $this->user_role = 0;

      return true;
   }

   /**
    * Users::getUserInfo()
    * 
    * @param mixed $user_id
    * @return 
    */
   public function getUserInfo($user_id) {

      global $db, $core;
      $sql = "SELECT * FROM " . $core->uTable . ""
              . "\n WHERE"
              . (is_numeric($user_id) ? "\n user_id = '" . (int) $user_id . "'" :
                      "\n user_email = '" . $user_id . "'");
      $row = $db->first($sql);
      if (!$user_id)
         return false;

      return ($row) ? $row : 0;
   }

   /**
    * Users::checkUserStatus()
    * 
    * @param email $user_email
    * @param password $password
    * @return
    */
   public function checkUserStatus($user_email, $password) {
      global $db, $core, $encr;

      $user_email = sanitise($user_email);
      $user_email = $db->escape($user_email);
      $password = sanitise($password);
      $res = array();

      $sql = "SELECT user_id, user_password, user_status FROM " . $core->uTable
              . "\n WHERE user_email = '" . $user_email . "'";
      $result = $db->query($sql);

      if ($db->numrows($result) > 0) {
         $row = $db->fetch($result);
         $enc_password = $encr->encode($password);

         if ($enc_password != $row['user_password']) {
            $res["status"] = 0;
         }
         //check for user active or inactive
         switch ($row['user_status']) {
            case "active":
               if ($enc_password == $row['user_password']) {
                  $res["status"] = 11;
                  $res["user_id"] = $row['user_id'];
               } else {
                  $res["status"] = 0;
               }

               break;
            case "inactive":
               $res["status"] = 22;
               break;
         }
      } else {
         $res["status"] = 0;
      }
      return $res;
   }

   /**
    * isAdmin()
    * 
    * @return
    */
   public function isAdmin() {
      if($this->logged_in)
          return($this->user_role == "admin");
      else return false;
   }

   /**
    * this::getUsers()
    * 
    * @param bool $from <>
    * @return
    */
   public function getUsers($from = false, $p, $ipp, $currpage) {
      global $db, $pager, $core;
      $pager = new \Nedo\Paginator($p, $currpage);

      $counter = countDataDB($core->uTable);
      $pager->items_total = $counter;
      $pager->default_ipp = $ipp;
      $pager->paginate();

      if ($counter == 0) {
         $pager->limit = null;
      }

      if (isset($_GET['sort'])) {
         list($sort, $order) = explode("-", $_GET['sort']);
         $sort = sanitise($sort);
         $order = sanitise($order);
         if (in_array($sort, array("user_name", "user_email", "user_role", "user_email", "user_created"))) {
            $ord = ($order == 'DESC') ? " DESC" : " ASC";
            $sorting = " u." . $sort . $ord;
         } else {
            $sorting = " u.user_created DESC";
         }
      } else {
         $sorting = " u.user_id ASC";
      }

      $clause = (isset($clause)) ? $clause : null;

      if (isset($_POST['fromdate']) && $_POST['fromdate'] <> "" || isset($from) && $from != '') {
         $enddate = date("Y-m-d");
         $fromdate = (empty($from)) ? $_POST['fromdate'] : $from;
         if (isset($_POST['enddate']) && $_POST['enddate'] <> "") {
            $enddate = $_POST['enddate'];
         }
         $clause .= " WHERE u.user_created BETWEEN '" . trim($fromdate) . "' AND '" . trim($enddate) . " 23:59:59'";
      }

      $sql = "SELECT "
              . "\n u.user_id,"
              . "\n u.user_name,"
              . "\n u.user_email,"
              . "\n u.user_limit,"
              . "\n u.user_role,"
              . "\n u.user_lastloginTime,"
              . "\n u.user_created,"
              . "\n u.user_lastloginIP,"
              . "\n u.user_status"
              . "\n FROM " . $core->uTable . " u"
              . "\n " . $clause
              . "\n ORDER BY " . $sorting . $pager->limit;
      $row = $db->fetch_all($sql);

      return ($row) ? $row : 0;
   }

   /**
    * 
    * @param type $data
    * @return
    */
   function arrangeUserTableData($data) {
      global $core, $fhand;
      $temp = array();
      $temp['th'] = $temp['body'] = null;
      $i = 0;
      foreach ($data[0] as $key => $value) {
         $i++;
         $tte = @$temp['th'][$key];

         if (@in_array($key, $tte))
            continue;

         $temp['th'][$key] = $key;
      }
      $temp['th']['user_actions'] = 'user_actions';
      $temp['th'] = $core->push_before('user_status', $temp['th'], 'disk_usage', 'disk_usage');

      foreach ($data as $key => $value) {
         $temp['body'][$key] = $value;

         if (!isset($temp['body'][$key]['disk_usage']) && isset($temp['body'][$key]['user_status']))
            $temp['body'][$key] = $core->push_before('user_status', $temp['body'][$key], 'disk_usage', $fhand->calcUserUsagePercentage($value['user_id'], $value['user_limit'], false));

         if (!isset($temp['body'][$key]['user_actions']))
            $temp['body'][$key]['user_actions'] = '<span class="foacts"><button class="btn btn-small ' . ($value['user_id'] == 1 ? 'btn-inverse disabled' : 'btn-danger') . '" data-id="' . $value['user_id'] . '" data-name="' . $value['user_name'] . '" data-action="uf_delete"><i class="icon-trash"></i></button></span>';
      }
      //print_r($temp);
      return $temp;
   }

   public function userStatusSignBtn($status) {

      switch ($status) {

         case "active":
            $display = '<button class="btn btn-small btn-success active" title="Click to deactivate user."><i class="icon-check"></i></button>';
            break;
         case "inactive":
            $display = '<button class="btn btn-small btn-warning i" title="Click to activate user."><i class="icon-check-empty"></i></button>';
            break;
         case "admin":
            $display = '<button class="btn btn-small btn-inverse active disabled"><i class="icon-check"></i></button>';
            break;
      }

      return $display;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $user_id
    * @param type $status
    */
   public function toggleStatus($user_id, $status) {
      global $db, $core;
      if ($this->isAdmin()):
         $data = array("user_status" => $status);
         $updated = $db->update($core->uTable, $data, "{$this->dpfu}id=" . $user_id);

         if ($db->affected() && $updated) {
            $core->jsonE["result"] = 1;
            $core->jsonE["message"] = '<strong>User status changed successfully.</strong>';
         } else {
            $core->jsonE["result"] = 0;
            $core->jsonE['message'] = "Sorry! I encountered an error. Please try again.";
         }
      else:
         redirectPage_to(CLO_URL);
      endif;
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $user_id
    */
   public function deleteUser($user_id) {
      global $db, $core, $fhand;
      if ($this->isAdmin()):
         $deleted = $db->delete($core->uTable, $this->dpfu . 'id = ' . $user_id) || 0;

         if ($db->affected() && $deleted) {
            $userdir = UPLOAD_PATH . return6charsha1($user_id);

            //delete user files and folders from DB.
            $fhand->deleteUserAllFoldersDB($user_id);
            $fhand->deleteUserAllFilesDB($user_id);
            //delete user physical files and personal directory.
            $fhand->rmfolderR($userdir);

            //thats all ok return true.         
            $core->jsonE["result"] = 1;
            $core->jsonE["message"] = '<strong>User deleted successfully.</strong>';
         } else {
            $core->jsonE["result"] = 0;
            $core->jsonE['message'] = "Sorry! I encountered an error. Please try again.";
         }
      else:
         redirectPage_to(CLO_URL);
      endif;
   }

   /**
    * Users::updateProfile()
    * 
    * @return
    */
   public function updateProfile() {
      global $db, $core, $encr;

      if (empty($_POST['user_name']))
         $core->msgs['user_name'] = 'Please enter your name.';

      if (empty($core->msgs)) {

         $data = array(
             'user_name' => sanitise($_POST['user_name']),
         );

         if ($_POST['user_password'] != "") {
            $data['user_password'] = $encr->encode($_POST['user_password']);
         }

         $updated = $db->update($core->uTable, $data, "user_id='" . (int) $this->userid . "'");

         if ($db->affected() && $updated) {
            $core->jsonE["result"] = 1;
            $core->jsonE["message"] = '<strong>Your profile updated successfully.</strong>';
         } else {
            $core->jsonE["result"] = 0;
            $core->jsonE['message'] = "Sorry! I couldn't find any changes to edit.";
         }
      } else {
         $core->jsonE["result"] = 0;
         $core->jsonE['message'] = "Sorry! I encountered an error. Please try again.";
      }
      return $core->jsonE;
   }

   /**
    * User::register()
    * 
    * @return
    */
   public function register() {
      global $db, $core, $encr;
      if ($encr->decode($_POST["registerme"]) == date("Ymd")) {
         $welcome = false;
         $pass = sanitise($_POST['register-passw']);

         if (empty($_POST['register-terms']))
            $core->msgs = array("title" => "Error! ", "message" => "Please check the user agreement!");

         if (empty($_POST['register-email']))
            $core->msgs = array("title" => "Error! ", "message" => "Please Enter Valid Email Address!");
         if (empty($_POST['register-fname']))
            $core->msgs = array("title" => "Error! ", "message" => "Please Enter Your Name!");
         if (empty($_POST['register-passw']))
            $core->msgs = array("title" => "Error! ", "message" => "Please Specify a password!");
         if ($_POST['register-passw'] != $_POST['register-passws'])
            $core->msgs = array("title" => "Error! ", "message" => "Passwords does not match!");

         if (empty($_POST['captcha']))
            $core->msgs = array("title" => "Error! ", "message" => "Please enter the security Code!");
         if ($_POST['captcha'] != $_SESSION['uscaptcha'])
            $core->msgs = array("title" => "Error! ", "message" => "Please check the security Code!");

         if (empty($core->msgs)) {
            $data = array(
                'user_name' => sanitise($_POST['register-fname']),
                'user_password' => $encr->encode($_POST['register-passw']),
                'user_email' => sanitise($_POST['register-email']),
                'user_limit' => $core->upload_user_default_disk_limit,
                'user_status' => 'active',
                'user_created' => "NOW()"
            );
            $useridf = $db->insert($core->uTable, $data);
            if ($db->affected()) {

               //user was created so touch some additional things...
               //create user physical folder and put inside an index.php file
               $uipath = UPLOAD_PATH . return6charsha1($useridf);
               $uidir = mkdir($uipath . '/thumbnail/', 0755, true);
               $uidir = mkdir($uipath . '/view/', 0755, true);
               
               if ($uidir) {

                  createIndexFile(2, $uipath);
                  createIndexFile(3, $uipath . '/thumbnail/');
                  createIndexFile(3, $uipath . '/view/');
                  $fhand = new \Nedo\FileHandler();

                  $fhand->createSampleData($useridf);
               }
               $this->login($data['user_email'], $pass);

               $welcome = $core->sendSingleMail('welcome', $data['user_email'], $data['user_name'], $pass);

               $core->msgs = array("message" => '
                             <script>
                             // <![CDATA[
                             $(function() {
                             $("#form-login").blockit({message : "Your account has been created!<br>Please wait while being redirected in <span id=\"countrd\"></span> or click <a href=\"' . $core->site_url . '\" >here</a>"}); $("#countrd").countdown(6, "s", function() {  window.location.href = "/";});
                             });
                             // ]]>
                             </script>');
            }
         }
      } else {
         $core->msgs = array("title" => false, "message" => "Something wrong with your session!
                ###refresh this page and try again.###always use usual ways.");
      }
      return (!$welcome ? Content::alertMessage() : Content::returnMessage());
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @return type
    */
   public function reset() {
      if(!$this->logged_in){
      global $db, $core, $encr;
      if ($encr->decode($_POST["resetme"]) == date("Ymd")) {

         if (empty($_POST['captcha']))
            $core->msgs = array("title" => "Error! ", "message" => "Please enter the security Code!");
         if ($_POST['captcha'] != $_SESSION['uscaptcha'])
            $core->msgs = array("title" => "Error! ", "message" => "Please check the security Code!");

         if (empty($core->msgs)) {

            $mail = sanitise($_POST['reset-email']);

            $row = $this->getUserInfo($_POST['reset-email']);
            $data = array(
                "user_token" => $encr->encode($mail.'!'. time())
            );
            if ($row) {

               $recover = $core->sendSingleMail('reset', $row['user_email'], $row['user_name'], false, $data['user_token']);

               if ($recover) {
                  //set user_token
                  $db->update($core->uTable, $data, "user_id='" . (int) $row['user_id'] . "'");

                  $core->msgs = array("title" => false, "message" => "<strong>We have sent an e-mail to you, check your mail please.</strong>");
               } else {
                  $core->msgs = array("title" => false, "message" => "Sorry! I encountered an error. Please try again.");
               }
            } else {
               $core->msgs = array("title" => false, "message" => "Sorry! I encountered an error. Please check your e-mail and try again.");
            }
         }
      } else {
         $core->msgs = array("title" => false, "message" => "Something wrong with your session!
                ###refresh this page and try again.###always use usual ways.");
      }
      return Content::alertMessage();
      }
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @global type $encr
    * @return type
    */
   public function resetProcess() {
      if(!$this->logged_in){
      global $db, $core, $encr;
      $login = false;
      if ($encr->decode($_POST["resetme"]) == date("Ymd")) {

         if (empty($_POST['reset-passw']))
            $core->msgs = array("title" => "Error! ", "message" => "Please enter your new password!");
         
         if ($_POST['reset-passw'] != $_POST['reset-passws'])
            $core->msgs = array("title" => "Error! ", "message" => "Please confirm your password!");
         
         if (empty($_POST['captcha']))
            $core->msgs = array("title" => "Error! ", "message" => "Please enter the security Code!");
         if ($_POST['captcha'] != $_SESSION['uscaptcha'])
            $core->msgs = array("title" => "Error! ", "message" => "Please check the security Code!");

         if (empty($core->msgs)) {
            
            $uin = $encr->decode($_POST['reset']);
            
            $uin = explode("!", $uin);
            
            $mail = sanitise($uin[0]);
            $newpass = $_POST['reset-passw'];
            
            $newPassHash = $encr->encode($newpass);

            $row = $this->getUserInfo($mail);
            
            $data = array(
                "user_password" => $newPassHash
            );
            if ($row) {
               
               //set user_token
               $recover = $db->update($core->uTable, $data, "user_id='" . (int) $row['user_id'] . "'") || 1;
               
               if ($recover) {
                  
                  $core->sendSingleMail('resetResult', $row['user_email'], $row['user_name'], $newpass, false);

                  $login = $this->login($mail, $newpass);
                  $core->msgs = '';
                  
                  $core->msgs = array("message" => '
                                <script>
                                // <![CDATA[
                                $(function() {
                                $("#reset_form").closest(".well:first").blockit({message : "You have set your new password successfuly you will be redirected in <span id=\"countrdr\"></span> or click <a href=\"' . $core->site_url . '\" ><b>here</b></a>."}); $("#countrdr").countdown(6, "s", function() { window.location.href = "/";});
                                });
                                // ]]>
                                </script>');                  
                  
               } else {
                  $core->msgs = array("title" => false, "message" => "Sorry! I encountered an error. Please try again.");
               }
            } else {
               $core->msgs = array("title" => false, "message" => "Sorry! I encountered an error. Please check your e-mail twice and try again.");
            }
         }
      } else {
         $core->msgs = array("title" => false, "message" => "Something wrong with your session!
                ###refresh this page and try again.###always use usual ways.");
      }
      }
      
      return (!$login ? Content::alertMessage() : Content::returnMessage());
   }

   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $token
    * @return boolean
    */
   public function checkUserToken($token) {
      global $db, $core;
      $sql = $db->query("SELECT user_email,user_id"
              . "\n FROM " . $core->uTable
              . "\n WHERE user_token = '" . sanitise($token) . "'"
              . "\n LIMIT 1");
      if ($db->numrows($sql) > 0) {
         return $db->fetch($sql);
      } else {
         return false;
      }
   }

   /**
    * 
    * @global type $core
    * @global type $fhand
    * @return type
    * feautered.
    */
   
   public function getUserSpaceLimit($notformat=false){
      global $core, $fhand;
      
      $globalLimit = $core->upload_user_default_disk_limit;
      
      $adminLimit = $fhand->formatBytes(0,2,true);
      
      if($this->isAdmin()){
         return $notformat;
      }
   }   
   /**
    * 
    * @global type $core
    * @global type $fhand
    * @return type
    * feautered.
    */
   
   public function getUserFileUploadSizeLimit($notformat=false){
      global $core, $fhand;
      return $notformat ? $core->upload_max_file_size_limit : ($fhand->formatBytes($core->upload_max_file_size_limit, 2, true));
   }
   
   /**
    * 
    * @global type $core
    * @return type
    * feautered.
    */
   public function getUserFileUploadItems(){
      global $core;
      $limit = sanitise($core->upload_user_default_up_items);
      return $limit == 0 ? 'N/A' : $limit;
   }  
   
   
   public function getUserAllowedFileTypes($type=false){
       global $core, $fhand;
       $types = $fhand->getAllowedTypesRegex($core->upload_allowed_file_types, $type);
       return mb_strtolower($types) == 'all' ? false : $types;
    }   
   /**
    * 
    * @global type $db
    * @global type $core
    * @param type $user_email
    * @return boolean
    */
   public function emailExists($user_email) {
      global $db, $core;

      $sql = $db->query("SELECT user_email,user_id"
              . "\n FROM " . $core->uTable
              . "\n WHERE user_email = '" . sanitise($user_email) . "'"
              . "\n LIMIT 1");
      if ($db->numrows($sql) == 1) {
         $core->jsonE["message"] = "Entered e-mail address is already in use.";
         return false;
      } else {
         return true;
      }
   }

}
//user class ends
?>