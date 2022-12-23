<?php 
  namespace support\model;
   

   interface userInterface {
     /**
      * add an admin user to the admin user table
      * @param array $details the details of the admin.
      */
     public  function  create(array $details): ?bool;
     

     /**
      * 
      * return all the admin users
      * @return array 
      */
     public function fetch_users(): ?array;
        /**
         * get a user details
         * 
         * @param string $user_id
         */
      public function fetch_user(string $user_id);
     /**
      * grant privilege to a user
      * @param string $user_id the id of the user to grant privilege
      * @return true
      */
      
      public function grant_privilege(string $user_id , $privilege);//: bool;

      /**
       * check if a user has the privilege
       * @param string $user_id
       * @param string $privilege
       */
      public function check_privilege(string $user_id , string $privilege);

      /**
       * revoke a user privilege
       * @param string $user_id
       * @param string $privilege
       */
      public function revoke_privilege(string $user_id , string $privilege): bool;

      /**
       * undo revoke on a user privilege
       * @param string $user_id
       * @param string $privilege
       */
      public function undo_revoke_privilege(string $user_id , string $privilege): bool;
    /**
     * loga user in
     * @param string $password
     * @param string $email
     * @return string user_id if successful else return false
     */
      public function login(string $password , string $email);
     
      /**
       * check if an admin already exists
       * @return bool
       */
      public function admin_exists(): bool;
    
       /**
        * deletes a user from the admin_user table
        * @param string $user_id
        */
        public function delete(string $user_id): bool;

        /**
         * 
         * adds a notification to the notifcation table
         * @param array  $details
         */
        public function notify(array $details):  bool;
       
        /**
         * add deatils to user profile table
         * @param array $details
         * @param string $user_id
         * @return true if successful
         */
        public function add_profile(array $details , string $user_id) : bool;

        
        /**
         * add a user_slot
         * @param array $details
          */
        public function add_slot(array $details): bool;

        /**
         * validate a user_slot token
         * @param string $token
         * @param string $token_id
         *  @return array [$telephone , $privilege]
         */
        public function validate_slot(string $token , string $token_id);

        /**
         * delete a user_slot
         * @param string $token_id
          */
        public function delete_slot(string $token_id): bool;


   }