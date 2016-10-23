<?php
/**
 * setup
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: setup.php UTF-8 , 11-Oct-2013 | 01:07:15 nwdo ε
 */
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
ob_start();

$APP_PATH = str_replace("setup" . DIRECTORY_SEPARATOR . "run.php", "", realpath(__FILE__));
//print $APP_PATH;
$configFile = $APP_PATH . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "config.ini.php";
if (file_exists($configFile)) {
    header("Location: /");
}
include_once 'functions.php';
?>
<?php
$error = false;
$result = null;
$step = isset($_GET['step']) ? $_GET['step'] : false;

$DB_HOST = isset($_SESSION['db_server']) ? $_SESSION['db_server'] : null;
$DB_NAME = isset($_SESSION['db_name']) ? $_SESSION['db_name'] : null;
$DB_USER = isset($_SESSION['db_user']) ? $_SESSION['db_user'] : null;
$DB_PASS = isset($_SESSION['db_pass']) ? $_SESSION['db_pass'] : null;
$DB_PREFIX = isset($_SESSION['db_prefix']) ? $_SESSION['db_prefix'] : null;

if (isset($_POST['db_server']) && isset($_POST['db_name']) && isset($_POST['db_user']) && isset($_POST['db_pass'])) {
    require_once($APP_PATH . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "class.mysql.php");
    $DB_HOST = $_SESSION['db_server'] = $_POST['db_server'];
    $DB_NAME = $_SESSION['db_name'] = $_POST['db_name'];
    $DB_USER = $_SESSION['db_user'] = $_POST['db_user'];
    $DB_PASS = $_SESSION['db_pass'] = $_POST['db_pass'];
    $DB_PREFIX = $_SESSION['db_prefix'] = $_POST['db_prefix'];

    $db = new \Nedo\Database($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $error = $db->checkDBConnection();
}
//check prefix for trailing underscore (_)
if (substr($DB_PREFIX, -1) != '_') {
    $DB_PREFIX = $DB_PREFIX . '_';
}


if (isset($_POST['start_installation']) && isset($_GET['install'])) {
    $clearDB = clearDB($DB_NAME);

    $execute = execute_SQL();

    if ($execute != true) {
        $error = $execute;
    }
}

if (isset($_POST['finish'])) {

    $config = makeConfig();
    $error = ((!is_array($config) && $config == true) ? false : $config);

    if (!$error && !is_array($error)) {
        unset($_SESSION['db_server']);
        unset($_SESSION['db_name']);
        unset($_SESSION['db_user']);
        unset($_SESSION['db_pass']);
        unset($_SESSION['db_prefix']);
    }
}

$formAddr = (
        (( (!$step || $step == 1) || ($step == 2 && $error) ) ? '/?setup&amp;step=2' : (($step == 2) ? '/?setup&amp;step=3&amp;install' : (($step == 3) ? '/?setup&amp;step=4&amp;finish' : '/') ) )
        );
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Welcome | Closhare Easy Installation</title>
        <link href="assets/css/skin/bootstrap-default.css" rel="stylesheet">
        <link href="assets/css/font-awesome.css" rel="stylesheet">
        <!--[if IE 7]>
        <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css">
        <![endif]-->
        <style type="text/css">
            
            .form-horizontal .control-group{
                margin-bottom: 7px;
            }
            legend{
                color: #666;
            }
            .label{
                width: 150px;
                margin-right: 0;
                text-align: right;
                margin-top: 5px;
                font-size: 12px;
                padding-top: 8px;
                padding-bottom: 8px;
            }
            .label:after{
                position: relative;
                content: ':';
                margin: 0 3px;
            }
            p.help-block, small.help-block{
                text-align: right;
                margin-right: 20px;
                font-size: 11px;
            }
            .bief{
                color: #CCC;
            }
        </style>
        <script type="text/javascript" src="assets/js/jquery/jquery.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="span6 well" style="margin: 20px auto !important; float: none;">
                    <form class="form-horizontal" action="<?php echo $formAddr; ?>" method="POST">
                        <fieldset>

                            <!-- Closhare Installation Form Description -->
                            <legend><b style="color: #CCC;">Closhare</b> Easy Installation</legend>


<?php
if (!$step || $step == 1 || $error):
    ?>

                                <div class="alert <?php echo ($error) ? 'alert-error' : 'alert-info' ?>">
    <?php if (!$error): ?>
                                        <strong>Hello</strong>, Please give me some information about Database.
                                    <?php else: ?>
                                        <?php echo $error; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="db_server">Database Server</label>

                                    <input id="db_server" name="db_server" placeholder="localhost" class="input-xlarge db" required type="text" value="<?php print($DB_HOST); ?>">
                                    <p class="help-block fltrt">Usually "localhost" is okay.</p>
                                </div>

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="db_name">Database Name</label>

                                    <input id="db_name" name="db_name" placeholder="DB_NAME" class="input-xlarge db" required type="text" value="<?php print($DB_NAME); ?>">
                                    <p class="help-block">Enter your DB name.</p>
                                </div>

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="db_user">Database User</label>

                                    <input id="db_user" name="db_user" placeholder="DB_USER" class="input-xlarge db" required type="text" value="<?php print($DB_USER); ?>">
                                    <p class="help-block">Enter your DB Username</p>
                                </div>

                                <!-- Password input-->
                                <div class="control-group">
                                    <label class="label" for="db_pass">Database Password</label>

                                    <input id="db_pass" name="db_pass" placeholder="DB_USER_PASSWORD" class="input-xlarge db" required type="text" value="<?php print($DB_PASS); ?>">
                                    <p class="help-block">Enter your DB user password.</p>
                                </div>

                                <!-- Password input-->
                                <div class="control-group">
                                    <label class="label" for="db_prefix">Database Prefix</label>

                                    <input id="db_prefix" name="db_prefix" placeholder="cl_" value="cl_" class="input-xlarge" type="text">
                                    <p class="help-block">Enter a prefix for your DB columns. (Optional)</p>
                                </div>

                                <!-- Button -->
                                <div class="control-group">
                                    <label class="label" for="run"></label>
                                    <div class="controls">
                                        <button id="next" disabled="disabled" name="next" class="btn btn-primary pull-right">Next</button>
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    jQuery(function() {
                                        jQuery("input.db").on("keyup", function() {
                                            jQuery("input.db").each(function() {

                                                if ($(this).val().length > 0) {
                                                    $("body").data("nextOK", 1);
                                                } else {
                                                    $("body").data("nextOK", 0);
                                                    $("button#next").attr("disabled", true);
                                                    return false;
                                                }
                                            });
                                            if ($("body").data("nextOK") == 1)
                                                $("button#next").attr("disabled", false);
                                        });

                                        jQuery("input.db").trigger("keyup");

                                    });
                                </script>                     


<?php endif; ?>                     
                            <?php if ($step == 2 && !$error): 
                                if(empty($DB_HOST)){
                                    header("Location: /");
                                }
                                ?>
                                <div class="" style="position: relative;">
                                    <div class="icon-check-sign" style="position: absolute; right: 0; top: 30px; font-size: 150px"></div>
                                    <h3>That's OK!</h3>
                                    <p>The configuration you have just set up:</p>
                                    <ul>
                                        <li><span class="help-block">Database Server: <b><?php echo $DB_HOST; ?></b></span></li>
                                        <li><span class="help-block">Database Name: <b><?php echo $DB_NAME; ?></b></span></li>
                                        <li><span class="help-block">Database User: <b><?php echo $DB_USER; ?></b></span></li>
                                        <li><span class="help-block">Database Password: <b><?php echo $DB_PASS; ?></b></span></li>
                                        <li><span class="help-block">Database Prefix: <b><?php echo $_POST['db_prefix']; ?></b></span></li>
                                        <input type="hidden" name="start_installation" value="1" />
                                    </ul>
                                    <hr>
                                    <h3>Now it is almost...</h3>
                                    <p>We couldn't find any issues to get it done but<br>
                                        If you think your configuration has some mistakes, please <a href="/?setup"> Go Back</a> to edit.</p>
                                    <h3>Else</h3>
                                </div>  

                                <!-- Button -->
                                <div class="control-group">
                                    <label class="label" for="run"></label>
                                    <div class="controls">
                                        <span class="help-block pull-right" style="margin-right: 20px"> 
                                            <button id="next" name="next" class="btn btn-primary">Start Installation</button></span>
                                    </div>
                                </div>
                                <div class="" style="position: relative;">
                                    <p><strong>Notes: </strong></p>

                                    <small class="help-block">**A new installation will destroy all data on database '<?php echo $DB_NAME; ?>'.</small>
                                    <small class="help-block">*You will set additional config in next step.</small>
                                </div>
<?php endif; ?>
                            <?php if ($step == 3 && !$error): 
                              if(empty($DB_HOST)){
                                    header("Location: /");
                                }
                                ?>

                                <div class="" style="position: relative;">
                                    <div class="icon-cogs bief" style="position: absolute; right: 0; top: 40px; font-size: 75px"></div>

                                    <h3>Thats Cool! Let's volume up...</h3>
                                    <p>Now please set general configurations.</p>
                                    <hr>
                                </div>  

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="site_name">Site Name</label>

                                    <input id="site_name" name="site_name" placeholder="Closhare" class="input-xlarge" type="text" value="<?php echo (isset($_POST['site_name']) ? $_POST['site_name'] : ''); ?>" required>
                                    <small class="help-block">Give the best name for your share-land.</small>
                                </div>

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="site_desc">Site Slogan</label>

                                    <input id="site_desc" name="site_desc" placeholder="The coolest way to collect &amp; share files on the internet!" class="input-xlarge" type="text" value="<?php echo (isset($_POST['site_desc']) ? $_POST['site_desc'] : ''); ?>" required>
                                    <small class="help-block">Make your site more understandable.</small>
                                </div>

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="mete_desc">Meta Description</label>

                                    <input id="mete_desc" name="mete_desc" placeholder="Upload &amp; Store &amp; Share files online easily..." class="input-xlarge" type="text" value="<?php echo (isset($_POST['mete_desc']) ? $_POST['mete_desc'] : ''); ?>" required>
                                    <small class="help-block">Best explanation for your site which will be on search engines.</small>
                                </div>

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="mete_desc">Screet Key</label>

                                    <input id="screet_key" name="screet_key" placeholder="Duffy87" class="input-xlarge" type="text" value="<?php echo (isset($_POST['screet_key']) ? $_POST['screet_key'] : ''); ?>">
                                    <small class="help-block">Define a screet key for encryption/decryption.</small>
                                </div>                     

                                <hr>
                                <div class="" style="position: relative; margin-bottom: 10px; margin-top: 20px">
                                    <div class="icon-user bief" style="position: absolute; right: 0; top: -20px; font-size: 75px"></div>
                                    <p>Details of Administrator (You).</p>
                                    <hr>
                                </div>                       

                                <!-- Text input-->
                                <div class="control-group">
                                    <label class="label" for="mete_desc">Full Name</label>

                                    <input id="admin_name" name="admin_name" placeholder="John Nedo" class="input-xlarge" type="text" value="<?php echo (isset($_POST['admin_name']) ? $_POST['admin_name'] : ''); ?>" required>
                                    <small class="help-block">Your full name please.</small>
                                </div> 

                                <!-- Email input-->
                                <div class="control-group">
                                    <label class="label" for="mete_desc">E-Mail</label>

                                    <input id="admin_mail" name="admin_mail" placeholder="mail@example.com" class="input-xlarge" type="email" value="<?php echo (isset($_POST['admin_mail']) ? $_POST['admin_mail'] : ''); ?>" required>
                                    <small class="help-block">Your e-mail please. (required for admin login)</small>
                                </div>

                                <!-- Password input-->
                                <div class="control-group">
                                    <label class="label" for="mete_desc">Password</label>

                                    <input id="admin_pass" name="admin_pass" placeholder="****" class="input-xlarge" type="text" required>
                                    <small id="strength"></small>
                                    <small class="help-block">A strong Password please.</small>
                                </div> 

                                <!-- Password input-->
                                <div class="control-group">
                                    <label class="label" for="mete_desc">Password again</label>

                                    <input id="admin_passw" name="admin_passw" placeholder="****" class="input-xlarge" type="text" required>
                                    <small id="match"></small>
                                    <small class="help-block">Confirm your password.</small>
                                </div>

                                <hr>

                                <!-- Button -->
                                <div class="control-group">
                                    <label class="label" for="run"></label>
                                    <div class="controls">
                                        <span class="help-block pull-right" style="margin-right: 30px"> 
                                            <button id="finish" name="finish" class="btn btn-primary">Finish!</button></span>
                                    </div>    
                                </div>
                                <script type="text/javascript">
                                    jQuery(function() {

                                        jQuery("input").on("keyup", function() {
                                            jQuery("input").each(function() {

                                                if ($(this).val().length > 0) {
                                                    $("body").data("nextOK", 1);
                                                } else {
                                                    $("body").data("nextOK", 0);
                                                    $("button#next").attr("disabled", true);
                                                    return false;
                                                }
                                            });
                                            if ($("body").data("nextOK") == 1)
                                                $("button#next").attr("disabled", false);
                                        });

                                        jQuery('#admin_pass').keyup(function(e) {
                                            var strongRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
                                            var mediumRegex = new RegExp("(?=.{4,}).*", "g");

                                            var enough = new RegExp("(?=.{2,}).*", "g");
                                            if (false == enough.test($(this).val())) {
                                                $('#strength').html('More Characters');
                                                $("body").data("nextOK", 0);
                                            } else if (strongRegex.test($(this).val())) {
                                                $('#strength').className = 'ok';
                                                $('#strength').html('Strong!');
                                            } else if (mediumRegex.test($(this).val())) {
                                                $('#strength').className = 'alert';
                                                $('#strength').html('Medium!');
                                            } else {
                                                $('#strength').className = 'error';
                                                $('#strength').html('Weak!');
                                            }
                                            return true;
                                        }).on("focus", function() {
                                            jQuery('#admin_passw').trigger("keyup");
                                        });
                                        jQuery('#admin_passw').keyup(function(e) {
                                            var cval = $(this).val();
                                            if (cval != "") {
                                                if (cval != $("#admin_pass").val()) {
                                                    $("#match").show().html("Does not match.");
                                                    $("body").data("nextOK", 0);
                                                } else {
                                                    $("#match").hide();
                                                }
                                            }
                                        });
                                    });
                                </script>                       

<?php endif; ?>
                            <?php if ($step == 4 && isset($_GET['finish'])):
                              if(empty($DB_HOST)){
                                    header("Location: /");
                                }                                
                                ?>
                                <?php if (is_array($error) && $error['result'] == 0) { ?>
                                    <div class="alert alert-error">
                                        <small><?php echo $error['msg']; ?></small>
                                    </div>  
                                    <div class="" style="position: relative;">

                                        <small style="width: 86%">Please <a href="/?setup&amp;step=3&amp;install"><< go back</a> and retry.<br>And please check the <b>"/application/lib"</b> folder has the right chmod settings.</small>

                                    </div>
    <?php } else { ?>
                                    <div class="" style="position: relative;">
                                        <div class="icon-bullseye bief" style="position: absolute; right: 0; top: 20px; font-size: 75px"></div>
                                        <h3>You are awesome!</h3>
        <?php if ($error): ?>
                                            <p>Ready now! </p>
                                            <hr>
                                            <div class="alert" style="width: 86%"><small><?php echo $error['msg']; ?></small></div>
        <?php else: ?>
                                            <span class="help-block">Administrator Details: </span>
                                            <small>E-mail: <b><?php echo $_POST['admin_mail'];?></b></small><br>
                                            <small>Password: <b><?php echo $_POST['admin_pass'];?></b></small><br>
                                            <small>Please don't forget to make the changes <b>(Site e-mail, CDN server etc...)</b> from the settings tab.</small>
                                            <hr>
                                            <div class="alert alert-success"><strong>All the things went OK!</strong></div>
        <?php endif; ?>

                                        <small>Please click the login button below, I will redirect you to <b><u><i>"Settings"</i></u></b> Tab.</small>
                                    </div>
                                    <div class="control-group"></div>
                                    <hr>


                                    <!-- Button -->
                                    <div class="control-group">
                                        <label class="label" for="run"></label>
                                        <div class="controls">
                                            <span class="help-block pull-right" style="margin-right: 30px"> 
                                                <a href="/?login" id="loginme" class="btn btn-success btn-large">Login</a></span>
                                        </div>
                                    </div>
    <?php } ?>

                            <?php endif; ?>


                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>