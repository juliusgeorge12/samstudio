<?php
  namespace app\services\mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

use function support\helper\env;

class mail {

    /**
     * @property private $mail the phpmailer object
     * @property private $host the smtp host
     * @property private $port the smtp port
     * @property private $username the smtp user name
     * @property private $password the smtp passord
     */
     private $mailer;
     private $host;
     private $port;
     private $username;
     private $password;
     private $from;

     public function __construct()
     {
             $this->mailer = new PHPMailer();
             $this->host = env('SMTP_HOST');
             $this->port = env('SMTP_PORT');
             $this->username = env('SMTP_USERNAME');
             $this->password = env('SMTP_PASSWORD');
             $this->from = env('ADMIN_EMAIL_ADDRESS');
             $this->setMail();
     }
     private function setMail(){
       $this->mailer->isSMTP();
       if(env('smtp_debug') === 'true'){
        $this->mailer->SMTPDebug = 1;
       }
        $this->mailer->Host = $this->host;
        $this->mailer->Port = $this->port;
        $this->mailer->setFrom($this->from, $this->username);
       if(env('smtp_secure') == 'true'){
       $this->mailer->SMTPSecure = env('email_encryption');
        }
        $this->mailer->Username = $this->username;
        $this->mailer->Password = $this->password;
        if(env('smtp_authenticate') === 'true'){
        $this->mailer->SMTPAuth = true;
        } else {
                $this->mailer->SMTPAuth = false;
        }
     }
     private function prepare_email(string $email , string $subject , string $body , bool $is_html = false){
        $this->mailer->addAddress($email);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $body;
        if($is_html){
                $this->mailer->isHTML(true);
        }
      }

   private function sendEmail(){
                return $this->mailer->send();
         }
  
 public function to(string $email , string $subject , string $body , bool $is_html = false){
                  $this->prepare_email($email , $subject , $body , $is_html);
                  return $this->sendEmail();
          }

  }