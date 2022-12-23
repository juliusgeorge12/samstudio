<?php
  namespace app\model;

  use support\container\container;
  use support\model\package as ModelPackage;

  class package extends accessor {

        protected static function get_instance(){
                return container::getInstance(ModelPackage::class);
        }
  }