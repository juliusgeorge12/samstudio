<?php
  namespace support\FScore;
  use support\FScore\fileManager;
  use Exception;
  
 class fileAdapter extends fileManager {
     public function get_file($path){
             try {
                     if(file_exists($path)){
             $this->open($path , 'r');
           $this->read();
             $this->flush_file();
             $this->close();
                     } else {
                             throw new Exception("the file $path could not be found");
                     }
             } catch (Exception $e){
               echo $e->getMessage();
                }
     }

     public function fetchAsString($path ,$use = false, $context = " "){
      try {
           return $this->getFileAsString($path , $use , $context);  
      } catch (Exception $e){
        echo $e->getMessage();
          }
        }

     public function append_to_file($path , $data){
        try {
        $this->open($path , 'a');
      $this->write($data);
        $this->flush_file();
        $this->close();
        } catch (Exception $e){
          echo $e->getMessage();
        }
}  
public function save_upload($uploads , $name = null , $type = null){
        $root = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' .
         DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR;
         $root = str_replace('\\' , '/' , $root);
         if(!file_exists($root)){ mkdir($root); }
         $file_paths = [];
         $num = 0;
          if(!is_array($uploads["name"])){
          $file = $uploads["tmp_name"];
          $filename = (is_null($name)) ? sha1_file($file) : $name;
          $ext = $this->fileType($uploads['name']);
          $destination = $root . $filename . "." . $ext;
          $path_to_return = ["url" => "public/files/" . $filename , "ext" => $ext];
         if(!is_null($type)){ 
           if(!$this->check_upload_type($uploads , $type)){
            if(is_array($type)){ $msg = implode(" or " , $type); }
            else {
              $msg = $type;
            } 
             throw new \Exception("the file type needed is $msg "); 
                } 
             } 
             $file_paths = $path_to_return;
           $this->move_file($file , $destination);
        } else {
          $all_files = $uploads["tmp_name"];
            for($i = 0; $i < count($all_files); $i++){
           $int = intval($i) + 1;
        $file = $uploads["tmp_name"][$i];
         $filename = (is_null($name)) ? sha1_file($file) : $name . $int;
        $ext = $this->fileType($uploads['name'][$i]);
        $destination = $root . $filename . "." . $ext;
        $path_to_return = ["url" => "public/files/" . $filename , "ext" => $ext];
       if(!is_null($type)){ 
         if(!$this->check_upload_type($uploads[$i] , $type)){
          if(is_array($type)){ $msg = implode(" or " , $type); }
          else {
            $msg = $type;
          } 
           throw new \Exception("the file type needed is $msg "); 
              } 
           } 
          array_push($file_paths , $path_to_return);
         $this->move_file($file , $destination);
        


        }  
         }
         return $file_paths;
       
       }
  }
?>