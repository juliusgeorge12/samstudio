<?php
  namespace Facades;

use support\container\container;
use support\RouteMapper\router as RouteMapperRouter;
  
  /** 
   * @method static public get($url , $callback
   * @method static public post($url , $callback)
   * @method static public put($url , $callback)
   * @method static public delete($url , $callback
   * @method static public any($url , $callback)
   * @method static public init()
   */
   class Router extends facade {
   public static function getFacadeInstance()
   {
     return container::getInstance(RouteMapperRouter::class);
   }
    }

?>