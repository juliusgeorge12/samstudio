<?php
  namespace Facades;

use support\container\container;
use support\TemplateEngine\engine;

  /**
   * @method static public render($view , $data = [])
   */
 
  class view extends facade {
         public static function getFacadeInstance()
         {
           return container::getInstance(engine::class);
         }
  }

?>