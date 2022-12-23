<?php
  namespace support\DBcore;


  use Exception;
  use support\DBcore\dbconnection;
   use support\DBcore\dbinterface;

use function support\helper\env;

class dbconnectionAdapter implements dbinterface {
           /**
            * stores the instance of the class
            *   
            */
            private static $instance = null;
            /**
             * holds the database connection
             * 
             */
          private $connection = null;
          /**
           * holds the prepared statement
           */
          private $stmt;
          /**
           * holds the returned result
           */
          private $result_num;

          private $result;

      private function __construct(){  
              $conn = dbconnection::getInstance(env('DB_HOST') , env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_NAME'));
              try {
                      $this->connection = $conn->connect();
              } catch(Exception $e){
                    echo $e->getMessage();
                      
              }
      }
      /**
       * returns an instnace of the class
       */
      public static function getInstance(){
        if(!self::$instance){
                self::$instance = new self;
              }
              return self::$instance;
      }
      /**
       * begins a database transaction
       */
       protected function begin_transact(){
               $this->connection->begin_transaction();
               }
               /**
                * sets the autocomit value of the database transaction
                * @param bool $flag  the autocomit value
                * @return void
                */
       protected function set_auto_commit(bool $flag){
               $this->connection->autocommit($flag);
       }
       /**
        * commits the transaction
        * @return true if successful
        */
       protected function commit(){
             return  $this->connection->commit();
       }
       /**
        * rolls back the transaction when called
        * @return true if successful
        */
       protected function rollback(){
              return $this->connection->rollback();
       }

           /** 
           * prepare an sql query
            * @param string $query the sql query
            * @return void
           */
   public function prepare($statement): void {
           try {
                    $this->stmt = $this->connection->prepare($statement);
        
            } catch(Exception $e){
                    echo $e->getMessage();
                 } 
            
    } 
    /**
     * 
     * binds a query parameters to 
     * the prepared statement 
     * 
     * @param array $params  the parameters to bind to the query
     * @return void
     */
    public function bind($params): void{
            if(count($params) < 1){
                  return ;
            }
            try {
          $types = str_repeat("s" , count($params));
          if($this->connection->errno){
          throw new Exception( $this->connection->error);
          } else {
              $this->stmt->bind_param($types, ...$params);
          }
                    }
                 catch (Exception $e){
                echo $e->getMessage();         
                }   
        
    } 
    /**
     * 
     * executes a prepared statement
     * @return void
     */
    public function execute(): void{
                try {
                        if($this->connection->errno){
                 throw new Exception( $this->connection->error);
                        }
                else {
                         $this->stmt->execute();
                         $this->result_num = $this->stmt->num_rows();
                         $this->result = $this->stmt->get_result();
                }
                        }
                     catch (Exception $e){
                    echo $e->getMessage();
                     }
                   
    }  
    /**
     * 
     * prepares a query, bind the params and execute the query
     * @return array result of query if successful
     *  
     */  
    protected function exec($query , $param = []){
       $this->prepare($query);
       $this->bind($param);
        $this->execute();
        $this->close(); 
        return $this->fetch()->fetch_all(MYSQLI_ASSOC);   
  } 
      /**
     * 
     * prepares a query, bind the params and execute the query
     * @return void
     *  
     */   
  protected function void_exec($query , $param = []){
        $this->prepare($query);
        $this->bind($param);
        $this->execute();
        $this->close();  
  }
      /**
     * 
     * prepares a query, bind the params and execute the query
     * @return array a single of query if successful
     *  
     */ 
  protected function scalar_exec($query , $param = []){
        $this->prepare($query);
        $this->bind($param);
        $this->execute();
        $this->close(); 
      return  $this->fetch()->fetch_assoc();  
  }
  /**
   * 
   * generates a unique id for the specified table
   * @param string $table_name   the name of the table you want to generate
   * an id for
   */
   public function generate_unique_id($table_name){
        try {
                if(!empty($table_name)){
        $id =  str_shuffle(123456789123456);
        $final_id = substr($id, 0, strlen($id));
        while($this->check_id($id,$table_name) > 0){
              $final_id = substr($id, 0, strlen($id));
        }
              return $final_id;
             } 
            } catch(Exception $e){
                    throw New Exception($e->getMessage());
            }
          }
          /**
           * checks if the id exists in the table name specified
           */
      private function check_id($id,$table_name){
      try {
     return count($this->exec("select id from
      $table_name where id = ? " , [$id]));
      } catch(Exception $e){
              throw New Exception($e->getMessage());
      }
}
      /**
       * 
       * performs a database transaction
       * @param function  callback function
       */
    protected function transact($function){
        $this->connection->begin_transaction(); 
     try {   
         $function();
       $this->commit();
    } catch(Exception $e){
              $this->connection->rollback();
       echo $e->getMessage();
    }  

   }
     /**
      * 
      * fetch the result of a database query operation
      * @return object result
      */
    public function fetch(): object{
          return $this->result;
    } 
    /**
     * 
     * fetch the number of result of an sql operation
     * @return string 
     */
    public function get_num(): int {
        return $this->result_num;
   } 
   /**
    * 
    * return the prepared statement object
    * @return object statement object
    * 
    */
   public function get_stmt(): object{
           return $this->stmt;
   }

     /**
      * 
      * closes the stmt connection
      * @return void
      */
    public function close(): void {
            if($this->stmt){
            $this->stmt->close();
            }
    } 
     /**
      * use for selecting data from the database
      * 
      * @param string $query the sql query
      * @param array $param the parameter to bind with the
      * query if there is anyone
      * @return array
      */
    public function select(string $query  , $param = []): ?array
       { 
        return $this->exec($query , $param);
        }  
     public function update(string $query , $param = []): void
     {
         $this->void_exec($query , $param);
     }
       
     public function insert(string $query , $param = []): void 
        {
         $this->void_exec($query , $param);
            } 
            public function delete(string $query , $param = []): void
            {
               $this->void_exec($query , $param);
           }
           public function  scalar($query , $param = []): ?array
           {
              return $this->scalar_exec($query , $param);
           }
           public function transaction($function){
                   $this->transact($function);
           }
  
           public function generate_id($table){
            return  $this->generate_unique_id($table);
      }

  }
?>