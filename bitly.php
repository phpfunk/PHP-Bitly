<?php
/**
* Simple PHP Bit.ly API Wrapper Class.
*
* @author Jeff Johns <phpfunk@gmail.com>
* @license MIT License
*/
class Bitly {

  public $api_key   =   NULL;
  public $res       =   NULL;
  public $version   =   '2.0.1';
  
  protected static $endpoint = 'http://api.bit.ly';

  /**
  * Magic method to request call for any method called
  * that does not exist in the object.
  *
  * @param  string  $method   method called
  * @param  array   $args     array of arguments
  *
  * @return array
  */
  public function __call($method, $args)
  {
    return self::call($method, $args, "object");
  }
  
  /**
  * Magic method to request call for any method called
  * that does not exist. This is the static version.
  * Only available in PHP 5.3.0+
  *
  * @param  string  $method   method called
  * @param  array   $args     array of arguments
  *
  * @return array
  */
  public function __callStatic($method, $args)
  {
    return self::call($method, $args, "static");
  }
  
  /**
  * Called when you call a method that doesn't exist.
  * Both the static and OO methods call this method
  * to send the API requests to bit.ly.
  *
  * @param  string  $method   method called
  * @param  array   $args     array of arguments
  * @param  string  $type     type of call (object || static)
  *
  * @return array
  */
  protected function call($method, $args, $type)
  {
    $method         =   strtolower($method);
    $params         =   null;
    $format         =   'json';
    $key_found      =   false;
    $version_found  =   false;
    
    foreach ($args[0] as $key => $val) {
      $amp              = (empty($params)) ? '' : '&';
      $params          .=   $amp . $key . '=' . urlencode($val);
      $format           =   ($key == 'format')  ? strtolower($val) : $format;
      $version_found    =   ($key == 'version') ? true : $version_found;
      $key_found        =   ($key == 'apiKey')  ? true : $key_found;
    }
    
    $params .= ($key_found === false && $type == 'object') ? '&apikey=' . $this->api_key : '';
    $params .= ($version_found === false && $type == 'object') ? '&version=' . $this->version : '';
    $params = (substr($params, 0, 1) == '&') ? substr($params, 1) : $params;
    
    $res = file_get_contents(self::$endpoint . '/' . $method . '?' . $params);
    if ($format == 'xml') {
      $res = self::normalize(simplexml_load_string(self::remove_cdata($res)), $method);
    }
    else {
      $res = json_decode($res);
    }
    
    if ($type == 'object') { 
      $this->res = $res;
      $this->api_key = ($key_found === true) ? $args[0]['apiKey'] : $this->api_key;
      $this->version = ($version_found === true) ? $args[0]['version'] : $this->version;
    }
    
    return $res;
  }
  
  /**
  * General method to get data from the returned
  * result. You can call any key from the results
  * object returned from the API call.
  *
  * @param  string  $what : What you want to be returned (EX: shortUrl)
  *
  * @return bool || string || array
  */
  public function get($what)
  {
    $method = 'get_' . $what;
    if (method_exists($this, $method)) {
      return $this->$method();
    }
    
    $key = $this->get_key();
    if ($key === false) {
      return (isset($this->res->results->$what)) ? $this->res->results->$what : false;
    }
    
    return (isset($this->res->results->$key->$what)) ? $this->res->results->$key->$what : false;
  }
  
  /**
  * Returns a string with the error code and message
  *
  * @return string
  */
  protected function get_error()
  {
    return 'Error Number (' . $this->res->errorCode . '): ' . $this->res->errorMessage;
  }
  
  /**
  * Returns the first key, where applicable under
  * the results object returned from the API.
  *
  * @return bool || string
  */
  protected function get_key()
  {
    foreach ((array) $this->res->results as $key => $arr) {
      if (is_array($this->res->results->$key)) {
        return $key;
      }
      else {
        return false;
      }
    }
  }
  
  /**
  * Returns all (if any) referrers by site/path => total.
  * And total number of referrers.
  *
  * @return array
  */
  protected function get_referrers()
  {
    $referrers = (array) $this->res->results->referrers;
    $tmp = array('total'=>0);
    foreach ($referrers as $site => $arr) {
      foreach ($arr as $section => $total) {
        $section = ($section == '/') ? '' : $section;
        $key = ($site == '_empty_') ? 'direct' : $site . $section;
        $tmp[$key] = $total;
        $tmp['total'] += $total;
      }
    }
    return $tmp;
  }
  
  /**
  * Returns a boolean if there is an error or not
  *
  * @return bool
  */
  public function is_error()
  {
    return ($this->res->errorCode > 0 || strtolower($this->res->statusCode) != "ok") ? true : false;
  }
  
  /**
  * Returns boolean if the string is json or not
  *
  * @param  string  $str  String to be evaluated
  *
  * @return bool
  */
  protected function is_json($str)
  {
    return (substr(trim($str), 0, 1) == '<') ? false : true;
  }
  
  /**
  * Since this class uses PHP's Simple XML it creates
  * an object different than the json returned object.
  * This method normalizes the XMl data to be returned
  * in the same pattern as the JSON result would.
  *
  * @param  mixed   $obj      The xml object result from API call
  * @param  string  $method   The method called
  *
  * @return mixed
  */
  protected static function normalize($obj, $method)
  {
    $methods = array('errors','shorten','info');
    if (! in_array($method, $methods)) { return $obj; }
    
    if ($method != 'errors') {
      $k = ($method == 'shorten') ? $obj->results->nodeKeyVal->nodeKey : $obj->results->doc->hash;
      $node = ($method == 'shorten') ? 'nodeKeyVal' : 'doc';
      
      foreach ((array) $obj->results->$node as $key => $val) {
        if ($key != 'nodeKey') {
          $obj->results->$k->$key = $val;
        }
      }
      unset($obj->results->$node);
    }
    else {
      $tmp = (array) $obj;
      $res = (array) $obj->results;
      $tmp['errorMessage'] = (string) $obj->errorMessage;
      $tmp['results'] = array();
      foreach($res['errorCode'] as $key => $value) {
        $tmp['results'][$key]['errorCode'] = $res['errorCode'][$key];
        $tmp['results'][$key]['errorMessage'] = $res['errorMessage'][$key];
        $tmp['results'][$key]['statusCode'] = $res['statusCode'][$key];
      }
      $obj = $tmp;
    }
    
    return $obj;
  }
  
  /**
  * Remove CDATA tags from XML
  *
  * @param  string   $str The string to remove CDATA from
  *
  * @return string
  */
  protected static function remove_cdata($str)
  {
    return preg_replace('#<!\[CDATA\[(.*?)\]\]>#s', "$1", $str);
  }
  
}
?>