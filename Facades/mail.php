<?php
    namespace Facades;

use app\services\mailer\mail as MailerMail;
use support\container\container;

 /**
  * @method static public to(string $email , string $suject, string $message , bool $is_html)
  */

class mail extends facade {

            public static function getFacadeInstance()
            {
                    return container::getInstance(MailerMail::class);
            }
    }