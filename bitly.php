<?php
class Bitly {

  public $api_key   =   NULL;
  public $res       =   NULL;
  public $version   =   '2.0.1';
  
  protected static $endpoint = 'http://api.bit.ly';

  public function __call($method, $args)
  {
    return self::call($method, $args, "object");
  }
  
  public function __callStatic($method, $args)
  {
    return self::call($method, $args, "static");
  }
  
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
  
  public function get_error()
  {
    
  }
  
  public function is_error($res=NULL)
  {
    return ($this->res->errorCode > 0 || $this->res->statusCode != "OK") ? true : false;
  }
  
  protected function is_json($str)
  {
    return (substr(trim($str), 0, 1) == '<') ? false : true;
  }
  
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
  
  protected static function remove_cdata($str)
  {
    return preg_replace('#<!\[CDATA\[(.*?)\]\]>#s', "$1", $str);
  }
  
}
?>