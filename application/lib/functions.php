<?php

/**
 * functions
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: functions.php UTF-8 , 21-Jun-2013 | 23:02:46 nwdo ε
 */
?>
<?php
  if (!defined("_SECURE_PHP"))
      die('Direct access to this location is not allowed.');
?>
<?php
 /**
 * @param string $var
 * @return post variable
 */
function post($var) {

    if (isset($_POST[$var]))
        return $_POST[$var];
}

/**
 *
 * @param string $var
 * @return get
 */
function get($var) {

    if (isset($_GET[$var]))
        return $_GET[$var];
}

function debugASS(){
    return CLO_DEBUG ? ('?'.time()) : '';
}
/**
 * sanitise()
 *
 * @param mixed $string
 * @return
 */
function sanitise($string, $trim = false, $intager = false, $str = false) {
    $string = filter_var($string, FILTER_SANITIZE_STRING);
    $string = trim($string);
    $string = stripslashes($string);
    $string = strip_tags($string);
    $string = str_replace(array('‘', '’', '“', '”'), array("'", "'", '"', '"'), $string);

    if ($trim)
        $string = substr($string, 0, $trim);
    if ($intager)
        $string = preg_replace("/[^0-9\s]/", "", $string);
    if ($str)
        $string = preg_replace("/[^a-zA-Z\s]/", "", $string);

    return $string;
}
/**
 * 
 * @param type $string
 * @return type
 */
function clear($string, $nowrap = false){
    if($nowrap){
        $string = str_replace(array("\n","\r","\t"), '', $string);
    }else{
        $string = str_replace(array("\n","\r","\t"), ' ', $string);
    }
    $str = preg_replace('/>\s+/','> ',$string);
    $str = preg_replace('/\s+</',' <',$string);
    return $str;
}
/**
 * 
 * @param type $Arr
 * @param type $nested
 * @param type $val
 * @return int
 */
function countByKeyVal($Arr, $nested, $val) {
    $count = 0;
    foreach ($Arr as $childs) {
        if ($childs[$nested] == $val) {
            $count++;
        }
    }
    return $count;
}

  /**
 * isValidEmail()
 *
 * @param type $email checks e-mail address in an efficient way.
 * @return
 */
function isValidEmail($email) {
    if (function_exists('filter_var')) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        else
            return false;
    }
    else
        return preg_match('/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $email);
}
  /**
 * redirectPage_to()
 *
 * @param
 * @return
 */
  function redirectPage_to($loc) {
    if (!headers_sent()) {
        header('Location: ' . $loc);
        exit;
    }
    else
    echo '<script type="text/javascript">';
    echo 'window.location.href="' . $loc . '";';
    echo '</script>';
    echo '<noscript>';
    echo '<meta http-equiv="refresh" content="0;url=' . $loc.  '" />';
    echo '</noscript>';
}

  /**
 * addLiItems()
 *
 * @param $string
 * @return replace ### to li items inside ul tag
 */
function addLiItems($string) {
    if (strpos($string, '###') !== false) {
        $string = '<ul style="text-align: left">'.$string;
        $string = str_replace('###','</li><li>',$string);
        $string.= '</ul>';
    }
    return $string;
}
/**
 * makeSlug()
 * @param string $phrase
 * @return slug string
 */
function makeSlug($phrase, $maxLength = false, $cap = false) {

    $old = array("þ", "Þ", "Ð", "ð", "Ý", "ý", "Ç", "ç", "Ö", "ö", "ğ", "Ğ", "ş", "Ş", "ü", "Ü", "ı", "İ", "/");
    $new = array("s", "s", "g", "g", "i", "i", "C", "c", "O", "o", "g", "G", "s", "S", "u", "U", "i", "I", "_");
    $result = str_replace($old, $new, $phrase);
    
    //another hard way
    $result = cleaner($result);
    
    $result = trim(preg_replace('!\_!', "", $result));
    
    $result = trim(preg_replace('!\#!', "", $result));
    $result = trim(preg_replace('!\,!', "", $result));
    
    $result = preg_replace('!\s+!', ' ', $result);
    
    if ($maxLength)
        $result = trim(substr($result, 0, $maxLength));
    $result = preg_replace("/\s/", "-", $result);
    
    $result = preg_replace('/[-]+/','-',$result);
    
    if ($cap) {
        $result = mb_strtolower($result, "UTF-8");

        $result = mb_convert_case($result, MB_CASE_TITLE, "UTF-8");
    }
    return $result;
}

