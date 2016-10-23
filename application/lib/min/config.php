<?php

/**
 * config
 * configurations for php minify.
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: config.php UTF-8 , 02-Jun-2013 | 00:45:02 nwdo ε
 */
define("_SECURE_PHP", true);
include_once('../../../init.php');

$min_enableBuilder = false;
$min_builderPassword = 'nfg784043**3';
$min_errorLogger = $DEBUG;
$min_allowDebugFlag = $DEBUG;
$min_cachePath = NEDOXROOT.'application/tmp';
if (!is_dir($min_cachePath)) {
    @mkdir($min_cachePath, 0755, true);  
    createIndexFile(2, $min_cachePath);
} 
$min_documentRoot = NEDOXROOT.'application/assets';
$min_cacheFileLocking = true;
$min_serveOptions['bubbleCssImports'] = true;
$min_serveOptions['rewriteCssUris'] = false;
if($core->compress_js_css){
$min_serveOptions['minifierOptions']['text/css']['prependRelativePath'] = CLO_DEF_ASS_URI.'/'; //hihi joke!
}
$min_serveOptions['maxAge'] = 31556926;
$min_serveOptions['minApp']['groupsOnly'] = true;
$min_symlinks = array();
$min_uploaderHoursBehind = 0;
$min_libPath = dirname(__FILE__) . '/lib';

// try to disable output_compression (may not have an effect)
ini_set('zlib.output_compression', '0');
?>