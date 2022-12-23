<?php
  namespace app\controller;

use app\http\middleware\middleware;
use app\http\request\Request;
  use app\http\Response\responseInterface;
     class controller {
            protected $res;
            protected $req;
            protected $middleware;
            protected $model;
        final  public function __construct(Request $req , responseInterface $res)
            {
                    $this->req = $req;
                    $this->res = $res;
                    $this->middleware = new middleware;
            }
   
    }

?>