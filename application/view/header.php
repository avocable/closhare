<?php

/**
 * header
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: header.php UTF-8 , 01-Jun-2013 | 02:32:11 nwdo ε
 */
//redirect if host is not the same of the site settings.
$core->fdirect();
?>
<!DOCTYPE html>
<html class="off">
<head>
<meta charset="utf-8">
<title><?php echo (isset($meta['title']) && !empty($meta['title'])) ? $meta['title'] : (\CLO_SITE_NAME. ' | '.CLO_SITE_SLOGAN); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
<meta name="description" content="<?php echo (isset($meta['description']) && !empty($meta['description'])) ? $meta['description'] : $core->meta_description; ?>">
<meta name="author" content="<?php echo \CLO_SITE_NAME; ?>">
<link rel="canonical" href="<?php echo CLO_URL; ?>"/>
<?php if (isset($meta['extra']) && !empty($meta['extra'])): ?>
   <?php print $meta['extra']; ?>
<?php endif; ?>
<script>
function client_pass(name, value) {
var cookie = [name, '=', value, '; domain=.', location.host.replace('www.','').toString(), '; path=/;'].join('');
document.cookie = cookie;
var xhr, params = "cli_resolution="+value;
xhr = new XMLHttpRequest();
xhr.open('POST', "/", true);  
xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
xhr.setRequestHeader("Content-length", params.length);
xhr.setRequestHeader("Connection", "close");
xhr.send(params);}   
client_pass('CLO_RES', (screen.width+'x'+screen.height));
</script>
<link rel="icon" type="image/png" href="<?php echo CLO_DEF_ASS_URI . "img/favicon.png"; ?>">
<?php print $content->printCssFiles(true);?>
<?php if($core->compress_js_css){?>
<!--[if IE 7]>
<link rel="stylesheet" href="<?php echo CLO_DEF_ASS_MIN_URI . "ie.css".debugASS(); ?>">
<![endif]-->
<?php }else{?>
<!--[if IE 7]>
<link rel="stylesheet" href="<?php echo CLO_DEF_CSS_URI . "font-awesome-ie7.min.css".debugASS();?>">
<![endif]-->
<?php }?>
<?php if(!$core->isShareUrI()):?>
<?php print $content->printJsFiles(true);?>
<?php endif;?>
<!-- HTML5 shim, for IE6-8 support -->
<!--[if lt IE 9]>
<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body<?php if(!$core->isShareUrI()):?> class="dn"<?php endif;?>>
<?php if ($user->logged_in || $core->isShareUrI()): echo $content->getTopNav();  endif;?>
 <div id="wrap">
  <div class="container-fluid" role="main">