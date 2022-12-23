<?php
  namespace support\container;
 

   /**
    * container class for resolving dependencies
    *
    */

   class container {
           /**
            * holds the instances of resolved classes
            */
           private  $resolvedInstances = [];
           private static  $continerInstance = null;
           private $param;
           private  $name;
           private function __constrcut(){

           }
           /**
            * instantiate the class
            * @param class $class
            */
           private function make($class){
                   return new $class(...$this->param);
           }
          
           /**
            * ----------------------------------------------
            * add the instance to the array of resolved instances
            * -------------------------------------------------
            * @param object $instance
            */
           private function add($instance){

                     $this->resolvedInstances[$this->name] = $instance;
           }
           
           /**
            * --------------------------------------------
            * forgets an instantiated class
            *  ------------------------------------------
            * @param string $name the name of the class to forget
            */
           public function forget($name){

           }
           /**
            * --------------------------------------------------------
            * get the container instance                
            * --------------------------------------------------------
            */
           public static function getContainerInstance(){
                 if(!static::$continerInstance){
                         static::$continerInstance = new self;
                 }
                 return static::$continerInstance;
           }
           
           /**
            * 
            * --------------------------------------------------------
            * get the instance of a class
            * ---------------------------------------------------------
            * @param class $classname the name of the class to instantiate
            * @param array $params the parameters to pass to the constructor
            */
           public static function getInstance($name , $params = []){
                   $container = static::getContainerInstance();
                   $container->param = $params;
                   return $container->resolve($name);
                 }
           private function resolve($name){
                $this->name = $name;
                if(!array_key_exists($this->name , $this->resolvedInstances)){
                   $this->add($this->make($this->name));
                    static::getInstance($this->name);
                 }
                 return $this->retrieve();
                }  

                public function retrieve(){
                  return $this->resolvedInstances[$this->name];
                }
         }

   ?>