<?php
  namespace app\controller;
   use app\controller\controller as baseController;
use app\model\user;
use Exception;
use Facades\DB;
use Facades\mail;
use Facades\redirect;
use Facades\view;
use support\FScore\fileAdapter;

use function support\helper\base64_url_encode;
use function support\helper\cookie;
use function support\helper\delete_cookie;
use function support\helper\env;
use function support\helper\get_session;
use function support\helper\getcookie;
use function support\helper\html_format;
use function support\helper\random_hash;
use function support\helper\session;
use function support\helper\session_terminate;
use function support\helper\tokenizer;
use function support\helper\validate_token;

class userController extends baseController {
  
     private function validate_user(){
      $user_id = get_session('user_id');
      $return_to = urlencode($this->req->getUrl());
      $this->res->setContentType('text/html');
      $user_id = get_session('user_id');
      if(!$user_id){
         $login_cookie = getcookie('x');
         $device = getcookie('x_id'); 
       if(!($login_cookie && $device)){
        redirect::to(env('base_url') . '/admin/login?page=' . $return_to);
         return ;
         }
        $user_id = validate_token($login_cookie);
         $device_id = validate_token($device);
         $is_same_device = ($device_id === $_SERVER["HTTP_USER_AGENT"]) ? true : false;
         $user_exists = user::user_exists($user_id);
          if(!($user_id && $is_same_device && $user_exists)) {
            redirect::to(env('base_url') . '/admin/login?page=' . $return_to);
         } else {
         session('user_id' , $user_id); 
         } 
        } 
        return get_session('user_id');
      
     }
      private function get_top(string $user_id){
         $pic = user::fetch_user($user_id)['photo'];
         $pic = (is_null($pic) || empty($pic)) ? env('base_url') . '/asset/img/avatar.png' : env('base_url') . "/". $pic; 

         $notif_num = 0;
         if($notif_num >= 1){
            $notif = '<div class="notif-indicator"></div>';
         } else {
            $notif = '';
         }
         return [$pic , $notif];
      }
        public function index(){
         $new_password = password_hash('Julius' , PASSWORD_BCRYPT , ["cost" => 11]);
         echo $new_password;
        }

