<?php
  namespace support\model;

 use Facades\DB;

use function support\helper\timestamp;

class userModel implements userInterface {

        public function create(array $details): bool {

           $params = $details;
           $sql = "INSERT INTO 
           `admin_user` (`id` , `firstname` , `lastname` , `password` , `email`)
            VALUES(?,?,?,?,?) ";
            DB::insert($sql , $params);
            return true;
        }

        public function fetch_users(): ?array
        {
               $sql = "SELECT id , email , CONCAT(firstname , ' '  , lastname) as name from `admin_user` where `deleted_at` is null";
               $result = DB::select($sql);
               if(!$result) return []; 
               return $result;
               
        }
        /**
         * get user details
         * 
         * @param string $user_id
         */
        public function fetch_user(string $user_id){
          $sql = "SELECT a.id ,CONCAT(a.firstname , ' '  , a.lastname) as name ,
          p.photo as photo from `admin_user` a
          left join admin_profile p on p.user_id = a.id   where `deleted_at` is null and a.id = ?";
          $result = DB::scalar($sql , [$user_id]);
          if(!$result) return null; 
          return $result;
        }
        public function user_exists(string $user_id): bool
        {
               $sql = "SELECT id  from `admin_user` where `id` = ? and `deleted_at` is null";
               $result = DB::scalar($sql , [$user_id]);
               if(!$result["id"]) return false; 
               return true;
               
        }

