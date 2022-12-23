<?php
  namespace app\http\request;
   interface Request {
           public function get() : array ;
           public function post() : array;
            public function getBody(): object;
            public function getParams(): array;
            public function getContentType(): string;
            public function getMethod(): string;
            public function getUrl() : string;
   }

?>