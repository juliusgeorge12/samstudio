<?php   
 namespace support\RouteMapper;

 use app\http\Response\response;

 class redirect extends response {
         /**
          * use for making redirect.
          * @param string $url the url
          * @param  bool $permanent if true it send a permanent redirect else a temporary redirect,
          * by default it is true
          * 
          */
         public  function to($url , bool $permanent = true){
                 $stat = ($permanent) ? 301 : 302;
                 $header = ["location: $url"];
                 $this->setStatus($stat);
                 $this->setHeaders($header);
                 exit;
         }
 }

?>