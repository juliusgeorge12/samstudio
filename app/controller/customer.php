<?php
  namespace app\controller;
  use app\controller\controller as baseController;
use Facades\redirect;
use Facades\view;

use function support\helper\env;
use function support\helper\get_session;
use function support\helper\html_format;
use function support\helper\session;

class customer extends baseController {

          public function home(){

                $this->res->output(
                        view::render('com' , ["base-url" => env("base_url")])
                );
          }

          public function service(){

                $this->res->output(
                        view::render('book')
                );
          }
          public function contact(){
            $nameErr = get_session('nameErr');
            $emailErr = get_session('emailErr');
            $subjectErr = get_session('subjectErr');
            $messageErr = get_session('messageErr');
            $get_data = $this->req->get();
            $subject = (isset($get_data["subject"])) ? html_format(urldecode($get_data["subject"])) : "";
            $message = (isset($get_data["message"])) ? html_format(urldecode($get_data["message"])) : "";
            if(isset($get_data["subject"]) && isset($get_data["message"])){
                  $param = "?subject=". $subject . "&message=" . $message;
               } else { $param = ""; }
            $data = ["nameErr" => $nameErr , "emailErr" => $emailErr , 
              "subjectErr" => $subjectErr , "messageErr" => $messageErr ,
               "message" => $message , "subject" => $subject , "param" => $param , "base-url" => env("base_url")];

                  $this->res->output(
                          view::render('contact' , $data)
                  );
          }
          public function contact_form(){
                $form_data = $this->req->post();
                $emailOk  = $nameOk = $subjectOk = $messageOk = false;
                $nameErr = $emaileErr = $subjectErr = $messageErr = "";
                if(!(isset($form_data["name"]) && empty($form_data['name']))){
                   $name = html_format($form_data["name"]);
                   $nameOk = true;
                } else {
                  $nameOk = false;
                  $nameErr = "please enter  your name";
                }

         if(!(isset($form_data["email"]) && empty($form_data["email"]))){
              $email = html_format($form_data["email"]);
              $emailOk = true;
                }
                else {
                  $emailOk = false;
                  $emaileErr = "please enter your email address";
                }
        if(!(isset($form_data["subject"]) && empty($form_data['subject']))){
              $subject = html_format($form_data["subject"]);
              $subjectOk = true;
          } else {
                $subjectOk = false;
                $subjectErr = "sorry subject can not be empty";
          }

          if(!(isset($form_data["message"]) && empty($form_data['message']))){
                $message = html_format($form_data["message"]);
                $messageOk = true;
            } else {
                  $messageOk = false;
                  $messageErr = "sorry message can not be empty";
            }
        if($messageOk && $subjectOk && $nameOk && $emailOk){
            $this->res->setStatus(200);
            $this->res->output(
         view::render('email_recieved')
         );
        } else {
                session('nameErr' , $nameErr);
                session('emailErr' , $emaileErr);
                session('subjectErr' , $subjectErr);
                session('messageErr' , $messageErr); 
                $get_data = $this->req->get();
                $subject = (isset($get_data["subject"])) ? html_format(urldecode($get_data["subject"])) : "";
                $message = (isset($get_data["message"])) ? html_format(urldecode($get_data["message"])) : "";
                if(isset($get_data["subject"]) && isset($get_data["message"])){
                      $param = "?subject=". $subject . "&message=" . $message;
                   } else { $param = ""; }
                   redirect::to('contact' . $param);
             }
          }

          public function book(){
            $this->res->output(
                  view::render('secondform' , ["base-url" => env("base_url")])
            );
          }
  }


?>