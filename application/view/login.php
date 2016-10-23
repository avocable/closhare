<?php

/**
 * login
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: login.php UTF-8 , 23-Jun-2013 | 03:46:47 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
?>
<form id="login_form" method="POST" class="form-horizontal" style="text-align: center" novalidate>
   <fieldset>
      <?php if (post("loginme")): ?>
         <div class="control-group" style="margin: 0">
            <?php echo $user->uilogin; ?>
         </div>
      <?php endif; ?>
      <div class="control-group">
         <div class="input-prepend">
            <span class="add-on"><i class="icon-white icon-envelope"></i></span>
            <input data-validation-required-message="" data-validation-email-message="email is not valid!" name="login-email" id="login-email" type="email" class="input-xlarge" value="<?php echo isset($_POST['login-email']) ? $_POST['login-email'] : ''; ?>" placeholder="Email" />
         </div>
      </div>
      <div class="control-group">
         <div class="input-prepend">
            <span class="add-on"><i class="icon-white icon-lock"></i></span>
            <input data-validation-required-message="" data-validation-minlength-message="password is not ready!" minlength="<?php echo $core->register_password_min_length ?>" name="login-passw" id="login-passw" type="password" class="input-xlarge" placeholder="Password">
         </div>
      </div>
      <div class="control-group warning" style="margin: 0;">
         <p class="help-block pull-left" style="text-align: left;font-size: 88%"></p>
      </div>
      <div class="control-group">
         <label class="checkbox pull-left">
            <input type="checkbox" value="remember-me" name="remember-me" id="remember-me">
            Remember me
         </label>
         <button data-loading-text="Signing in..." class="btn button-loading btn-large pull-right" id="login-submit" type="submit">Sign in</button>
      </div>
      <input type="hidden" name="loginme" value="<?php echo $encr->encode(date("Ymd")); ?>" />
   </fieldset>
</form>