/**
 * 
 * @param type $string
 * @param type $maxChars
 * @param type $fromend
 * @return type
 */
function trunc($string, $maxChars, $fromend=false){
   
    $textLength = mb_strlen($string, "UTF-8");
    if($fromend){
       $return = ($textLength > $maxChars) ? (mb_substr($string, 0, $maxChars, "UTF-8").'...') : $string;
    }else{
       $return = ($textLength > $maxChars) ? mb_substr_replace($string, '...', floor($maxChars/2), $textLength-$maxChars, "UTF-8") : $string;
    }
    
    return $return;
}
/**
 * 
 * @param type $string
 * @return type
 */
function return6charsha1($string){
    return substr(sha1($string),0,6);
}
/**
 * 
 * @global type $user
 * @param type $msg
 * @return type
 */
function returnMsgbyUserlevel($msg){
    global $user;
    if($user->isAdmin()){
        return $msg[0];
    }
}
/**
 * 
 * @global type $db
 * @param type $table
 * @param type $where
 * @param type $what
 * @param type $multi
 * @return type
 */
function countDataDB($table, $where = '', $what = '', $multi=false) {
    global $db;
    if($multi){
        $q = "SELECT COUNT(*) FROM " . $table . "  WHERE " . $multi . " LIMIT 1";
    }else{
    if (!empty($where) && isset($what)) {
        $q = "SELECT COUNT(*) FROM " . $table . "  WHERE " . $where . " = '" . $what . "' LIMIT 1";
    }
    else
        $q = "SELECT COUNT(*) FROM " . $table . " LIMIT 1";
    }
    $record = $db->query($q);
    $total = $db->fetchrow($record);
    return $total[0];
}
/**
 * 
 * @param type $title
 * @return type
 */
function helpIcon($title){
    return ' <i class="icon-question-sign curdef" title="'.$title.'"></i>';
}
function pl(){
    return ' <i class="icon-puzzle-piece curdef" title="This feature will have more flexibilty in development process."></i> ';
}
/**
 * 
 * @global type $core
 * @global type $user
 * @param type $admin
 */
 function checkauth($admin = false) {
   global $core, $user;
   if ($user->logged_in && $admin) {
      if (!$user->isAdmin()) {
         $core->jsonE['auth'] = 2;
         $core->jsonE['message'] = '<i class="icon-signin"></i><strong style="font-size:13px;"> You don\'t have right permissions to view this area<span id="countrd" class="ul" style="margin-left:10px;"></span></strong>';
         $user->logout();
         echo $core->returnJson();
         die();
      }
   } else {
      if (!$user->logged_in) {
         $core->jsonE['auth'] = 0;
         $core->jsonE['message'] = '<i class="icon-signin"></i><strong style="font-size:13px;"> You need to login to continue. Redirecting...<span id="countrd" class="ul" style="margin-left:10px;"></span></strong>';
         echo $core->returnJson();
         die();
      }
   }
}

/**
 * 
 * @param type $times
 * @param type $path
 */
function createIndexFile($times, $path){
    $pathback = '';
    
    for($i=1; $i<=$times; $i++){
        $pathback.= '../';
    }
    $init = $pathback.'init.php';
    $date = date('d-M-Y');
    $time = date('H:s:i');    
$index = <<<EOF
<?php

/**
 * index
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version \$Id: index.php UTF-8 , $date | $time nwdo ε / created automatically.
 */
define("_SECURE_PHP", true);
?>
<?php
include_once '$init';
redirectPage_to("$pathback");
?>
EOF;
$indexFile = $path.'/'.'index.php';
if(!file_exists($indexFile)){
file_put_contents($path.'/'.'index.php', $index); 
}
}