      public function create(){
         $csrf = $this->middleware->csrf();
         $post_data = $this->req->post();
       if(!(isset($post_data['csrf-token']) && $csrf->verify($post_data['csrf-token']))){
            $this->res->setStatus(400 , 'invalid request');
            $message = "error bad request, this could be that the request has 
        expired , refresh the page and do not confirm form resubmission";
         $this->res->output(
          view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
       );
           return ;
           }
         if(user::admin_exists()){
            $this->res->setStatus(403);
            $message= "access denied you can't create account admin already exist,
             ask admin to create a slot for you";
             $this->res->output(
          view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
       );
            return ;
         }
            $email = $password = $firstname = $lastname = " ";
            $emailErr = $lnameErr = $fnameErr = $pswErr = " ";
            $fnameOk = $lnameOk = $emailOk = $pswOk = false;
            //validate the first name
            if(isset($post_data["fname"])){
              $firstname = htmlspecialchars($post_data["fname"]);
              if(empty($firstname)){ $fnameErr = "*firstname is required"; }
               else if(!preg_match("/^[a-zA-Z]*$/", $firstname)){ $fnameErr = "*first name can only contain the engish alhabets no whitespace allowed too"; } 
                    else { $fnameOk = true; }
            }
            //validate the last name
            if(isset($post_data["lname"])){
              $lastname = htmlspecialchars($post_data["lname"]);
              if(empty($lastname)){ $lnameErr = "*lastname is required"; }
              else if(!preg_match("/^[a-zA-Z]*$/", $lastname)){ $lnameErr = "*last name can only contain the engish alhabets no whitespace allowed too"; } 
                   else { $lnameOk = true; }
            }
            //validate email
            if(isset($post_data["email"])){
              $email = htmlspecialchars($post_data["email"]);
              if(empty($email)){ $emailErr = "*email is required"; }
              else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ $email = "*your email is invalid"; } 
                   else { $emailOk = true; }
            }
            //validate password
            if(isset($post_data["password"])){
              $password = htmlspecialchars($post_data["password"]);
              if(empty($password)){ $pswErr = "*password is required"; }
              else if(strlen($password) < 6){ $pswErr = "*your password should be atleast 7 character long"; } 
                   else { $pswOk = true; }
            }
            $nonce = random_hash('sha1');
            $csrf = $this->middleware->csrf();
            $csrf_token = $csrf->generate_token();
            if(!($pswOk && $emailOk && $fnameOk && $lnameOk)){
                session('fnameErr' , $fnameErr);
                session('lnameErr' , $lnameErr);
                session('emailErr' , $emailErr);
                session('pswErr' , $pswErr);
                redirect::to('register');
            }
            $password = password_hash($password , PASSWORD_BCRYPT , ['cost' => 11]);
            $user_id = DB::generate_unique_id('admin_user');
            $details = [$user_id ,$firstname , $lastname , $password, $email ];
            if(user::create($details) && user::grant_privilege($user_id, 'all')){
               session('user_id' , $user_id);
               $token = tokenizer($user_id);
               $device_token = tokenizer(htmlspecialchars($_SERVER["HTTP_USER_AGENT"]));
               cookie('x' ,$token, time() + (60 * 60 * 24 * 31) , 'admin/');
               cookie('x_id' ,$device_token, time() + (60 * 60 * 24 * 31) , 'admin/');
               redirect::to(env('base_url') . '/admin/manage/profile');
            }
      }
      

      public function home(){
         $top_message = get_session('top_message');
         $top_type = get_session('top_type');
         $msg_modal = "";
        if(empty($top_message) && empty($top_message)){
            $top_message = "";
         } else {
            if($top_type == "success"){
               $msg = '<p class="success">';
            } else {
               $msg = '<p class="danger">';
            }
               $msg_modal = '<div class="error-dialog"><div class="error">';
               $msg_modal .= $msg . $top_message;
               $msg_modal .= '</p></div></div>';
         }

         $req = $this->req; 
         $user_id = $this->validate_user();
         $top = $this->get_top($user_id);
         $user_name = user::fetch_user($user_id)["name"];
        $revenue = user::revenue();
         $balance =  'N' . $revenue["balance"];
         $pending =  'N' . $revenue["pending"];
         $processed =  'N' . $revenue["processed"]; 
        $this->res->output(
       view::render('dashboard' , ["top_message" => $msg_modal , "img" => $top[0] , "notif" => $top[1] ,
          "username" => $user_name , "balance" => $balance , 
         "pending" => $pending , "processed" => $processed , "base-url" => env("base_url")])
        );
        session('top_message' , '');
        session('top_type' , ''); 
        
      }
      public function admin(){
        redirect::to('admin/dashboard');    
      }
      

      public function users(){

         $req = $this->req;
         $user_id = $this->validate_user();
         $top = $this->get_top($user_id);
         $users = user::fetch_users();

         $table_data = "";
         foreach($users as $user){
            $id = $user["id"];
            $name = $user["name"];
            $email = $user["email"];
            $user_type = (user::is_admin($id)) ? "Admin" : "employee/team";
            $revoke_btn = "admin/user/revoke/" . $id;
            $undo_revoke_btn = "admin/user/undo_revoke/" . $id;
            $delete_btn = "admin/user/delete/" . $id;
            $data = '<tr><td>' . $name . '</td><td>' . $email . '</td><td>'. 
            $user_type . '</td><td><a href="' . $revoke_btn . '">
            <button id="af">Revoke this user privilege</button></a></td>
            <td><a href="' . $undo_revoke_btn . '"><button id="af">undo revoke</button></a></td>
            <td><a href="' . $delete_btn . '"><button id="ef">Delete user</button></a></td></tr>';
            $table_data .= $data;
            }

         $this->res->setContentType('text/html');
         $this->res->output(
         view::render('user_management' ,  [  "notif" => $top[1] , "img" => $top[0] ,
          "table_content" => $table_data , "base-url" => env("base_url")])
         );
         
      }
      public function recent(){

         $req = $this->req;
         $user_id = $this->validate_user();
         $top = $this->get_top($user_id);
         $this->res->setContentType('text/html');
         $this->res->output(
         view::render('recent' , [ "notif" => $top[1] , "img" => $top[0] , "base-url" => env("base_url")])
         );
      }

      public function login(){
         $csrf = $this->middleware->csrf();
         $csrf_token = $csrf->generate_token();
         $req = $this->req;
         $nonce = random_hash('sha1');
         $formError = get_session('login_error');
         $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
         $this->res->setContentType('text/html');
         $this->res->output(
         view::render('login', ['csrf' => $csrf_token , 'nonce' => $nonce ,
          'form-error'=> $formError , "base-url" => env("base_url")])
         );
         session('login_error' , '');
      }


      public function login_validate(){
         $csrf = $this->middleware->csrf();
         $post_data = $this->req->post();
         $get_data = $this->req->get();
         $page = (isset($get_data["page"])) ? $get_data["page"] : null;
         $addition = (!is_null($page)) ? "?page=" . $page : '';
         $redirect_to = (!is_null($page)) ? urldecode($page) : 'dashboard';
         
         if(!(isset($post_data['csrf-token']) && $csrf->verify($post_data['csrf-token']))){
           $this->res->setStatus(400 , 'invalid request');
           $message = "error bad request, this could be that the request has 
           expired , refresh the page and do not confirm form resubmission";
          $this->res->output(
             view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
          );
           return ;
          }
           $form_data = [htmlspecialchars($post_data["email"]) ,
            htmlspecialchars($post_data["password"])];
            if(empty($form_data[0]) || empty($form_data[1])){
               session('login_error' , 'email or password cannot be empty');
               redirect::to('login' . $addition);
                return;
            }
            $user_id = user::login($form_data[1], $form_data[0]);
            if(!$user_id){
               session('login_error' , 'incorrect email or password');
              redirect::to('login' . $addition);
               return;
            }
            session('user_id' , $user_id);
            $token = tokenizer($user_id);
            $device_token = tokenizer(htmlspecialchars($_SERVER["HTTP_USER_AGENT"]));
            cookie('x' ,$token, time() + (60 * 60 * 24 * 31) , '/admin');
            cookie('x_id' ,$device_token, time() + (60 * 60 * 24 * 31) , '/admin');
            redirect::to($redirect_to);
           
      }

      public function signup(){
         if(user::admin_exists()){
            $this->res->setStatus(403);
            $message = "access denied you can't create account admin already exist,
             ask admin to create a slot for you";
           $this->res->output(
          view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
       );
            return ;
         }
        $nonce = random_hash('sha1');
         $csrf = $this->middleware->csrf();
         $csrf_token = $csrf->generate_token();
         $fnameErr = get_session('fnameErr');
         $lnameErr = get_session('lnameErr');
         $emailErr = get_session('emailErr');
         $pswErr = get_session('pswErr');
         $data =   ['csrf' => $csrf_token , 'nonce' => $nonce, 
         'fnameErr' => $fnameErr , 'lnameErr' => $lnameErr , 'emailErr' => $emailErr ,
          'pswErr' => $pswErr , "base-url" => env("base_url")];
         $this->res->setContentType('text/html');
         $this->res->output(
         view::render('register' , $data)
         );

                session('fnameErr' , '');
                session('lnameErr' , '');
                session('emailErr' , '');
                session('pswErr' , '');
      }
      public function about(){
         

         $this->res->output(
            view::render('abt' , ["base-url" => env("base_url")])
         );
      }

   public function manage_about(){
      $csrf = $this->middleware->csrf();
      $csrf_token = $csrf->generate_token();
      $user_id = $this->validate_user();
      $top = $this->get_top($user_id);
      $telephoneErr = get_session('telephoneErr');
      $bioErr = get_session('bioErr');
      $picErr = get_session('picErr');
       $this->res->output(
         view::render('about-us' , [  "img" => $top[0] , "notif" => $top[1] ,
          'crsf_token' => $csrf_token , 'telephoneErr' => $telephoneErr ,
           'bioErr' => $bioErr , 'picErr' => $picErr , "base-url" => env("base_url")])
      );
      session('telephoneErr' , '');
      session('bioErr' , '');
      session('picErr' , '');
   }
   public function update_profile(){
      $user_id = $this->validate_user();
      $csrf = $this->middleware->csrf();
      $post_data = $this->req->post();
      if(!(isset($post_data['csrf-token']) && $csrf->verify($post_data['csrf-token']))){
        $this->res->setStatus(400 , 'invalid request');
       $message = "error bad request, this could be that the request has 
        expired , refresh the page and do not confirm form resubmission";
       $this->res->output(
          view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
       );
        return ;
       }
       $teleponeOk = $bioOk = $picOk = false;
       $telepone = $bio = "";
       if(empty($post_data["telephone"])){
         $telephoneErr = "telephone can't be empty";
         $teleponeOk = false;
       } elseif(!preg_match('/^[0-9]{11,14}$/', filter_var($post_data["telephone"] , FILTER_SANITIZE_NUMBER_INT))){
         $telephoneErr = "telephone can only contain didgit and must be between 11 to 14 digit long";
         $teleponeOk = false;
       }
        else {
         $telephoneErr = "";
         $teleponeOk = true;
         $telepone = html_format($post_data['telephone']);
        }
        if(empty($post_data["bio"])){
         $bioErr = "your bio can't be empty";
         $bioOk = false;
       }
        else {
         $bioErr = "";
         $bioOk = true;
         $bio = html_format($post_data['bio']);
        }
        if(isset($_FILES["profile_pic"]) && !empty($_FILES["profile_pic"]["name"])){
         $file = new fileAdapter;
         try {
          $photo_url = $file->save_upload($_FILES["profile_pic"] , $user_id , ['image/jpeg' , 'image/png']);
          $photo_url = $photo_url["url"] . "." . $photo_url["ext"];
          $picErr = "";
          $picOk = true;
         } catch(Exception $e){
            $picErr = $e->getMessage();
            $picOk = false;
         } 
          } else {
         $picOk = true;
         $prev_photo = user::pic_exists($user_id);
         $photo_url = ($prev_photo) ? $prev_photo : 'asset/img/avatar.png';
         $picErr = "";
        }
        if(($teleponeOk && $bioOk && $picOk)){
         $data = [$photo_url , $bio , $telepone];
         if(user::profile_exists($user_id)){
              if(user::update_profile($user_id , $data)){
               redirect::to(env("base_url") .'/admin/dashboard');
              }
           } else {
         if(user::add_profile($data , $user_id)){
            redirect::to(env("base_url"). '/admin/dashboard');
               }
           }
        }
        else {
        session('telephoneErr' , $telephoneErr);
        session('bioErr' , $bioErr);
        session('picErr' , $picErr);
       redirect::to('profile');
        }
   }
     public function add_photo(){
      echo "afa";
     }
     public function password_reset(){
      $csrf = $this->middleware->csrf();
      $csrf_token = $csrf->generate_token();
      $nonce = random_hash('sha1');
      $emailErr = get_session('emailErr');
      $this->res->setContentType('text/html');
      $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
      $this->res->output(
         view::render('forgot_password' ,  ['csrf' => $csrf_token , 'emailErr' => $emailErr ,
           'nonce' => $nonce , "base-url" => env("base_url")]
      ));
      session('emailErr' , '');
     }

     public function password_reset_email(){
      $csrf = $this->middleware->csrf();
      $post_data = $this->req->post();
     
      if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
        $this->res->setStatus(400 , 'invalid request');
       $message = "error bad request, this could be that the request has 
        expired , refresh the page and do not confirm form resubmission";
       $this->res->output(
          view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
       );
        return ;
       }
        $email = html_format($post_data["email"]);
        $emailErr = "";
        $emailOk = false;
        if(empty($email)){
         $emailErr = "*email can not be empty";
        }
        else if(!user::email_exists($email)){
          $emailErr = "*there is no account associated with the email you entered";
        } else {
         $emailErr = "";
         $emailOk = true;
        }
        if(!$emailOk){
         session('emailErr' , $emailErr);
         redirect::to(env('base_url') . '/admin/password/reset');
         return;
        }
        $this->res->output(
         view::render('forgot_password_email')
        );
     }
     public function add_user(){
      $user_id = $this->validate_user();
      $top = $this->get_top($user_id);
      $this->res->setContentType('text/html');
      if(!user::is_admin($user_id)){
         $this->res->output(
            view::render('error' , ["notif" => $top[1] , "error" => "Error: you don't have enough permission to carry out the action" ,
            "base-url" => env("base_url")])
            );
            return;
      }
      $csrf = $this->middleware->csrf();
      $csrf_token = $csrf->generate_token();
      $nonce = random_hash('sha1');
      $emailErr = get_session('emailErr');
      $pswErr = get_session('pswErr');
      $privErr = get_session('privErr');
      $this->res->setContentType('text/html');
      $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
      $this->res->output(
      view::render('new_user' ,  ['notif' => $top[1] , 'csrf' => $csrf_token ,
       'emailErr' => $emailErr , 'pswErr' => $pswErr , 'privErr' => $privErr])
      );
      session('emailErr' , '');
      session('pswErr' ,'');
      session('privErr' , '');
   }

   public function new_user(){ 
      $user_id = $this->validate_user();
      $csrf = $this->middleware->csrf();
      $post_data = $this->req->post();
      if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
        $this->res->setStatus(400 , 'invalid request');
       $message = "error bad request, this could be that the request has 
        expired , refresh the page and do not confirm form resubmission";
       $this->res->output(
          view::render('token_error' , ["error" => $message])
       );
        return ;
       }
       $new_user_email = html_format($post_data["email"]);
       $admin_password = html_format($post_data["password"]);
       $privileges = (isset($post_data["Privilege"])) ? $post_data["Privilege"] : [];
       $pswOk = $emailOk = $privOk = false;
       $emailErr = $pswErr = $privErr = "";
       if(empty($new_user_email)){
         $emailErr = "*email field cannot be empty";
       } else if(!filter_var($new_user_email , FILTER_VALIDATE_EMAIL)){
         $emailErr = "*invalid email fromat";
       } else if(user::email_exists($new_user_email)){
         $emailErr = "*email has been taken";
       } else {
         $emailOk = true;
         $emailErr = "";
       }
       if(count($privileges) < 1){
         $privErr = "*mark atleast one privilege to grant to this user";
       } else {
         $privErr = "";
         $privOk = true;
       }
       if(empty($admin_password)){
         $pswErr = "*password field can not  be empty";
       }
        else if(!user::validate_password($admin_password , $user_id)){
         $pswErr = "*incorrect password";
       } else {
         $pswOk = true;
         $pswErr = "";
       }
       if(!($pswOk && $emailOk && $privOk)){
         session('privErr' , $privErr);
         session('emailErr' , $emailErr);
         session('pswErr' , $pswErr);
         redirect::to('add');
         return;
     } 
     $new_user_id = DB::generate_unique_id('admin_user');
     $token = base64_url_encode(random_hash());
     $token_id = DB::generate_unique_id('user_slot');
     $details = [$token_id , $token , $new_user_id,
      implode(',' , $privileges) , $new_user_email ];
      if(user::add_slot($details)){
      $url = env('base_url') . "/admin/users/validate?token_id=".
       $token_id . "&token=" . $token;
       $email_message = view::render('email' , ['url' => $url , 'message' => 'create account']);
       if(!mail::to($new_user_email , 'email to create account' , $email_message , true)){
         $this->res->output(
           view::render('token_error' , ["error" => "oops! sorry something went wrong"])
         );
       } else {
         session('top_message' , 'user slot has been created for the user and an email has been sent to the
         user email address');
         session('top_type' , 'success');
         //send notification
         $current_user_details = user::fetch_user($user_id);
         $notification = $current_user_details["name"] . " created a user slot";
         $details = [DB::generate_unique_id('notification') , $notification];
         user::notify($details);
         redirect::to(env('base_url') . '/admin/dashboard');
       }
       }
      }
      public function verify_slot(){
        $get_data = $this->req->get();
        $token_id = (isset($get_data['token_id'])) ? $get_data["token_id"] : " ";
        $token = (isset($get_data['token'])) ? $get_data["token"] : " ";
        $data = user::validate_slot($token , $token_id);
        if($data !== false){
         $user_email = $data[0];
         $password = random_hash();
         $password_hash = password_hash($password , PASSWORD_BCRYPT , ['cost' => 11]);
         $user_id = $data[1];
         $details = [$user_id , 'john' , 'doe' , $password_hash , $user_email];
       if(user::create($details)){
         $privileges = $data[2];
         user::grant_privilege($user_id , $privileges);
         $email_message =  view::render('password_email_send' , ["email" => $user_email ,
         'password' => $password , "url" => env('base_url') . '/admin/login' , 'message' => 'click here to login' ]);
         if(mail::to($user_email, 'user login details' , $email_message , true)){
         $this->res->output(
            view::render('success_message' , ["message" => 'the token has been validated successfully , your login details
            has been sent to ' . $user_email ])
         );
      } else {
         $this->res->output(
            view::render('token_error' , ["error" => 'oops! sorry email could not be sent something went wrong' , "base-url" => env("base_url")])
         );
      }
        } 
      } else {
         $message = "Invalid token , this can be that the
          token has expired or it does not exist";
         $this->res->output(
            view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
         );
      }
       }
       public function logout(){
         delete_cookie('PHPSESSID');
         delete_cookie('x' , 'admin/');
         delete_cookie('x_id' , 'admin/');
           session_unset();
          session_terminate();
         redirect::to(env('base_url') . '/admin/login');
       }
         public function revoke_user_form(){
            $user_id = $this->validate_user();
            $top = $this->get_top($user_id);
            $this->res->setContentType('text/html');
            if(!(user::is_admin($user_id) || user::check_privilege($user_id , 'special'))){
               $this->res->output(
                  view::render('error' , ["notif" => $top[1] , "error" => "Error: you don't have enough permission to carry out the action",
                  "base-url" => env("base_url")])
                  );
                  return;
            }
            $csrf = $this->middleware->csrf();
            $csrf_token = $csrf->generate_token();
            $nonce = random_hash('sha1');
            $pswErr = get_session('pswErr');
            $privErr = get_session('privErr');
            $user_id_to_revoke = $this->req->getParams()[0];
            $this->res->setContentType('text/html');
            $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
            $this->res->output(
            view::render('revoke' ,  ['notif' => $top[1] , 'csrf' => $csrf_token ,
             'user_id' => $user_id_to_revoke , 'pswErr' => $pswErr , 'privErr' => $privErr , "base-url" => env("base_url")])
            );
            session('pswErr' ,'');
            session('privErr' , '');
               
         }
    public function revoke_user(){
      $user_id = $this->validate_user();
      $csrf = $this->middleware->csrf();
      $post_data = $this->req->post();
      if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
        $this->res->setStatus(400 , 'invalid request');
       $message = "error bad request, this could be that the request has 
        expired , refresh the page and do not confirm form resubmission";
       $this->res->output(
          view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
       );
        return ;
       }
       $user_id_to_revoke = $this->req->getParams()[0];
       $admin_password = html_format($post_data["password"]);
       $privileges = (isset($post_data["privilege"])) ? $post_data["privilege"] : [];
       $pswOk = $privOk = false;
       $pswErr = $privErr = "";
         if(count($privileges) < 1){
            $privErr = "*mark atleast one privilege to revoke from this user";
          } else {
            $privErr = "";
            $privOk = true;
          }
          if(empty($admin_password)){
            $pswErr = "*password field can not  be empty";
          }
           else if(!user::validate_password($admin_password , $user_id)){
            $pswErr = "*incorrect password";
          } else {
            $pswOk = true;
            $pswErr = "";
          }
          if(!($pswOk && $privOk)){
            session('privErr' , $privErr);
            session('pswErr' , $pswErr);
            redirect::to($user_id_to_revoke);
            return;
        }
        if($user_id_to_revoke == $user_id){
         $msg = 'you can not revoke privilege from yourself grant someone 
         special or all privilege and let the person revoke your privileges';
         session('top_message' , $msg);
         session('top_type' , 'error');
          redirect::to(env('base_url') . '/admin/dashboard');
          return;
        }
        $privileges = implode(',' , $privileges);
       
        //the details of the user to revoke privilege from
        $user_details = user::fetch_user($user_id_to_revoke);
       if(user::revoke_privilege($user_id_to_revoke , $privileges)){
         $msg = 'you have successfully revoke ' . $privileges . 
         " privilege from " . $user_details["name"];
         session('top_message' , $msg);
         session('top_type' , 'success');
         //send notification
         $current_user_details = user::fetch_user($user_id);
         $notification = $current_user_details["name"] . " revoked " .
          $privileges . " from " . $user_details["name"];
         $details = [DB::generate_unique_id('notification') , $notification];
         user::notify($details);
         redirect::to(env('base_url') . '/admin/dashboard');
         } else {
            $msg = 'oops! something went wrong privilege could not be revoked from ' .  $user_details["name"];
         session('top_message' , $msg);
         session('top_type' , 'error');
          redirect::to(env('base_url') . '/admin/dashboard');
         }
       }

         public function undo_revoke_form(){
            $user_id = $this->validate_user();
            $top = $this->get_top($user_id);
            $this->res->setContentType('text/html');
            if(!(user::is_admin($user_id) || user::check_privilege($user_id , 'special'))){
               $this->res->output(
                  view::render('error' , ["notif" => $top[1] , "error" => "Error: you don't have enough permission to carry out the action" ,
                  "base-url" => env("base_url")])
                  );
                  return;
            }
            $csrf = $this->middleware->csrf();
            $csrf_token = $csrf->generate_token();
            $nonce = random_hash('sha1');
            $pswErr = get_session('pswErr');
            $privErr = get_session('privErr');
            $user_id_to_undo_revoke = $this->req->getParams()[0];
            $this->res->setContentType('text/html');
            $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
            $this->res->output(
            view::render('undo_revoke' ,  ['notif' => $top[1] , 'csrf' => $csrf_token ,
             'user_id' => $user_id_to_undo_revoke , 'pswErr' => $pswErr , 
             'privErr' => $privErr , "base-url" => env("base_url")])
            );
            session('pswErr' ,'');
            session('privErr' , '');
               
         }

         public function undo_revoke(){
            $user_id = $this->validate_user();
            $csrf = $this->middleware->csrf();
            $post_data = $this->req->post();
            if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
              $this->res->setStatus(400 , 'invalid request');
             $message = "error bad request, this could be that the request has 
              expired , refresh the page and do not confirm form resubmission";
             $this->res->output(
                view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
             );
              return ;
             }
             $user_id_to_undo_revoke = $this->req->getParams()[0];
             $admin_password = html_format($post_data["password"]);
             $privileges = (isset($post_data["privilege"])) ? $post_data["privilege"] : [];
             $pswOk = $privOk = false;
             $pswErr = $privErr = "";
               if(count($privileges) < 1){
                  $privErr = "*mark atleast one privilege to grant to this user or undo revoke on";
                } else {
                  $privErr = "";
                  $privOk = true;
                }
                if(empty($admin_password)){
                  $pswErr = "*password field can not  be empty";
                }
                else if(!user::validate_password($admin_password , $user_id)){
                  $pswErr = "*incorrect password";
                } else {
                  $pswOk = true;
                  $pswErr = "";
                }
                if(!($pswOk && $privOk)){
                  session('privErr' , $privErr);
                  session('pswErr' , $pswErr);
                  redirect::to($user_id_to_undo_revoke);
                  return;
              }
              if($user_id_to_undo_revoke == $user_id){
               $msg = 'you can not grant yourself or undo your revoked privileges ask
                someone with special privilege or all privilege to do that for you';
               session('top_message' , $msg);
               session('top_type' , 'error');
                redirect::to(env('base_url') . '/admin/dashboard');
                return;
              }
              $privileges = implode(',' , $privileges);
                //the details of the user to undo_revoke privilege for
          $user_details = user::fetch_user($user_id_to_undo_revoke);
        if(user::undo_revoke_privilege($user_id_to_undo_revoke , $privileges)){
          $msg = 'you have successfully granted ' . $privileges . 
          " privileges  to " . $user_details["name"];
          session('top_message' , $msg);
          session('top_type' , 'success');
          //send notification
          $current_user_details = user::fetch_user($user_id);
          $notification = $current_user_details["name"] . " granted " .
           $privileges . " privileges to " . $user_details["name"];
          $details = [DB::generate_unique_id('notification') , $notification];
          user::notify($details);
          redirect::to(env('base_url') . '/admin/dashboard');
          } else {
             $msg = 'oops! something went wrong privileges could not be granted to ' .  $user_details["name"];
          session('top_message' , $msg);
          session('top_type' , 'error');
           redirect::to(env('base_url') . '/admin/dashboard');
          }
             
         }
         public function delete_user_form(){

         }

         public function delete_user(){

         }

        public function change_password_form(){
         $user_id = $this->validate_user();
         $top = $this->get_top($user_id);
         $this->res->setContentType('text/html');
         $csrf = $this->middleware->csrf();
         $csrf_token = $csrf->generate_token();
         $nonce = random_hash('sha1');
         $oldPswErr = get_session('pswErr');
         $newPswErr = get_session('newPswErr');
         $confirmPswErr = get_session('confirmPswErr');
         $this->res->setContentType('text/html');
         $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
         $this->res->output(
         view::render('change_password' ,  ['notif' => $top[1] , 'csrf' => $csrf_token , 
         'pswErr' => $oldPswErr , 'newPswErr' => $newPswErr , 
         'confirmPswErr' => $confirmPswErr, "base-url" => env("base_url") ])
         );
         session('pswErr' ,'');
         session('newPswErr' , '');
         session('confirmPswErr' , '');
           
        }
        public function change_password(){
         $user_id = $this->validate_user();
         $csrf = $this->middleware->csrf();
         $post_data = $this->req->post();
         if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
           $this->res->setStatus(400 , 'invalid request');
          $message = "error bad request, this could be that the request has 
           expired , refresh the page and do not confirm form resubmission";
          $this->res->output(
             view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
          );
           return ;
          }
          $old_password = html_format($post_data["old_password"]);
          $new_password = html_format($post_data["new_password"]);
          $confirm_password = html_format($post_data["confirm_password"]);
          $pswOk = $newPswOk = $confirmPswOk =  false;
          $pswErr = $newPswErr = $confirmPswErr = "";
          $password_pattern = "/(?=^.{8,}$)(?=.*\d)(?=.*[!@#$%^&*]+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/";
          if(empty($old_password)){
            $pswErr = "*password field can not be empty";
          } else if(!user::validate_password($old_password , $user_id)){
            $pswErr = "*incorrect password";
          } else {
            $pswErr = "";
            $pswOk = true;
          }
          if(empty($new_password)){
            $newPswErr = "*new password field can not be empty";
          } else if(!preg_match($password_pattern , $new_password)){
            $newPswErr = "*your new password should contain atleast 
            1 uppercase alphabet , 1 lower case alphabet , 1 numerals and
             one special character and it must be not be less than 8 characters";
          } else if($old_password === $new_password){
             $newPswErr = "*your new password can not be your old password";
          }
           else {
            $newPswErr = "";
            $newPswOk = true;
          }
          if(empty($confirm_password)){
            $confirmPswErr = "*your confirm password field can not be empty";
          } else if(!($confirm_password === $new_password)){
            $confirmPswErr = "*password mismatch";
          }
           else {
            $confirmPswErr = "";
           $confirmPswOk = true;
         }
         if(!($pswOk && $newPswOk && $confirmPswOk)){
            session('pswErr' , $pswErr);
            session('newPswErr' ,  $newPswErr);
            session('confirmPswErr' , $confirmPswErr);
            redirect::to(env('base_url') . '/admin/user/password/change');
            return;
         }
        
         if(user::change_password($user_id , $new_password)){
            $msg = 'your password has been changed successfully';
            session('top_message' , $msg);
            session('top_type' , 'success');
            redirect::to(env('base_url') . '/admin/dashboard');
         } else {
            $msg = 'oops! password could not be changed something went wrong';
            session('top_message' , $msg);
            session('top_type' , 'error');
            redirect::to(env('base_url') . '/admin/dashboard');
         }
        }

        public function change_email_form(){
         $user_id = $this->validate_user();
         $top = $this->get_top($user_id);
         $this->res->setContentType('text/html');
         $csrf = $this->middleware->csrf();
         $csrf_token = $csrf->generate_token();
         $nonce = random_hash('sha1');
         $pswErr = get_session('pswErr');
         $emailErr = get_session('emailErr');
         $this->res->setContentType('text/html');
         $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
         $this->res->output(
         view::render('change_email' ,  ['notif' => $top[1] , 'csrf' => $csrf_token , 
         'pswErr' => $pswErr , 'emailErr' => $emailErr , "base-url" => env("base_url")])
         );
         session('pswErr' ,'');
         session('emailErr' , '');
           
        }
        public function change_email(){
         $user_id = $this->validate_user();
         $csrf = $this->middleware->csrf();
         $post_data = $this->req->post();
         if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
           $this->res->setStatus(400 , 'invalid request');
          $message = "error bad request, this could be that the request has 
           expired , refresh the page and do not confirm form resubmission";
          $this->res->output(
             view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
          );
           return ;
          }
          $password = html_format($post_data["password"]);
          $email = html_format($post_data["email"]);
          $pswOk = $emailOk =  false;
          $pswErr = $emailErr = "";
            if(empty($password)){
            $pswErr = "*password field can not be empty";
          } else if(!user::validate_password($password , $user_id)){
            $pswErr = "*incorrect password";
          } else {
            $pswErr = "";
            $pswOk = true;
          }
          if(empty($email)){
            $emailErr = "*your email field can not be empty";
          } else if(!filter_var($email , FILTER_VALIDATE_EMAIL)){
            $emailErr = "*invalid email , please enter a valid email";
          }
           else {
            $emailErr = "";
           $emailOk = true;
         }
         if(!($pswOk && $emailOk)){
            session('pswErr' , $pswErr);
            session('emailErr' ,  $emailErr);
             redirect::to(env('base_url') . '/admin/user/email/change');
            return;
         }
        
         if(user::change_email($user_id , $email)){
            $msg = 'your email has been changed successfully';
            session('top_message' , $msg);
            session('top_type' , 'success');
            redirect::to(env('base_url') . '/admin/dashboard');
         } else {
            $msg = 'oops! email could not be changed something went wrong';
            session('top_message' , $msg);
            session('top_type' , 'error');
            redirect::to(env('base_url') . '/admin/dashboard');
         }
        }
        public function change_name_form(){
         $user_id = $this->validate_user();
         $top = $this->get_top($user_id);
         $this->res->setContentType('text/html');
         $csrf = $this->middleware->csrf();
         $csrf_token = $csrf->generate_token();
         $nonce = random_hash('sha1');
         $pswErr = get_session('pswErr');
         $fnameErr = get_session('fnameErr');
         $lnameErr = get_session('lnameErr');
         $this->res->setContentType('text/html');
         $this->res->setHeaders(["Content-Security-Policy: script-src 'nonce-$nonce'"]);
         $this->res->output(
         view::render('change_name' ,  ['notif' => $top[1] , 'csrf' => $csrf_token , 
         'pswErr' => $pswErr , 'fnameErr' => $fnameErr , 'lnameErr' => $lnameErr , "base-url" => env("base_url") ])
         );
         session('pswErr' ,'');
         session('fnameErr' , '');
         session('lnameErr' , '');
           
        }
        public function change_name(){
         $user_id = $this->validate_user();
         $csrf = $this->middleware->csrf();
         $post_data = $this->req->post();
         if(!(isset($post_data['csrf_token']) && $csrf->verify($post_data['csrf_token']))){
           $this->res->setStatus(400 , 'invalid request');
          $message = "error bad request, this could be that the request has 
           expired , refresh the page and do not confirm form resubmission";
          $this->res->output(
             view::render('token_error' , ["error" => $message , "base-url" => env("base_url")])
          );
           return ;
          }
          $password = html_format($post_data["password"]);
          $fname = html_format($post_data["fname"]);
          $lname = html_format($post_data["lname"]);
          $pswOk = $fnameOk = $lnameOk =  false;
          $pswErr = $fnameErr = $lnameErr = "";
            if(empty($password)){
            $pswErr = "*password field can not be empty";
          } else if(!user::validate_password($password , $user_id)){
            $pswErr = "*incorrect password";
          } else {
            $pswErr = "";
            $pswOk = true;
          }
          if(empty($fname)){
            $fnameErr = "*first name can not be empty";
          } else if(!preg_match("/^[a-zA-Z]*$/", $fname)){ 
            $fnameErr = "*first name can only contain the engish alhabets no whitespace allowed too"; } 
           else { 
            $fnameOk = true; 
         }
           if(empty($lname)){
            $fnameErr = "*last name can not be empty";
          } else if(!preg_match("/^[a-zA-Z]*$/", $lname)){ 
            $lnameErr = "*last name can only contain the engish alhabets no whitespace allowed too"; } 
           else {
             $lnameOk = true;
             }
         if(!($pswOk && $fnameOk && $lnameOk)){
            session('pswErr' , $pswErr);
            session('fnameErr' ,  $fnameErr);
            session('lnameErr' ,  $lnameErr);
             redirect::to(env('base_url') . '/admin/user/email/change');
            return;
         }
        
         if(user::change_name($user_id , [$fname , $lname])){
            $msg = 'your name has been changed to ' . $fname . ' ' . $lname . ' successfully';
            session('top_message' , $msg);
            session('top_type' , 'success');
            redirect::to(env('base_url') . '/admin/dashboard');
         } else {
            $msg = 'oops! email could not be changed something went wrong';
            session('top_message' , $msg);
            session('top_type' , 'error');
            redirect::to(env('base_url') . '/admin/dashboard');
         }
        }

   }
   

?>