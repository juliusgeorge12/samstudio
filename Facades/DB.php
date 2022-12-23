<?php 
 namespace Facades;

use Facades\facade;
use support\DBcore\dbconnectionAdapter;

  /**
   * @method static public select($sql , $param)
   * @method static public scalar($sql , $param)
   * @method static public update($sql,$param)
   * @method static public insert($sql)
   * @method static public delete($sql)
   * @method static public transaction($callback)
   * @method static public generate_unique_id($table)
   * @see support\DBcore\dbconnectionAdpater
   */

      class DB extends facade { 
           
   public static function getFacadeInstance()
   {
      return dbconnectionAdapter::getInstance();
   }

    }
    
 ?>