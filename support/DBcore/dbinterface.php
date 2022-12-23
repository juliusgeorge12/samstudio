<?php
   namespace support\DBcore;
   interface dbinterface {
         public function prepare($statement): void;
         public function bind($params): void ;
         public function execute(): void;
         public function fetch():  object;
         public function get_num():  int;
         public function get_stmt(): object;
         public function close(): void;
         public function select(string $sql , $params = []): ?array;
         public function update(string $sql , $params = []): void;
         public function delete(string $sql , $params = []): void;
         public function scalar(string $sql , $params = []): ?array;
         public function transaction($callback);
   }
?>