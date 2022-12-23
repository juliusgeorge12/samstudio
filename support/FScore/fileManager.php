<?php 
   namespace support\FScore;
   class fileManager {
           private $stream;
           private $size;
          protected function open($path , $mode){
                $this->stream = fopen($path , $mode);
                $this->size = filesize($path);           
          }
          protected  function read(){
            echo fread($this->stream , $this->size );
        }
        protected  function write($data){
                fwrite($this->stream , $data);
            }
          protected function flush_file(){
                   flush();
           }
          protected function close(){
                  fclose($this->stream);
          }
          protected function readline(){
            fgets($this->stream);
          }
          protected function endoffile() : bool {
                  return feof($this->stream);
          }
          protected function delete($path): bool {
               return   unlink($path);
          }
       protected function fileType($path){
               return strtolower(pathinfo($path , PATHINFO_EXTENSION));
       }
       protected function mime_type($path){
               return strtolower(mime_content_type($path));
       }
       protected function getFileAsString($path , $use = false , $context = " "){
               return file_get_contents($path , $use , $context);
       }
       protected function move_file($filename , $destination){
          return move_uploaded_file($filename , $destination);
       }
       protected function check_type($file , $type){
              if(strpos($this->mime_type($file) , $type)) return true;
              else return false;
          }
          protected function check_upload_type($upload, $type){
                if(is_array($type)){
                        if(in_array($upload['type'] , $type)) return true; else 
                        return false;
                }
                if(strpos($upload['type'] , $type) !== false) 
                        return true;
                else return false;
            }
          protected function rename_file($old_name , $new_name){
                  rename($old_name , $new_name);
          }
       }
?>