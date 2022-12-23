<?php
 namespace app\http\middleware;

use function support\helper\base64_url_encode;
use function support\helper\get_session;
use function support\helper\random_hash;
use function support\helper\session;

class csrf {
        
        /**
         * generates a token store it in session and return the token
         * @return string 
         */
        public function generate_token(){
                $token = base64_url_encode(random_hash());
                  session('csrf_token' , $token);
                return $token;
        }

        /**
         * return the stored csrf token
         * @return string
         */
         public function get_token(){
                 return get_session("csrf_token");
         }

        /**
         * verify the csrf token
         * @param string $token the token to verify
         * @return true if successful
         */
        
         public function verify($token){
                 $stored_token = $this->get_token();
      if($stored_token === $token) return true; else return false;
         }
  }
 

 ?>