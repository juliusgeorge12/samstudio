<?php
 namespace support\DBcore;
  use Exception;
  use mysqli;
  class dbconnection {
           private static $instance = null;
          private $connection = null;
          private $host = null;
          private $username = null;
          private $password = null;
          private $dbname = null;

          private function __construct($host , $username , $password , $dbname = null){
           $this->host = $host;
           $this->username = $username;
           $this->password = $password;
           $this->dbname = $dbname;
          }

          public static function getInstance($host , $username , $password , $dbname = null){
            if(!self::$instance){
              self::$instance = new self($host , $username , $password , $dbname);
            }
            return self::$instance;
          }
        public function connect()  {
          mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
            try {
                 $this->connection = new mysqli($this->host , $this->username , $this->password , $this->dbname);
              return $this->connection;
                } catch(Exception $e){
                throw New Exception($e->getMessage()); 
            }
        }

        }
       

 ?>