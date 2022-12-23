<?php
  namespace support\envLoader;

  class loader {
          private $env_path;
          public function __construct()
          {
                  $root = dirname(dirname(__DIR__));
                  $root = str_replace('\\' , '/' , $root);
                  $this->env_path = $root . "/.env";
          }
        public function load(){
                if(!file_exists($this->env_path)){
                  echo "the file " . $this->env_path . " does not exist";
                return ;
                }
         $lines = file($this->env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
         foreach ($lines as $line){
                if(strpos(trim($line) , "#") === 0){
                        continue;
                }
                list($name , $value) = explode('=', $line , 2);
                $name = trim($name);
                $value = trim($value);
                if(!array_key_exists($name , $_SERVER) && !array_key_exists($name , $_ENV)){
                        putenv(sprintf('%s=%s' , $name , $value));
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                }
             }
        }
  }