<?php
  namespace app\http\request;


   class RequestHandler implements Request {
           private $params;
           private $method;
           private $contentType;
           public function __construct($param)
           {
                $this->params = $param;   
                $this->method = trim($_SERVER["REQUEST_METHOD"]);
                $this->contentType = !empty($_SERVER["CONTENT_TYPE"])? trim($_SERVER["CONTENT_TYPE"]) : '';
           }

           /**
            * returns all get data sent with the request 
            * @return array 
            */
       public function get(): array {
               return $_GET;
       }  
       /**
        * returns all post data sent with the request
        * @return array
        */
       public function post(): array {
        return $_POST;
          }
          /**
           * returns all data sent with the request.
           * @return object
           * 
           */
      public function getBody(): object {
              $content = trim(file_get_contents("php://input"));
              $decoded = json_decode($content);
              return $decoded;
      }

      /**
       * returns files sent with the request
       * @return array
       * 
       */
       public function get_files(){
               return $_FILES;
       }
      /**
       * returns all the values that match the url
       * @return array
       * 
       */
      public function getParams(): array{
              return $this->params;
      }
     /**
      * returns the content type sent with the request
      * 
      */
      public function getContentType(): string{
              return $this->contentType;
      }

      /**
       * returns the request method
       * @return string
       * 
       */
      public function getMethod(): string {
              return $this->method;
      }
      /**
       * returns the request url
       */
      public function getUrl(): string{
        return htmlspecialchars($_SERVER["REQUEST_URI"]);
      }
   }


?>