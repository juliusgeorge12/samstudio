<?php
 namespace app\model;

use support\container\container;
use support\model\userModel;

 /**
  * @method static public fetch_user(string $user_id)
  * @method static public fetch_users()
  * @method static public create(array $details)
  * @method static public grant_privilege($user_id , array $privilege)
  * @method static public check_privilege(string $user_id , string $privilege)
  * @method static public revoke_privilege(string $user_id , string $privileges)
  * @method static public undo_revoke_privilege(string $user_id , string $privileges)
  * @method static public login(string $password , string $email);
  * @method static public update_user_details(array $details , $user_id)
  * @method static public admin_exists()
  * @method static public delete(string $user_id)
  * @method static public update_bio(string $detail , string $user_id )
  * @method static public notify(array $detail)
  * @method static public add_profile(array $details , string $user_id)
  * @method static public profile_exists(string $user_id)
  * @method static public update_profile(string $user_id , array $details)
  * @method static public add_slot(array $details)
  * @method static public delete_slot(string $token_id)
  * @method static public validate_slot(string $token , string $token_id)
  * @method static public revenue()
  * @method static public is_admin(string $user_id)
  * @method static public validate_password(string $password , string $user_id)
  * @method static public email_exists(string $email)
  * @method static public pic_exists(string $user_id)
  * @method static public change_password(string $user_id , string $new_password)
  * @method static public change_email(string $user_id , string $new_email)
  * @method static public change_name(string $user_id , $names)
  * @see support/model/userInterface
  */

class user extends accessor {

  protected static function get_instance(){
        return  container::getInstance(userModel::class);
  }

 }