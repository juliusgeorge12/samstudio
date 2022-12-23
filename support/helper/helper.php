<?php
   namespace support\helper;

  use app\services\session\session_handler;

//contains helper functions used in this app.



    /**
     * set at cookie
     * @param string $name name of cookie
     * @param string $value cookie value 
     * @param int $expiration cookie expiration date in seconds, by default it is set to 31 days
     * @param string $path the cookie path
     *  @param string $domain the avaliability of cookie ,by default it is set to same domain as 
     * the domain from which the cookie is set
     * @param bool $secure if set to true cookie would only be sent in a secured context i.e https.
     * default is false 
     * @return void.
     */
    function cookie($name , $value , $expiration = '', $path = '/' , $domain = '' , $secure = false ){
       $expiration = (empty($expiration)) ?  (time() + (60 * 60 * 24 * 31)) : $expiration;
      $domain = (empty($domain)) ? '' : $domain;
     setcookie($name , $value , $expiration ,$path , $domain , $secure);
     
    }
     
    /**
     * deletes a cookie
     * @param string $name cookie name
     * @return void
     */
     function delete_cookie($name , $path = "/"){
        setcookie($name , "deleted" , time() - 3600 , $path);
     }


      /**
       * for getting cookie value
      * @param string $name cookie name
      * @return string if cookie is set otherwise returns false
      */   
      function getcookie($name){
         if(isset($_COOKIE[$name])) return $_COOKIE[$name] ; else return false;
      }
       /**
        * starts and instantiate the custom session handler class
        */
        function start_custom_session(){
          session_set_save_handler(new session_handler , true);
   
        }

    /**
     * set a session variable
     * @param string $name 
     * @param string $variable
     */
        function session($name , $variable){
            $_SESSION[$name] = $variable;
     }



     /**
      * unset a session variable
      */
      function delete_session($session){
         $_SESSION[$session] = '';
      }



      /**
       * destroy the session
       */
      function session_terminate(){
           session_destroy();
      }


      /**
       * gets a session variable
       * @param string $name
       * @return string if sucessful otherwise return false
       */
       function get_session($name){
          if(isset($_SESSION[$name])) return $_SESSION[$name]; else return false;
       }

    
       /**
        * gets the timestamp
        * @return string timestamp
        */
    function timestamp(){
         return date('Y-m-d h:i:s');
    }


    /**
     * formats the date 
     * @param string $date the date to be formatted
     * @param string $format the format to apply
     * @return string the formatted date
     */
    function format_date($date , $format){
         date($date , $format);
    }


    /**
     * use for hashing a string using the hash algo
     * @param string $string the string to be hashed
     * @param string $algo the sha algo to use these include sha1 , sha328 , md5
     * @param string $secret the secret to use in hashing the string
     */
    function hash($string , $algo , $secret ){
      return hash_hmac($algo , $string , $secret);
    }


     /**
      * generate a random hash
      * @param string $algo by default md5 is used
      * @return string 
      */
       function random_hash($algo = 'md5'){
          return hash(random_bytes(100) , $algo , random_bytes(200));
       } 
       

     /**
      * base64 url encode a string
      * @param string $string the string to be encoded
      * @return string
      */
       function base64_url_encode($string){
         return str_replace(['+','/','='],['-','_',''], base64_encode($string));
       }


        /**
      * base64 url decode a string
      * @param string $string the string to be decoded
      * @return string
      */
      function base64_url_decode($string){
         return str_replace(['+','/','='],['-','_',''], base64_decode($string));
      }



    /**
     * use for encrypting data using open_ssl
     * @param string $string the string to be encrypted
     * @return string encrypted string
     */
    function encrypt($string){
      $output = "";
      $ciphering = "AES-128-CTR";
      $iv_length = openssl_cipher_iv_length($ciphering);
      $options = 0;
      $encryption_iv = random_bytes($iv_length);
      $encryption_key = base64_url_decode($string);
       $output = openssl_encrypt($string,$ciphering,$encryption_key,$options,$encryption_iv); 
       $output = base64_encode($output) .'.' . base64_encode($encryption_key) . 
       '.' . base64_encode($encryption_iv) ;
        $output = base64_encode($output);
      return $output;
    }


      /**
     * use for encrypting data using open_ssl
     * @param string $string the string to be encrypted
     * @return string encrypted string
     */
    function decrypt($string){
      $output = "";
      $ciphering = "AES-128-CTR";
      $options = 0;
       $string = base64_decode($string);
         $payload = explode('.', $string);
     $output = openssl_decrypt(base64_decode($payload[0]) , $ciphering , 
     base64_decode($payload[1]) , $options , base64_decode($payload[2]));
       return $output;
    }
    /**
     * generate a token using the payload
     * @param mixed $payload
     * @return string $token
     */
    function tokenizer($payload){
       $data = serialize($payload);
       $data = encrypt($data);
       $token = $data . hash($data , 'md5' , $data);
       $token = base64_url_encode($token);
       return $token;
    }

    /**
     * validate a token generated by the tokenizer and return payload 
     * sent with the token if the token is valid.
     * @param string $token the token to be validated
     * @return mixed 
     */
      function validate_token(string $token){
       $token = base64_decode($token);
         $signature = substr($token , strlen($token) - 32 , 32);
         $payload = substr($token , 0 , strlen($token) - 32);
         $generated_token = $payload . hash($payload , 'md5' , $payload);
         if(!($generated_token === $token)) return false;
         $payload = decrypt($payload);
         $payload = unserialize($payload);
         return $payload;
      }
  /**
   * for getting an environmental variable
   * @param string $varaible
   * @return string if it is defined else false
   */
  function env($key , $default = null){
   $value = getenv($key);
   if($value === false){
           return $default;
   } else {
           return $value;
   }
  }
   
  /**
   * convert a string to html_special character
   * @param string $string
   */
   function html_format(string $string){
      return htmlspecialchars($string);
   }
   /**
    * returns a base64 encode url of a file
    * 
    * @param string $path
    */
    function get_base64_url(string $path){

     if(!file_exists($path)){
       echo "file does not exists"; 
      return false;
    }
      $data = file_get_contents($path);
      $encoded = base64_encode($data);
      $url = "data:" . mime_content_type($path) . ";base64," . $encoded; 
      return $url;
     
    }
    
 ?>