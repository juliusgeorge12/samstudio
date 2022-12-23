<?php
   namespace support\RouteMapper;
   use app\http\request\RequestHandler;
   use app\http\Response\response;
use Closure;
use Facades\view;

class router implements routerInterface {
          private  $container;
          private  $registered_path = [];
          public function get($url , $callback){
                        if(strcasecmp($_SERVER["REQUEST_METHOD"] , 'get') !== 0){
                                return ;
                        }
                        $this->register($url , $callback);
        }

        public function post($url , $callback){
                if(strcasecmp($_SERVER["REQUEST_METHOD"] , 'POST') !== 0){ 
                        return ; }
                        $this->register($url , $callback);
                                }
         public function put($url , $callback){
                 if(strcasecmp($_SERVER["REQUEST_METHOD"] , 'put') !== 0){
                                  return ; }
                                  $this->register($url , $callback);
                               }
        public  function delete($url , $callback){
                          if(strcasecmp($_SERVER["REQUEST_METHOD"] , 'delete') !== 0){
                                   return ; }
                                   $this->register($url , $callback);
                                }
        public function any($url , $callback){
          $this->register($url , $callback);
                        }
         private  function register($url , $callback){
                              $this->registered_path[$url] = $callback;
         }
          public  function init(){
                 $request_path = $_SERVER["REQUEST_URI"];
                 $params = parse_url($request_path , PHP_URL_PATH);
                      foreach($this->registered_path as $regex => $cb){
                        $regex = (stripos($regex , "/") !== 0) ? "/" . $regex : $regex;
                        $regex = str_replace('/' , '\/' , $regex);
                $is_match = preg_match('/^' . ($regex) . '$/' , $params , $matches , PREG_OFFSET_CAPTURE);
                     if($is_match){
                        array_shift($matches);
                        $params = array_map( function($param){
                           return $param[0]; } , $matches);
                           if($cb instanceof Closure){
                         $cb(new RequestHandler($params), new response);
                          return;  
                        }
                        $this->init_class($cb , $params);
                        return;
                     }
                       }
                $this->_default(new response); 
                   }
          private  function init_class($callable , $params){
                   $x = explode('@' , $callable);
                   $class = '\app\controller\\' . $x[0];
                   $method = $x[1];
                  $instance = new $class(new RequestHandler($params) , new response);
                    $instance->$method();
                 }
        private  function _default(response $res){
                $res->setContentType("text/html");
                $res->setStatus(404 , "not found");
                $res->output(view::render('not-found'));
        }
    } 
 
?>
