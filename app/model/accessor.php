<?php
  namespace app\model;

use RuntimeException;

class accessor {
  
         protected static $instance;
        
         private static function load_instance(){

                static::$instance = static::get_instance();
               
            }

        private static function get_instance(){
            throw new RuntimeException('there is no get_instance method in the model class,
            hence no instance of model is returned');
        }
         
         public static function __callStatic($method, $arguments)
         {
             static::load_instance();
            return static::$instance->$method(...$arguments);
           }
   }

?>