<?php

/**
 * register
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: register.php UTF-8 , 23-Jun-2013 | 22:56:44 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
?>
<form id="register_form" action="/#page=register" method="POST" class="form-horizontal" style="text-align: center" novalidate>
   <fieldset>
      <?php if (post("registerme")): ?>
         <div class="control-group" style="margin: 0">
            <?php echo $user->uiregister; ?>
         </div>
      <?php endif; ?>        
      <div class="control-group">
         <input data-validation-ajax-ajax="" name="register-email" id="register-email" data-validation-required-message="" data-validation-email-message="" type="email" value="<?php echo isset($_POST['register-email']) ? $_POST['register-email'] : ''; ?>"  class="input-xlarge" placeholder="Email" />
      </div>
      <div class="control-group">
         <input name="register-fname" id="register-fname" minlength="3" data-validation-required-message="" type="text" class="input-xlarge" placeholder="Your Name" value="<?php echo isset($_POST['register-fname']) ? $_POST['register-fname'] : ''; ?>" />
      </div>       
      <div class="control-group">
         <input name="register-passw" id="register-passw" minlength="<?php echo $core->register_password_min_length ?>" data-validation-required-message="" type="password" class="input-xlarge" placeholder="Password" autocomplete="off" />
      </div>
      <div class="control-group">
         <input name="register-passws" data-validation-matches-match="register-passw" id="register-passws" data-validation-required-message="" data-validation-matches-message="Passwords do not match" type="password" class="input-xlarge" placeholder="Confirm password" autocomplete="off" />
      </div>
      <div class="control-group" style="margin-bottom: 5px">
         <div class="warning" style="text-align: center;"><p class="help-block warning"></p></div>
         <strong>You are not a robot? Prove. | </strong>
         <a href="javascript:;" onclick="$('.captcha_img').attr('src', 'captcha.png?'+Math.random()); return false" id="change-image-register">Get new one</a>
         <img src="captcha.png" class="captcha_img" />
      </div>
      <div class="control-group">
         <input type="text" name="captcha" id="captcha_input_register" class="input-large" minlength="4" data-validation-required-message=""  placeholder="Enter what you see above" style="margin: 0" />
      </div>        
      <div class="control-group">
         <label class="checkbox pull-left">
            <input type="checkbox" value="1" id="register-terms" name="register-terms" data-validation-required-message="">
            I agree to <a class="btn btn-mini" data-loading-text="Loading" href="javascript:;" data-action="content_terms-of-use" data-toggle="modal">Terms</a> of Use 
         </label>            
         <button data-loading-text="Procesing..." class="btn button-loading pull-right btn-large" id="register-submit" type="submit">Sign up</button>
      </div>
      <input type="hidden" name="registerme" value="<?php echo $encr->encode(date("Ymd")); ?>">
   </fieldset>
</form>