<?php
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