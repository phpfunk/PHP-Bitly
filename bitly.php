<?php
/**
Description:    Really simple Bitly API client.
Requirements:   PHP 5+, json_decode(), bitly API Key
API Doc:        http://code.google.com/p/bitly-api/wiki/ApiDocumentation

Five main API methods:
1. shorten
2. expand
3. info
4. stats
5. errors

You can call any of them easily by:
$bitly = new Bitly;
$res = $bitly->shorten(array(
  'apiKey'  =>  'YOUR KEY',
  'login'   =>  'YOUR USERNAME',
  'longUrl' =>  'YOUR URL'
));

If you have PHP 5.3.0+ installed you can:
$res = Bitly::shorten(array(
  'apiKey'  =>  'YOUR KEY',
  'login'   =>  'YOUR USERNAME',
  'longUrl' =>  'YOUR URL'
));

The array argument should be an array of keys and values.
The keys corresponding to the API arguments and the values,
well being the value you'd like to set.

You DO NOT have to set the version or format parameter.
**/

class Bitly {

  public function __call($method, $args)
  {
    return self::call($method, $args);
  }
  
  public function __callStatic($method, $args)
  {
    return self::call($method, $args);
  }
  
  protected function call($method, $args)
  {
    $method = strtolower($method);
    $params = null;
    $format = 'json';
    foreach ($args[0] as $key => $val) {
      $params .= '&' . $key . '=' . urlencode($val);
      $format = ($key == 'format') ? strtolower($val) : $format;
    }
    $res = file_get_contents('http://api.bit.ly/' . $method . '?version=2.0.1' . $params);
    return ($format == 'json') ? json_decode($res) : $res;
  }
  
}
?>