<?php
   namespace support\TemplateEngine;

   class engine {
           private static $root;
           private static $template;
           private static $path;
           private static function setRoot(){
              self::$root = dirname(dirname(__DIR__)) . '/app/view/template/';
              if(DIRECTORY_SEPARATOR !== '/'){
                self::$root = str_replace('\\' , '/' , self::$root);
                }
           }
           private static function setPath($name){
                self::setRoot();
                self::$path = self::$root . $name . '.template.html';
                if(DIRECTORY_SEPARATOR !== '/'){
                self::$path = str_replace('\\' , '/' , self::$path);
                }
           }
        protected static function load_template($name){
                self::setPath($name);
                if(file_exists(self::$path)){
                        self::$template = file_get_contents(self::$path);
                } else {
                        echo "the template " . $name .".template.html " . " does not exist in ". 
                        self::$root;
                }
        }
        protected static function inject(array $data){
              if(!is_null($data)){  $s = [];
                $r = [] ;
               foreach($data as $needle=>$info){
                array_push($s, '@' .$needle);
                array_push($r , $info);
               }
               self::$template = str_replace($s , $r , self::$template);
          }
               return self::$template;
        }

        public static function render($view , $data = []){
                self::load_template($view);
               return self::inject($data);
              }
   }
   


?>