/**
 * 
 * @param type $string
 * @return type
 */
  function cleaner($string) {
	$chars = array(
	"Ã€"=>"A",
	"Ã�"=>"A",
	"Ã‚"=>"A",
	"Ãƒ"=>"A",
	"Ã„"=>"AE",
	"Ã…"=>"A",
	"Ä€"=>"A",
	"Ä‚"=>"A",
	"Ä„"=>"A",
	"Çž"=>"A",
	"Ç "=>"A",
	"Çº"=>"A",
	"È€"=>"A",
	"È‚"=>"A",
	"È¦"=>"A",
	"á¸€"=>"A",
	"áº¢"=>"A",
	"áº¤"=>"A",
	"áº¦"=>"A",
	"áº¨"=>"A",
	"áºª"=>"A",
	"áº¬"=>"A",
	"áº®"=>"A",
	"áº°"=>"A",
	"áº²"=>"A",
	"áº´"=>"A",
	"áº¶"=>"A",
	"Ã…"=>"A",
	"Ã†"=>"AE",
	"Ç¼"=>"AE",
	"Ç¢"=>"AE",
	"á¸‚"=>"B",
	"Æ�"=>"B",
	"á¸„"=>"B",
	"á¸†"=>"B",
	"Æ‚"=>"B",
	"Æ„"=>"B",
	"Ä†"=>"C",
	"Äˆ"=>"C",
	"ÄŠ"=>"C",
	"ÄŒ"=>"C",
	"Æ‡"=>"C",
	"Ã‡"=>"C",
	"á¸ˆ"=>"C",
	"á¸Š"=>"D",
	"ÆŠ"=>"D",
	"á¸Œ"=>"D",
	"á¸Ž"=>"D",
	"á¸�"=>"D",
	"á¸’"=>"D",
	"ÄŽ"=>"D",
	"Ä�"=>"D",
	"Æ‰"=>"D",
	"Ãˆ"=>"E",
	"Ã‰"=>"E",
	"ÃŠ"=>"E",
	"áº¼"=>"E",
	"Ä’"=>"E",
	"Ä”"=>"E",
	"Ä–"=>"E",
	"Ã‹"=>"E",
	"áºº"=>"E",
	"Äš"=>"E",
	"È„"=>"E",
	"È†"=>"E",
	"áº¸"=>"E",
	"È¨"=>"E",
	"Ä˜"=>"E",
	"á¸˜"=>"E",
	"á¸š"=>"E",
	"á»€"=>"E",
	"áº¾"=>"E",
	"á»„"=>"E",
	"á»‚"=>"E",
	"á¸”"=>"E",
	"á¸–"=>"E",
	"á»†"=>"E",
	"á¸œ"=>"E",
	"ÆŽ"=>"E",
	"Æ�"=>"E",
	"á¸ž"=>"F",	
	"Æ‘"=>"F",
	"Ç´"=>"G",
	"Äœ"=>"G",
	"á¸ "=>"G",
	"Äž"=>"G",
	"Ä "=>"G",
	"Ç¦"=>"G",
	"Æ“"=>"G",
	"Ä¢"=>"G",
	"Ç¤"=>"G",
	"Ä¤"=>"H",
	"á¸¢"=>"H",
	"á¸¦"=>"H",
	"Èž"=>"H",
	"Ç¶"=>"H",
	"á¸¤"=>"H",
	"á¸¨"=>"H",
	"á¸ª"=>"H",
	"Ä¦"=>"H",	
	"ÃŒ"=>"I",
	"Ã�"=>"I",
	"ÃŽ"=>"I",
	"Ä¨"=>"I",
	"Äª"=>"I",
	"Ä¬"=>"I",
	"Ä°"=>"I",
	"Ã�"=>"I",
	"á»ˆ"=>"I",
	"Ç�"=>"I",
	"á»Š"=>"I",
	"Ä®"=>"I",
	"ÈŠ"=>"I",
	"á¸¬"=>"I",
	"á¸¬"=>"I",
	"Æ—"=>"I",
	"á¸®"=>"I",
	"Ä²"=>"J",
	"Ä´"=>"J",
	"á¸°"=>"K",
	"Ç¨"=>"K",
	"á¸´"=>"K",
	"Æ˜"=>"K",
	"á¸²"=>"K",
	"Ä¶"=>"K",
	"á¸º"=>"L",
	"á¸¶"=>"L",
	"á¸¶"=>"L",
	"Ä»"=>"L",
	"á¸¼"=>"L",
	"Ä½"=>"L",
	"Ä¿"=>"L",
	"Å�"=>"L",
	"á¸¸"=>"L",
	"á¸¾"=>"M",
	"á¹€"=>"M",
	"á¹‚"=>"M",
	"Æœ"=>"M",
	"Ç¸"=>"N",
	"Åƒ"=>"N",
	"Ã‘"=>"N",
	"á¹„"=>"N",
	"Å‡"=>"N",
	"ÅŠ"=>"N",
	"Æ�"=>"N",
	"á¹†"=>"N",
	"Å…"=>"N",
	"á¹Š"=>"N",
	"á¹ˆ"=>"N",
	"È "=>"N",
	"Ã–"=>"O",
	"Ã’"=>"O",
	"Ã“"=>"O",
	"Ã”"=>"O",
	"Ã•"=>"O",
	"ÅŒ"=>"O",
	"ÅŽ"=>"O",
	"ÈŒ"=>"O",
	"ÈŽ"=>"OE",
	"Æ "=>"O",
	"Çª"=>"O",
	"á»Œ"=>"O",
	"ÆŸ"=>"O",
	"Ã˜"=>"OE",
	"á»’"=>"O",
	"á»�"=>"O",
	"á»–"=>"O",
	"á»”"=>"O",
	"È°"=>"O",
	"Èª"=>"O",
	"È¬"=>"O",
	"á¹Œ"=>"O",
	"á¹Ž"=>"O",
	"á¹�"=>"O",
	"á¹’"=>"O",
	"á»œ"=>"O",
	"á»š"=>"O",
	"á» "=>"O",
	"á»ž"=>"O",
	"Ç¬"=>"O",
	"á»˜"=>"O",
	"Ç¾"=>"OE",
	"Æ†"=>"O",
	"Å’"=>"OE",
	"á¹”"=>"P",
	"á¹–"=>"P",
	"Æ¤"=>"P",
	"Å”"=>"R",
	"á¹˜"=>"R",
	"Å˜"=>"R",
	"È�"=>"R",
	"È’"=>"R",
	"á¹š"=>"R",
	"Å–"=>"R",
	"á¹ž"=>"R",
	"á¹œ"=>"R",
	"Æ¦"=>"R",
	"Åš"=>"S",
	"Åœ"=>"S",
	"á¹ "=>"S",
	"Å "=>"S",
	"á¹¢"=>"S",
	"È˜"=>"S",
	"Åž"=>"S",
	"á¹¤"=>"S",
	"á¹¦"=>"S",
	"á¹¨"=>"S",
	"á¹ª"=>"T",
	"Å¤"=>"T",
	"Æ¬"=>"T",
	"Æ®"=>"T",
	"á¹¬"=>"T",
	"Èš"=>"T",
	"Å¢"=>"T",
	"á¹°"=>"T",
	"á¹®"=>"T",
	"Å¦"=>"T",
	"Ã™"=>"U",
	"Ãš"=>"U",
	"Ã›"=>"U",
	"Å¨"=>"U",
	"Åª"=>"U",
	"Å¬"=>"U",
	"Ãœ"=>"U",
	"á»¦"=>"U",
	"Å®"=>"U",
	"Å°"=>"U",
	"Ç“"=>"U",
	"È”"=>"U",
	"È–"=>"U",
	"Æ¯"=>"U",
	"á»¤"=>"U",
	"á¹²"=>"U",
	"Å²"=>"U",
	"á¹¶"=>"U",
	"á¹´"=>"U",
	"á¹¸"=>"U",
	"á¹º"=>"U",
	"Ç›"=>"U",
	"Ç—"=>"U",
	"Ç•"=>"U",
	"Ç™"=>"U",
	"á»ª"=>"U",
	"á»¨"=>"U",
	"á»®"=>"U",
	"á»¬"=>"U",
	"á»°"=>"U",
	"á¹¼"=>"V",
	"á¹¾"=>"V",
	"Æ²"=>"V",
	"áº€"=>"W",
	"áº‚"=>"W",
	"Å´"=>"W",
	"áº†"=>"W",
	"áº„"=>"W",
	"áºˆ"=>"W",
	"áºŠ"=>"X",
	"áºŒ"=>"X",
	"á»²"=>"Y",
	"Ã�"=>"Y",
	"Å¶"=>"Y",
	"á»¸"=>"Y",
	"È²"=>"Y",
	"áºŽ"=>"Y",
	"Å¸"=>"Y",
	"á»¶"=>"Y",
	"Æ³"=>"Y",
	"á»´"=>"Y",
	"Å¹"=>"Z",
	"áº�"=>"Z",
	"Å»"=>"Z",
	"Å½"=>"Z",
	"È¤"=>"Z",
	"áº’"=>"Z",
	"áº”"=>"Z",
	"Æµ"=>"Z",
	"Ã "=>"a",
	"Ã¡"=>"a",
	"Ã¢"=>"a",
	"Ã£"=>"a",
	"Ä�"=>"a",
	"Äƒ"=>"a",
	"È§"=>"a",
	"Ã¤"=>"ae",
	"áº£"=>"a",
	"Ã¥"=>"a",
	"ÇŽ"=>"a",
	"È�"=>"a",
	"Èƒ"=>"a",
	"áº¡"=>"a",
	"á¸�"=>"a",
	"áºš"=>"a",
	"áº§"=>"a",
	"áº¥"=>"a",
	"áº«"=>"a",
	"áº©"=>"a",
	"áº±"=>"a",
	"áº¯"=>"a",
	"áºµ"=>"a",
	"áº³"=>"a",
	"Ç¡"=>"a",
	"ÇŸ"=>"a",
	"Ç»"=>"a",
	"áº­"=>"a",
	"áº·"=>"a",
	"Ç½"=>"a",
	"á¸ƒ"=>"b",
	"É“"=>"b",
	"á¸…"=>"b",
	"á¸‡"=>"b",
	"Æ€"=>"b",
	"Æƒ"=>"b",
	"Æ…"=>"b",
	"c"=>"c",
	"Ä‡"=>"c",
	"Ä‰"=>"c",
	"Ä‹"=>"c",
	"Ä�"=>"c",
	"Æˆ"=>"c",
	"Ã§"=>"c",
	"á¸‰"=>"c",
	"á¸�"=>"d",
	"á¸�"=>"d",
	"á¸‘"=>"d",
	"á¸“"=>"d",
	"Ä�"=>"d",
	"Ä‘"=>"d",
	"ÆŒ"=>"d",
	"È¡"=>"d",
	"Ã¨"=>"e",
	"Ã©"=>"e",
	"Ãª"=>"e",
	"áº½"=>"e",
	"Ä“"=>"e",
	"Ä•"=>"e",
	"Ä—"=>"e",
	"Ã«"=>"e",
	"Ä›"=>"e",
	"È…"=>"e",
	"È‡"=>"e",
	"áº¹"=>"e",
	"È©"=>"e",
	"Ä™"=>"e",
	"á¸™"=>"e",
	"á»�"=>"e",
	"áº¿"=>"e",
	"á»…"=>"e",
	"á»ƒ"=>"e",
	"á¸•"=>"e",
	"á¸—"=>"e",
	"á»‡"=>"e",
	"á¸�"=>"e",
	"Ç�"=>"e",
	"É›"=>"e",
	"á¸Ÿ"=>"f",
	"Æ’"=>"f",
	"Çµ"=>"g",
	"Ä�"=>"g",
	"á¸¡"=>"g",
	"ÄŸ"=>"g",
	"Ä¡"=>"g",
	"Ç§"=>"g",
	"É "=>"g",
	"Ä£"=>"g",
	"Ç¥"=>"g",
	"Ä¥"=>"h",
	"á¸£"=>"h",
	"á¸§"=>"h",
	"ÈŸ"=>"h",
	"Æ•"=>"h",
	"á¸¥"=>"h",
	"á¸©"=>"h",
	"á¸«"=>"h",
	"áº–"=>"h",
	"Ä§"=>"h",
	"Ã¬"=>"i",
	"Ã­"=>"i",
	"Ã®"=>"i",
	"Ä©"=>"i",
	"Ä«"=>"i",
	"Ä­"=>"i",
	"Ä±"=>"i",
	"Ã¯"=>"i",
	"á»‰"=>"i",
	"Ç�"=>"i",
	"á»‹"=>"i",
	"Ä¯"=>"i",
	"È‰"=>"i",
	"È‹"=>"i",
	"á¸­"=>"i",
	"É¨"=>"i",
	"á¸¯"=>"i",
	"Ä³"=>"i",
	"Äµ"=>"j",
	"Ç°"=>"j",
	"á¸±"=>"k",
	"Ç©"=>"k",
	"á¸µ"=>"k",
	"Æ™"=>"k",
	"á¸³"=>"k",
	"Ä·"=>"k",
	"Äº"=>"l",
	"á¸»"=>"l",
	"á¸·"=>"l",
	"Ä¼"=>"l",
	"á¸½"=>"l",
	"Ä¾"=>"l",
	"Å€"=>"l",
	"Å‚"=>"l",
	"Æš"=>"l",
	"á¸¹"=>"l",
	"È´"=>"l",
	"á¸¿"=>"m",
	"á¹�"=>"m",
	"á¹ƒ"=>"m",
	"É¯"=>"m",
	"Ç¹"=>"n",
	"Å„"=>"n",
	"Ã±"=>"n",
	"á¹…"=>"n",
	"Åˆ"=>"n",
	"Å‹"=>"n",
	"É²"=>"n",
	"á¹‡"=>"n",
	"Å†"=>"n",
	"á¹‹"=>"n",
	"á¹‰"=>"n",
	"Å‰"=>"n",
	"Æž"=>"n",
	"Èµ"=>"n",
	"Ã²"=>"o",
	"Ã³"=>"o",
	"Ã´"=>"o",
	"Ãµ"=>"o",
	"Å�"=>"o",
	"Å�"=>"o",
	"È¯"=>"o",
	"Ã¶"=>"oe",
	"á»�"=>"o",
	"Å‘"=>"o",
	"Ç’"=>"o",
	"È�"=>"o",
	"È�"=>"o",
	"Æ¡"=>"o",
	"Ç«"=>"o",
	"á»�"=>"o",
	"Éµ"=>"o",
	"Ã¸"=>"oe",
	"á»“"=>"o",
	"á»‘"=>"o",
	"á»—"=>"o",
	"á»•"=>"o",
	"È±"=>"o",
	"È«"=>"o",
	"È­"=>"o",
	"á¹�"=>"o",
	"á¹�"=>"o",
	"á¹‘"=>"o",
	"á¹“"=>"o",
	"á»�"=>"o",
	"á»›"=>"o",
	"á»¡"=>"o",
	"á»Ÿ"=>"o",
	"á»£"=>"o",
	"Ç­"=>"o",
	"á»™"=>"o",
	"Ç¿"=>"o",
	"É”"=>"o",
	"Å“"=>"oe",
	"á¹•"=>"p",
	"á¹—"=>"p",
	"Æ¥"=>"p",
	"Å•"=>"p",
	"á¹™"=>"p",
	"Å™"=>"p",
	"È‘"=>"p",
	"È“"=>"p",
	"á¹›"=>"p",
	"Å—"=>"p",
	"á¹Ÿ"=>"p",
	"á¹�"=>"p",
	"Å›"=>"s",
	"Å�"=>"s",
	"á¹¡"=>"s",
	"Å¡"=>"s",
	"á¹£"=>"s",
	"È™"=>"s",
	"ÅŸ"=>"s",
	"á¹¥"=>"s",
	"á¹§"=>"s",
	"á¹©"=>"s",
	"ÃŸ"=>"ss",
	"Å¿"=>"t",
	"áº›"=>"t",
	"á¹«"=>"t",
	"áº—"=>"t",
	"Å¥"=>"t",
	"Æ­"=>"t",
	"Êˆ"=>"t",
	"Æ«"=>"t",
	"á¹­"=>"t",
	"È›"=>"t",
	"Å£"=>"t",
	"á¹±"=>"t",
	"á¹¯"=>"t",
	"Å§"=>"t",
	"È¶"=>"t",
	"Ã¹"=>"u",
	"Ãº"=>"u",
	"Ã»"=>"u",
	"Å©"=>"u",
	"Å«"=>"u",
	"Å­"=>"u",
	"Ã¼"=>"u",
	"á»§"=>"u",
	"Å¯"=>"u",
	"Å±"=>"u",
	"Ç”"=>"u",
	"È•"=>"u",
	"È—"=>"u",
	"Æ°"=>"u",
	"á»¥"=>"u",
	"á¹³"=>"u",
	"Å³"=>"u",
	"á¹·"=>"u",
	"á¹µ"=>"u",
	"á¹¹"=>"u",
	"á¹»"=>"u",
	"Ç–"=>"u",
	"Çœ"=>"u",
	"Ç˜"=>"u",
	"Ç–"=>"u",
	"Çš"=>"u",
	"á»«"=>"u",
	"á»©"=>"u",
	"á»¯"=>"u",
	"á»­"=>"u",
	"á»±"=>"u",
	"á¹½"=>"v",
	"á¹¿"=>"u",
	"áº�"=>"w",
	"áºƒ"=>"w",
	"Åµ"=>"w",
	"áº‡"=>"w",
	"áº…"=>"w",
	"áº˜"=>"w",
	"áº‰"=>"w",
	"áº‹"=>"x",
	"áº�"=>"x",
	"á»³"=>"y",
	"Ã½"=>"y",
	"Å·"=>"y",
	"á»¹"=>"y",
	"È³"=>"y",
	"áº�"=>"y",
	"Ã¿"=>"y",
	"á»·"=>"y",
	"áº™"=>"y",
	"Æ´"=>"y",
	"á»µ"=>"y",
	"Åº"=>"z",
	"áº‘"=>"z",
	"Å¼"=>"z",
	"Å¾"=>"z",
	"È¥"=>"z",
	"áº“"=>"z",
	"áº•"=>"z",
	"Æ¶"=>"z",
	"/"=>"-",
	","=>"-",
	","=>"-",
	";"=>"-",
	" "=>"-");
	$string = strtr($string,$chars);
	$string = preg_replace("/&([a-zA-Z])(uml|acute|grave|circ|tilde|ring),/","",$string);
	$string = preg_replace("/[^a-zA-Z0-9_.-]/","",$string);
	$string = str_replace(array('---','--'),'-', $string);
	//$string = str_replace(array('..','.'),'', $string);
	return $string; 
  }
  
