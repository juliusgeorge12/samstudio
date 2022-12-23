<?php
  namespace Facades;

  use RuntimeException; 

  class facade {
    private static $name;
    protected static $instance;
    public static function getFacadeInstance(){
      throw new RuntimeException('facade does not have a getFacadeInstance method');
    }

    protected static function load_instance(){
      static::$instance = static::getFacadeInstance();
    }
    public static function __callStatic($name, $arguments)
    {
           static::load_instance();
           return static::$instance->$name(...$arguments);
    }

   

   }

?>