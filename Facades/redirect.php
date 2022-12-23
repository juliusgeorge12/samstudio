<?php   
 namespace Facades;

use support\container\container;
use support\RouteMapper\redirect as RouteMapperRedirect;
 
 /**
  * @method static public to($url , $permanent)
  */

class redirect extends facade {

   public static function getFacadeInstance()
   {
          return container::getInstance(RouteMapperRedirect::class);
   }
  }

?>