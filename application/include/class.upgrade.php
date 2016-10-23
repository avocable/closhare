<?php

/**
 * upgrade
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: class.goo.gl.php UTF-8 , 01-Jul-2013 | 23:53:08 nwdo ε
 */
namespace Nedo;
use Nedo\Core;

if (!defined("_SECURE_PHP")){
    die('Direct access to this location is not allowed.');
}

class Upgrade {
    
    private $_hash;
    private $_installedVersion;
    private $_remoteVersion;
    private $_checkURI;   

   // Constructor
   function __construct() {   
       $sc = new \Nedo\Core();    
       
       $settings = $sc->getSettings(false);
       
       $this->_installedVersion = $sc->clo_version;
       
       $this->_hash = $sc->clo_hash;
       
       $this->_checkURI = "http://closhare.net/fly/closhare-ch.php";
      
   }
   
   
   public function check() {
       
       $Arr = array(
           "c" => "version-check",
           "h" => $this->_hash,
           "v" => $this->_installedVersion
       );
       
       $result = $this->getContent($this->_checkURI, $Arr);
       if($result){
           
           return $this->printResult($result);
       }
       return false;
   }
   
   private function getContent($url, $data) {
       
      $query = http_build_query($data);
      
      if (function_exists('curl_init')) {
         $response = $this->curl_get_content($url, $query);
      } else {
          
            $url = $url . '?' . $query;
            
            $opts = array('https' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $query
                )
            );

            $context  = stream_context_create($opts);            
            
         $response = file_get_contents($url, false, (isset($context) ? $context : NULL));
      }
      
      return $response;
   }
   
   /**
    * 
    * @param type $url
    * @param type $requestType
    * @return type
    */

   private function curl_get_content($url, $query) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        //print_r($response);
        return $response;
    }
    

    private function printResult($json) {
        if($json != null)
            return json_decode($json, true);
    }

}

?>
