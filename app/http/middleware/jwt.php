<?php
   namespace app\http\middleware;

use Exception;
use iterable as ite;
use SplObjectStorage;

use function support\helper\base64_url_decode;
use function support\helper\base64_url_encode;
use function support\helper\decrypt;
use function support\helper\encrypt;
use function support\helper\hash;
use function support\helper\random_hash;

class jwt {


         /**
          * generates a json web token
          * @param array $payload the payload you want to include with the jwt
          * @param int $days the number of days before the token will expire default is 30 days
          */
          public function generate($payload , $days = 30){
           $header = json_encode(["typ"=>"jwt" , "alg" => "md5"]);
           $header = base64_url_encode($header);
           $days = time() + (60 * 60 * 24 * $days);
           $seed = base64_url_encode(random_hash() . random_hash());
           $secret = encrypt($seed);
           $body = [];
           foreach($payload as $key => $value){
                   $body[$key] = $value;
           }
           $body['exp'] = $days;
           $body['t_id'] = $secret;
           $payload = base64_url_encode(json_encode($body));
           $signature = hash($header . '.' . $payload , 'md5' , $seed);
           $signature = base64_url_encode($signature);
           $jwt = [$header , $payload , $signature];
          return implode('.' , $jwt);
             }

             /**
              * validate a json web token generated 
              * @param string $jwt the jwt to be validated.
              * @return array if successful return the payload sent with it otherwise return false.
              */
               public function validate(string $jwt){
                       $jwt = $jwt;
                       $parts = explode('.' , $jwt);
                       $header = (isset($parts[0])) ?  base64_url_decode($parts[0]) : null;
                      $payload = (isset($parts[1])) ?  base64_url_decode($parts[1]) : null;
                         $signature = (isset($parts[2])) ?  base64_url_decode($parts[2]) : null;
                         $header = json_decode($header);
                        $payload_details = json_decode($payload);
                         if(!($payload_details && $header))   return false; 
                      $info = [];
              foreach($payload_details as $key=>$value){
                          if(!(($key === "exp") || ($key === "t_id"))){
                                 $info[$key] = $value;
                             }
                       } 
                       $expiration = $payload_details->exp;
                      $secret = $payload_details->t_id;
                      $expired = time() > $expiration;
                      if($expired)  return false;
                      $new_token = base64_url_encode(json_encode($header));
                      $new_token .= "." . base64_url_encode(json_encode($payload_details));
                      $new_signature = hash($new_token , $header->alg , decrypt($secret));
                       $new_token .= "." . base64_url_encode($new_signature); 
                       if(!($jwt === $new_token)) return false;                     
                      return $info;
 
               }
           }
     
     ?>