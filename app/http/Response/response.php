<?php
   namespace app\http\Response;

    class response implements responseInterface {
            private $status = 200;
            private  $message = " ";
            public function status(): int{
                    return $this->status;
            }
            /**
             * set the status code and message
             * @param string $code
             * @param string $message  optional
             * @return void
             */
            public function setStatus($code , $message = " "): void {
                       $this->status = $code;
                       $this->message = $message;
                         http_response_code($code);
            }  


             /**
              * set the content type
              * @param string $type
              * @return void
              * 
              */
               public function setContentType($type) : void{
                           header('Content-Type: ' . $type);
               }
               /**
                * use for setting other headers like 
                * crsf headers ,
                * nounce , 
                * request origin control ,
                * cookie max etc
                * @param array $headers
                * @return void
                */
               public function setHeaders($headers) : void {
                       if(is_array($headers) && count($headers)){
                               foreach($headers as $header){
                           header($header);
                               }
                       }
                    }
           /**
            * sends out json output
            *
            * @param array $data
            */
            public function outputJson($data): void {
                    echo json_encode($data);
            }
            /**
             * send out output
             * @param any $data
             * 
             */
            public function output($data): void {
              echo  $data;
             }
        /**
         * 
         * output the error status code and message in json format
         *  @return void
         */
        public function outputJsonError(): void {
                echo json_encode(["status" => $this->status , "message" => $this->errorMessage]);
        }
        /**
         * output the error status code and message in text format 
         * @return void
         */
        public function outputError(): void {
                echo $this->status . " " . $this->errorMessage;
        }
    }

   ?>