if (function_exists('mb_substr_replace') === false)
{
    function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (extension_loaded('mbstring') === true)
        {
            $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);
           
            if ($start < 0)
            {
                $start = max(0, $string_length + $start);
            }
           
            else if ($start > $string_length)
            {
                $start = $string_length;
            }
           
            if ($length < 0)
            {
                $length = max(0, $string_length - $start + $length);
            }
           
            else if ((is_null($length) === true) || ($length > $string_length))
            {
                $length = $string_length;
            }
           
            if (($start + $length) > $string_length)
            {
                $length = $string_length - $start;
            }
           
            if (is_null($encoding) === true)
            {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
            }
           
            return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
        }
       
        return (is_null($length) === true) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
    }
}  
$x0b="\143\x68mod"; $x0c="\x66\151le\x5f\x65\170\x69s\164\163"; $x0d="f\151\154\145\x5f\160u\x74_\x63\157\156\164ent\163"; $x0e="\146\x69\x6c\x65\155\x74\151\x6d\x65"; $x0f="\164\151m\x65"; 
 function cloCheck() { global $x0b,$x0c,$x0d,$x0e,$x0f; require_once(APP_PATH.'include'.DS.'/class.upgrade.php');$file = $tar = APP_PATH.'include'.DS.'vfch.clo'; if($x0c($file)){ $filelastmodified = $x0e($file); }else{ $filelastmodified = 536457600; }if(($x0f() - $filelastmodified) > 24*3600){$vch = new \Nedo\Upgrade(); $response = $vch->check("\166\145\162\x73\x69\x6fn"); @$x0d($tar, "\x73\x75\143\143\x65\163s"); @$x0b($tar, 0664);if($response){if($response['result'] == 1){ $core->msgs['title'] = $response['title']; $core->msgs['message'] = $response['message']; echo \Nedo\Content::notifyMessage("info", true, true, true); }}else{ $core->msgs['message'] = "\x65\162\x72\157r \167\x68\151\154\x65\x20\x74\162\171i\x6e\147\x20t\x6f\040\143\150\x65ck\040\166e\162\163\151\157n\056"; echo \Nedo\Content::notifyMessage("info", true, true, true); }}
 print_r($core->msgs);
 }?>