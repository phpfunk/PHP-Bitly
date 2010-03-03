### Description
Really simple Bitly API client.

### Requirements
PHP 5+, json_decode(), bitly API Key

### API Documentation
http://code.google.com/p/bitly-api/wiki/ApiDocumentation

### Main Methods from Bitly API
1. shorten
2. expand
3. info
4. stats
5. errors

### Object Example
$bitly = new Bitly;
$res = $bitly->shorten(array(
  'apiKey'  =>  'YOUR KEY',
  'login'   =>  'YOUR USERNAME',
  'longUrl' =>  'YOUR URL'
));

### Static Example - PHP 5.3.0+ required
$res = Bitly::shorten(array(
  'apiKey'  =>  'YOUR KEY',
  'login'   =>  'YOUR USERNAME',
  'longUrl' =>  'YOUR URL'
));

### Notes
The array argument should be an array of keys and values. The keys corresponding to the API arguments and the values, well being the value you'd like to set.

You DO NOT have to set the version or format parameter.

I plan on added other features shortly. This is a quick start to calling the Bitly API easily.