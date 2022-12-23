<?php
  namespace app\http\Response;
   
   interface responseInterface {
           
           public function status() : int;


           public function setStatus($code , $message = []) : void;

           public function setContentType($type) : void;

           public function setHeaders($headers)  : void;
           
           public function outputJson($data): void ;
           
           public function output($data): void ;

           public function outputJsonError() : void;

           public function outputError() : void;

   } 

?>