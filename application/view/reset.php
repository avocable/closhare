<?php

/**
 * reset
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: reset.php UTF-8 , 27-Jun-2013 | 00:27:41 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
$noCapt = false;
?>
<form id="reset_form" action="<?php echo (isset($_GET['reset']) && $user->checkUserToken($_GET['reset'])) ? ('/?reset='.$_GET['reset']) : '/#page=recover';?>" method="POST" class="form-horizontal" style="text-align: center" novalidate>
   <fieldset>
      <?php if (post("resetme")): ?>
         <div class="control-group" style="margin-bottom: 0">
            <?php echo $user->uireset; ?>
         </div>
      <?php endif; ?>          
      <legend>Recover login details.</legend> 
      <?php if (isset($_GET['reset'])) { ?>
         <?php if (!empty($_GET['reset']) && $user->checkUserToken($_GET['reset'])) { ?>
            <div class="control-group" style="margin-top: 0">
               <div class="input-prepend">
                  <span class="add-on"><i class="icon-white icon-lock"></i></span>        
                  <input name="reset-passw" id="reset-passw" minlength="<?php echo $core->register_password_min_length ?>" data-validation-required-message="" type="password" class="input-xlarge" placeholder="Enter your new password" autocomplete="off" /> 
               </div>
            </div>
            <div class="control-group">
               <div class="input-prepend">
                  <span class="add-on"><i class="icon-white icon-lock"></i></span>
                  <input name="reset-passws" data-validation-matches-match="reset-passw" id="reset-passws" data-validation-required-message="" data-validation-matches-message="Passwords do not match" type="password" class="input-xlarge" placeholder="Confirm password" autocomplete="off" />
               </div>
            </div>
      <input type="hidden" value="<?php echo $_GET['reset'];?>" name="reset" />
         <?php
         } else {
            //the requested reset hash is broken/unvalid
            $core->msgs = array("title" => "Problem with request", "message" => "###Something wrong with your request.
                ###We don't have enough information to continue this.###Please send a new reset request to your mail again.###Click <a href=\"" . $core->site_url . "/#page=recover\"><b>here</b></a> to continue.");
            print $content->alertMessage("error", false, false, true);
            $noCapt = true;
         }
         ?>
<?php } else { ?>

         <div class="control-group" style="margin-top: 0">
            <div class="input-prepend">
               <span class="add-on"><i class="icon-white icon-envelope"></i></span>
               <input data-validation-ajax-ajax="" name="reset-email" id="reset-email" data-validation-required-message="" data-validation-email-message="" type="email" class="input-xlarge" value="<?php echo isset($_POST['reset-email']) ? $_POST['reset-email'] : ''; ?>" placeholder="Email" placeholder="Enter your e-mail address" />

            </div>           
         </div>
<?php } ?>      
<?php if (!$noCapt) { ?>      
         <div class="control-group" style="margin-bottom: 5px">
            <div class="warning" style="text-align: center;"><p class="help-block warning"></p></div>
            <strong>You are not a robot? Prove. | </strong>
            <a href="javascript:;" onclick="$('.captcha_img').attr('src', 'captcha.png?'+Math.random()); return false" id="change-image-register">Get new one</a> 
            <img src="captcha.png" class="captcha_img" />
         </div>
         <div class="control-group">
            <input type="text" name="captcha" id="captcha_input_recover" class="input-large" minlength="4" data-validation-required-message=""  placeholder="Enter what you see above" style="margin: 0" />
         </div>  
         <div class="control-group" style="margin-top: 25px">
            <button data-loading-text="Procesing..." class="btn button-loading btn-large pull-right" id="reset-submit" type="submit">Send</button>
         </div>
         <input type="hidden" name="resetme" value="<?php echo $encr->encode(date("Ymd")); ?>" />
<?php } ?>
   </fieldset>
</form>