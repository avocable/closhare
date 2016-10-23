<?php

/**
 * config
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: config.ini UTF-8 , 21-Jun-2013 | 23:02:21 nwdo ε
 */
?>
<?php

if (!defined("_SECURE_PHP"))
   die('Direct access to this location is not allowed.');
/**
 * Database Constants - these constants refer to
 * the database configuration settings.
 */
####
###CloShare language###
$lang = 'en';
####
###CloShare version###
define('VERSION', 'version_here');
####
###DB server###
define('DB_SERVER', 'db_server_here');
####
###DB user###
define('DB_USER', 'db_user_here');
####
###DB password###
define('DB_PASS', 'db_pass_here');
####
###Which DB should I use?###
define('DB_DATABASE', 'db_name_here');
####
###TABLE prefix
define('DB_PFX', 'db_prefix_here');
####
###predefined secret key for all encryptions for file-user_passwords etc...Cannot be changed after a fresh installation###
define("ENC_KEY", "enc_key_here");
define("NUM_ENC_KEY", 565);
###
// Should I Show Php, MySql Errors?
// Not recomended for live site. true/false
$DEBUG = true;
?>