        public function grant_privilege(string $user_id , $privilege): bool
        {
          $privilege_id = DB::generate_unique_id('user_privilege');
          $privileges = ["add_user","delete_user","special","create","edit","delete"];
          $sql = "INSERT INTO `user_privilege`(`id`, `user_id`, `add_user`, `delete_user`, 
          `special`, `create` , `edit` , `delete`) VALUES (?,?,?,?,?,?,?,?)
          on duplicate key update `add_user` = ? , `delete_user` = ? , `special` = ? , `create` = ? ,
           `edit` = ? , `delete` = ? 
          ";
          $params = [$privilege_id,$user_id];
          $arr = [];
          if(!is_array($privilege) && $privilege === "all"){
             for($i= 0; $i < count($privileges); $i++){
              array_push($arr , true);
             }
          } else {
            $privilege = explode(',',$privilege);
           foreach($privileges as $check){
            if(in_array($check , $privilege)){
               array_push($arr,1); }
                else { array_push($arr , 0); }

                 }
             }
             $arr = array_merge($arr , $arr);
            $params = array_merge($params , $arr);
               DB::insert($sql , $params);
               return true;
             }

        public function check_privilege(string $user_id, string $privilege)
        {
          $all =  ["add_user","delete_user","special","create","edit","delete"];
          $positive = "";
          $negative = "";
          if($privilege === 'all'){
            foreach($all as $one){
              $positive .= "`$one` = '1' and ";
              $negative .= "`$one` = '0' and ";
            }
             $positive = substr(trim($positive),0,strlen($positive) - 4);
             $negative = substr(trim($negative),0,strlen($negative) - 4);
            $sql = "SELECT CASE WHEN ($positive) THEN true ELSE false
            END as granted FROM user_privilege WHERE user_id = ?";
  
          } else {
       $sql = "SELECT CASE WHEN `$privilege` = '1' THEN true WHEN `$privilege` = '0' THEN false
       END as granted FROM user_privilege WHERE user_id = ?";
          }
       return DB::scalar($sql , [$user_id])["granted"];
        }

      /**
       * for revoking a user privilege(s)
       * @return true if successful
       */
      public function revoke_privilege(string $user_id, string $privilege): bool
       {
           $params = [];
           $sql = "UPDATE `user_privilege` SET  `add_user` = ? , `delete_user` = ? ,
           `special` = ? , `create` = ? , `edit` = ? , `delete` = ? WHERE user_id = ?";        
              if($privilege === "all"){
                $params = ["0","0","0","0","0","0"];
                            } 
              else {
        $privileges = explode(',',$privilege);
        $sql = "UPDATE `user_privilege` SET ";
         foreach($privileges as $privilege){
         $sql .= "`$privilege`" . " = ? ,";
         array_push($params,"0");
               }
        $sql = substr($sql,0,strlen($sql) - 1);
        $sql .= "WHERE user_id = ?";

                          }
        array_push($params,$user_id);
        DB::update($sql , $params);
        return true;
      }

       /**
       * for undoing a revoke on a user privilege(s)
       * @return true if successful
       */
      public function undo_revoke_privilege(string $user_id, string $privilege): bool
       {
        $sql = "UPDATE `user_privilege` SET  `add_user` = ? , `delete_user` = ? ,
        `special` = ? , `create` = ? , `edit` = ? , `delete` = ? WHERE user_id =?";
           $params = [];
              if($privilege === "all"){
          $params = ["1","1","1","11","1","1"];
                            } 
              else {
        $privileges = explode(',',$privilege);
        $sql = "UPDATE `user_privilege` SET ";
         foreach($privileges as $privilege){
         $sql .= "`$privilege`" . " = ? ,";
         array_push($params,"1");
               }
        $sql = substr($sql,0,strlen($sql) - 1);
        $sql .= "WHERE user_id = ?";
         }
        array_push($params,$user_id);
        DB::update($sql , $params);
        return true;
      }
     /**
      * log a user in
      * @param string $password
      * @param string $email
      */
    public function login(string $password , string $email ){
       $sql = "select id as user_id , password from admin_user where  `email` = ? and `deleted_at` is null ";
       $details = DB::scalar($sql , [$email]);
       $user_id = $details['user_id'];
      if(password_verify($password , $details['password'])){
         if(password_needs_rehash($details['password'] , PASSWORD_BCRYPT , ['cost' => 11])){
           $new_password = password_hash($password , PASSWORD_BCRYPT , ["cost" => 11]);
           $this->change_password($user_id , $new_password);
         }
       return $user_id;
       }
       return false;
       }
        /**
         * checks if a user password is valid
         * 
         */
       public function validate_password(string $password , string $user_id){
        $sql = "select  password from admin_user where  `id` = ? and `deleted_at` is null ";
        $details = DB::scalar($sql , [$user_id]);
        $password_hash = $details['password'];
       if(password_verify($password , $password_hash)){
          if(password_needs_rehash($details['password'] , PASSWORD_BCRYPT , ['cost' => 11])){
            $new_password = password_hash($password , PASSWORD_BCRYPT , ["cost" => 11]);
            $this->change_password($user_id , $new_password);
          }
        return true;
        }
        return false;
       }
     
     /**
      * check if an admin exists
      * @return bool
      */
      public function admin_exists(): bool
      {
        $sql = "select count(`id`) as num from admin_user where ?";
         $result = DB::scalar($sql , [true])["num"];
         if($result >= 1) return true; else return false;
      } 
      /**
       * check if an email exists
       * 
       * @param string $email
       */

       public function email_exists(string $email){
        $sql = 'select id from `admin_user` where email = ?';
        $result = DB::scalar($sql , [$email])["id"];
        if(is_null($result)) return false; else return true;
       }
        /**
         * checks if a user has a profile picture
         * 
         * 
         * @param string $user_id
         * @return string|false user photo on success and 
         * false if photo does not exists
         * 
         */
        public function pic_exists(string $user_id){
          $sql = 'select photo  from `admin_profile` where user_id = ?';
          $result = DB::scalar($sql , [$user_id])["id"];
          if(is_null($result) || empty($result)) return false; else return $result;
        }

       /**
      * check if the user is an admin user
      * @return bool
      */
      public function is_admin(string $user_id): bool
      {
        return $this->check_privilege($user_id ,'all');
      } 
      
      /**
       * 
       * adds a new notification entry to the notification table
       * @param array $details
       * @return bool
       */
      public function notify(array $details): bool
      {
        $sql = "insert into `notification`(`id` , `content`) values(?,?)";
        DB::insert($sql , $details);
        return true;
      } 
      public function revenue(){
        $sql = "SELECT
        SUM(amount) AS balance,
        (
        SELECT
            SUM(t2.amount)
        FROM TRANSACTION
            t2
        WHERE
            t2.success = FALSE
    ) AS processed,
    (
        SELECT
            SUM(t3.amount)
        FROM TRANSACTION
            t3
        WHERE
            success
    ) AS pending
    FROM TRANSACTION
        t1";
        $result = DB::scalar($sql);
        return $result;
      }
      /**
       * adds a user profile
       * @param array $details
       * @return bool
       */
      public function add_profile(array $details, string $user_id): bool
      { 
        $params = [DB::generate_unique_id('admin_profile') , $user_id];
        $sql = "insert into `admin_profile`(`id` , `user_id` , `photo` , `bio` , `telephone`)
          values(?,?,?,?,?)"; 
        $params = array_merge($params , $details);
         DB::insert($sql , $params);
        return true;
      }
      /**
       * updates a user profile
       * 
       * @param string $user_id
       * @param array $details [$photo , $bio , $telephone]
       */
      public function update_profile(string $user_id , array $details){
        $params = $details;
        array_push($params , $user_id);
        $sql = "update `admin_profile` set `photo` = ? , `bio` = ?, `telephone` = ? where user_id = ? "; 
        DB::update($sql , $params);
        return true;
      }
      public function profile_exists(string $user_id){
        $sql = "select telephone , bio from `admin_profile` where user_id  = ? ";
        $result = DB::scalar($sql , [$user_id]);
      if($result["telephone"] !== null && $result["bio"] !== null) return true; else return false;
     
      }

      /**
       * delete a user
       * @param string $user_id
       * @return bool
       */
      public function delete(string $user_id): bool
      {
       DB::transaction(
         function($user_id){
          $sql1 = "update `admin_user` set `deleted_at` = ? where user_id = ?";
          $sql2 = "delete from `admin_profile` where user_id = ?";
          $sql3 = "delete from `user_privilege` where user_id = ?";
           DB::update($sql1 , [$user_id]);
           DB::delete($sql2 , [$user_id]);
           DB::delete($sql3 , [$user_id]);
         }
       );
        return true;
      }
      /**
       * add a user slot
       * @param array $details
       */
      public function add_slot(array $details): bool
      {
        $sql = "insert into `user_slot` (`id` , `token` , `user_id` , `privilege` , `email`) VALUES(?,?,?,?,?)";
        DB::insert($sql , $details);
        return true;
      }

      /**
       * delete a user slot
       * @param string $token_id
       */
      public function delete_slot(string $token_id): bool
      {
        $sql = "delete from `user_slot` where id = ?";
        DB::delete($sql , [$token_id]);
        return true;
      }

      /**
       * validate a user_slot token 
       * @param $token
       * @return array [$email ,$user_id , $privilege] if successful
       */
      public function validate_slot(string $token , string $token_id)
      {
        $sql = "select `email` , `user_id` , `privilege` , CASE
         WHEN (created_at + (60 * 60 * 24 * 5) ) > NOW() THEN true ELSE false END AS expired 
         from `user_slot` where token = ?";
          $result = DB::scalar($sql , [$token]);
          if(is_null($result)){
            return false;
          }
          if($result["expired"]){
            return false;
          }
          $this->delete_slot($token_id);
          return [$result["email"] , $result["user_id"] , $result["privilege"]];
      }

      /**
       * change the user password
       * 
       * @param string $user_id
       * @param string $new_password
       */
      public function change_password(string $user_id , string $new_password){
       $new_password = password_hash($new_password , PASSWORD_BCRYPT , ["cost" => 11]);
        $sql = "update `admin_user` set password = ? where id = ? ";
         DB::update($sql , [$new_password , $user_id]);
        return true; 
      }
       /**
       * change the user email
       * 
       * @param string $user_id
       * @param string $new_email
       */
      public function change_email(string $user_id , string $new_email){
        $sql = "update `admin_user` set email = ? where id = ? ";
        DB::update($sql , [$new_email , $user_id]);
         return true;
      }
       /**
       * change the user first name and last name
       * 
       * @param string $user_id
       * @param array $names = [$firstname , $lastname]
       */
      public function change_name(string $user_id ,  $names = []){
        $params = $names;
        array_push($params , $user_id);
        $sql = "update `admin_user` set firstname = ? , lastname = ? where id = ? ";
        DB::update($sql , $params);
        return true; 
      }

   }