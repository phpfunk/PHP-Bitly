### Description
Really simple Bitly API client.

### Requirements
PHP 5+, json_decode(), bitly API Key

### API Documentation
[http://code.google.com/p/bitly-api/wiki/ApiDocumentation](http://code.google.com/p/bitly-api/wiki/ApiDocumentation)

### Updates
There are now two different wrappers created for the Bit.ly API. There is one for version 2 and now the most current version 3. The reason there are now two different libraries is because the entire nomenclature of the result keys has switched from version 2 to version 3. Instead of remapping all the keys I chose to keep the version 2 file and create a version 3 file.

If you want to use any of the version 2 only methods, you must load the version 2 wrapper.

### Methods from Bitly API
1. shorten            (v2, v3)
2. expand             (v2, v3)
3. info               (v2)
4. stats              (v2)
5. errors             (v2)
6. validate           (v3)
7. clicks             (v3)
8. bitly_pro_domain   (v3)

### Object Example - Version 3 API
    $bitly = new Bitly;
    $res = $bitly->shorten(array(
        'apiKey'  =>  'YOUR KEY',
        'login'   =>  'YOUR USERNAME',
        'longUrl' =>  'YOUR URL'
    ));
    
### Object Example - Version 2 API
    $bitly = new Bitly_V2;
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
    
### Object Options
If you are do not use the static call-to-action you have access to other methods for error checking, error messages and getting direct information back. Any key returned under the 'result' can be returned using the 'get' method. Some examples are below.

### Example
    $bitly = new Bitly;
    $res = $bitly->shorten(array(
        'apiKey'  =>  'YOUR KEY',
        'login'   =>  'YOUR USERNAME',
        'longUrl' =>  'YOUR URL'
    ));
    
    if ($bitly->is_error()) {
      print $bitly->get('error');
    }
    else {
      $short_url = $bitly->get('url');
      $hash = $bitly->get('hash');
    }

### Notes
The array argument should be an array of keys and values. The keys corresponding to the API arguments and the values, well being the value you'd like to set.

You DO NOT have to set format param.