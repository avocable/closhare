<?php

/**
 * class.share
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: class.share.php UTF-8 , 29-Jul-2013 | 10:53:08 nwdo ε
 */
namespace Nedo;
if (!defined("_SECURE_PHP"))
   die('Direct access to this location is not allowed.');

class Share {

   //var $dpfs = 'shared_'; //prefix for columns of "_shared"
   private $shortUrlArr;

   /**
    * 
    * @global type $core
    * @global type $user
    * @return type
    */

   function createShareUrl() {

      global $core, $user;

      return $core->generateRandID();
   }

   /**
    * 
    * @global type $core
    * @global type $fhand
    * @global type $encr
    * @param type $ItemsHash
    * @return type
    */
   function comboutSharedItems($ItemsHash) {
      global $core, $fhand, $encr;

      $elements = array();

      $decodedElements = $encr->decode($ItemsHash);
      
      $elements['type'] = $fhand->fileOrFolder($decodedElements);

      $parts = explode("%%", $decodedElements);

      $elements['uid'] = $parts[1];
      $comboutedItems = $parts[0];

      $elements['items'] = $fhand->getRequestedFilesArr($comboutedItems);

      return $elements;
   }

   public function getShareElementParams($elements, $type=false, $uihash=false) {
      global $user, $fhand, $encr;
      
      //check for a quick cache;
      //if(isset($this->shortUrlArr[$elements]) && !empty($this->shortUrlArr[$elements])) return $this->shortUrlArr[$elements];
      
      require_once(LIB_PATH . "/rotors/class.goo.gl.php");
      $goo = new GoogleURIShortener();
      $otype = $type;
      
      $userid = ($uihash ? $encr->decode($uihash) : $user->userid);
      $info = array(
          'name' => null,
          'description' => null
      );

      if ($type == 'fo' || ($fhand->fileOrFolder($elements) == 'folder')) {
         $data = $fhand->getFolderInfoDB($elements);
         $info['name'] = $data['folder_name'];
         $info['description'] = $data['folder_description'];
         
      } else {

         //file(s)
         $files = $fhand->getRequestedFilesArr($elements);
         
         $metaImgArr = array();
         
         foreach ($files as $file) {
            $data = $fhand->getFileInfoFromDB($file, $userid );
            
            if(!$data) return array("result" => 0);

            $mime = $fhand->getMimeFromExtension($data['file_extension']);
            $type = $fhand->getTypeIcon($data, false);

            $info['name'].= $type . ' >> ' . $data['file_name'] . '.' . $data['file_extension'] . ', ';
            $info['description'] = ' added on ' . date("d F, Y", strtotime($data['date']));
            
            //prepare image preview url for sharing. for og:tags etc...
            if($fhand->isImage($data['file_extension'])){
               if(count($metaImgArr) <= 10)
                  $metaImgArr[] = array(
                   "name"       => $fhand->getFileName($data, false),
                   "user_dir"   => return6charsha1($data['file_user_id']),
                   "resolution" => "320x320"
               );
            }
         }
         $info['name'] = trim(sanitise($info['name']), ',');
      }
      $shareObj = $elements . '%%' . $userid;
           
      $shareHash = $encr->encode($shareObj);
      
      $shareUrl = CLO_URL . '/share/' . $shareHash;

      $shortenURI = $goo->shorten($shareUrl);

      $return = clear('<div id="shareme" data-url="' . CLO_URL . '" data-text="' . CLO_SITE_NAME . '">
            <div href="javascript:;" class="btn btn-primary origin">
            <i class="icon-share"></i>
            </div>
            </div>');
      
      $result = array(
          'html' => $return,
          'url' => $shareUrl,
          'link' => $shortenURI,
          'title' => $info['name'],
          'description' => $info['description'] ? $info['description'] : ''
      );
      
      if($otype == false){
         unset($result['html']);
      }
      
      if(isset($metaImgArr) && !empty($metaImgArr))
         $fhand->createMultipleImageViewUrl($metaImgArr);
      
      return $result;
   }
   
   
   public function createMetaData($metas){
      global $core, $user;
      
      $metaArr = array();
      
      //print nl2br(print_r($metas,true));echo"<br>";
      $metaProp = '';
      $x=0;
      foreach($metas as $key => $meta){
         $x++;
         if(!$meta)continue;
         foreach($meta as $k => $val){
            if(is_numeric($k)){
               $metaProp.= '<meta property="'.$val['property'].'" content="'.$val['content'].'">'."\n";
               //if(!array_search($val['content'], $metas))
                       //$metaArr[($k == 'kind' ? $val : '')] = '<meta property="'.$val['property'].'" content="'.$val['content'].'">'."\n";
            }
         }
         if($x == 5){
            break;
         }
//         unset($key);
//         unset($value);
      }
      
//      foreach ($metaArr as $key => $value) {
//         $metaProp.= $value;
//      }
      return $metaProp;
   }
   
   
   public function createSocialMeta($metaArr){
      
      $metaArrOut = $type = array();
      $metaProp = $ontype = '';
      $metaArrOut["type"] = "website";
      
      if(is_array($metaArr) && !empty($metaArr)){
         $x = 0;
         foreach ($metaArr as $key => $value) {
            $x++;

            $description = isset($metaArrOut["description"]) ? $metaArrOut["description"].', ' : ($value['folder'].' &hArr; ');
            
            //object card
            $metaArrOut["card"] = $value['card']; 
            
            //object title
            $metaArrOut["title"] = $value['title'];
            
            //object type
            if(!in_array($value["type"], $type)){
               
               $mtype = $value["type"];
               
               $type[$value["type"]] = $ontype = ($mtype == "audio" ? "music.song" : ($mtype == "video" ? "video.other" : ($mtype == "image" ? "article" : $metaArrOut["type"]) ) );
               
               $metaArrOut["type"] = $type[$value["type"]];
            }
            
            //object url
            $metaArrOut["url"] = $value['url'];              
            
            //object description
            $metaArrOut["description"] = $description.$value['description'];  
            
            //object site_name
            
            $metaArrOut["site_name"] = $value['site_name'];
            
            //specific infos for description

            if(!isset($metaArrOut["image"])) $metaArrOut["image"] = array();
            
            if($x > 4) continue;
            
            if(!in_array($value['source'], $metaArrOut["image"]))
                    $metaArrOut["image"][] = $value['source'];
            
         }
         if(count($type) == 1){
            $metaArrOut["type"] = $ontype;
         }else{
            $metaArrOut["type"] = "website";
         }
         unset($key);
         unset($value);   
         
         /**sc => Schema.org markup for Google+
          * og => Open Graph
          * tw => Twitter Card data
          */
         $tags = array(
             "sc" => array("tag" => "itemprop", "prefix" => ""),
             "og" => array("tag" => "property", "prefix" => "og:"),
             "tw" => array("tag" => "name", "prefix" => "twitter:")
         );   
         
         // create HTML form from Array
         foreach ($tags as $key => $value) {
            foreach ($metaArrOut as $k => $val) {
               if(!is_array($val)){
                  if($this->onlyOgschema($k, $key)){                     
                  $metaProp.= "\n".'<meta '.$tags[$key]["tag"].'="'.$tags[$key]["prefix"].($key == 'sc' && $k == 'title' ? 'name' : $k).'" content="'.$val.'">';
                  }
                  
               }else{
                  //its a preview image
                  $t = 0;
                  foreach($val as $ikey => $image){

                     $metaProp.= "\n".'<meta '.$tags[$key]["tag"].'="'.$tags[$key]["prefix"].$k.($key == 'tw' && $k == 'image' ? ($t.':src') : '').'" content="'.$image.($key == 'tw' && $k == 'image' ? '/' : '').'">';
                     
                     if($key == 'tw' && $k == "image"){
                        $t++;
                     }                     
                  }
                  
               }
            }
            
         }
         
      }
      //var_dump($metaArrOut);
      //var_dump($type);
      return $metaProp."\n";
   }
   
   
   private function onlyOgschema($k, $key){
      
      $oA = array("type", "url", "site_name");
      
      $oT = ($k == "card") ? true : false;
      
      $newk = ($key == "og") ? true : false;
      
      $newt = ($key == "tw") ? true : false;
      
      return ((in_array($k, $oA) && $newk && !$oT) || (!in_array($k, $oA) && !$newk && !$oT) || (!in_array($k, $oA) && $newk && !$oT) || ($newt && $key != "sc" && !in_array($k, $oA)) );
      
   }

}

?>