<?php
  namespace app\http\middleware;

use support\container\container;

class middleware {


            public function csrf(){
                    return container::getInstance(csrf::class);
            }

            
            public function jwt(){
                    return container::getInstance(jwt::class);
            }
    }