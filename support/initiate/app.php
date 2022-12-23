<?php 
   namespace support\initiate;

     use Facades\Router;
use support\container\container;
use support\envLoader\loader;

use function support\helper\start_custom_session;


class app {
      private static   $router_init_path;
      private static function set_router_def_path(){
           self::$router_init_path =  dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Route/web.php';
           if(DIRECTORY_SEPARATOR === '\\'){
            self::$router_init_path = str_replace('\\' , '/' ,self::$router_init_path);
           }
      }
        public static function run(){
         $loader = container::getInstance(loader::class);
          $loader->load();
          start_custom_session();
          session_start();
             self::set_router_def_path();
             if(file_exists(self::$router_init_path)){
               require_once self::$router_init_path;
               Router::init();
             } else {
               echo "app need the file " . self::$router_init_path  . 
               " to run please create it and define your routes there";
             }
          
        }
     }

   ?>
