<?php
  namespace app\services\session;

use Facades\DB;
use SessionHandlerInterface;
 use function support\helper\timestamp;

class session_handler implements SessionHandlerInterface {
     public function __construct()
          {
                  
          }
      
       public function open($path, $name): bool
       {   
                   return true;
       }
       public function read($id): string
       {  
        $sql = "SELECT data FROM `session` WHERE id = ?";
         $result = DB::scalar($sql , [$id])["data"];
        if(is_null($result)) return ' '; else { return $result; }
         }
      public function close(): bool
      {
              return true;
      }

      public function write($id, $data): bool
      {
         $sql = "INSERT INTO `session` (`id` , `data`) VALUES (?,?) ON DUPLICATE KEY UPDATE `data` = ?";
         $param = [$id , $data , $data];     
        DB::insert($sql , $param);
         return true; 
        
      }

     public function destroy($id): bool
      {        
        $sql = "DELETE  FROM `session` WHERE id = ?";
        $param = [$id];     
       DB::delete($sql , $param);
        return true;
        
        }
      public function gc($max_lifetime): int
      {  
        $sql = "DELETE FROM `session` WHERE modified_at < DATE_SUB(NOW() , INTERVAL $max_lifetime SECOND)";
        DB::delete($sql); 
        return true;
      }
   }

  ?>