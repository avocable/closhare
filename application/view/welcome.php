<?php

/**
 * login
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: login.php UTF-8 , 22-Jun-2013 | 14:10:36 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
?>
<div class="row-fluid">
   <div id="form-login" class="span12">
      <div class="tabs-top tabbable" id="page">
         <ul class="nav nav-pills tabnav" id="user-post-box-lrr" style="margin-bottom: 5px;">
            <?php if (isset($_GET['reset'])): ?>
               <li class="active"><a href="#reset" data-toggle="tab" class="pg">Password Reset</a></li>
            <?php else: ?>
               <li class="active"><a href="#login" data-toggle="tab" data-callback="" class="pg">Login</a></li>
               <?php if ($core->register_allowed && ($core->register_user_limit == 0 || $core->register_user_limit > countDataDB($core->uTable))): ?><li><a href="#register" data-toggle="tab" data-callback="" class="pg">Register</a></li><?php endif; ?>
               <li class="pull-right"><a href="#recover" data-toggle="tab" data-callback="" class="pg">Forgot Password?</a></li>
            <?php endif; ?>
         </ul>      
         <div class="tab-content well" style="padding:20px 19px 0 25px;">
            <?php if (isset($_GET['reset'])): ?>
               <div class="tab-pane active" id="reset"><?php include_once VIEW_PATH . 'reset.php'; ?></div>
            <?php else: ?>
               <div class="tab-pane active" id="login"><?php include_once VIEW_PATH . 'login.php'; ?></div>
               <?php if ($core->register_allowed && ($core->register_user_limit == 0 || $core->register_user_limit > countDataDB($core->uTable))): ?><div class="tab-pane" id="register"><?php include_once VIEW_PATH . 'register.php'; ?></div><?php endif; ?>
               <div class="tab-pane" id="recover"><?php include_once VIEW_PATH . 'reset.php'; ?></div>
            <?php endif; ?>
         </div>     
      </div>
   </div>
</div>

