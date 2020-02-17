<?php
namespace App\Helpers;

use App\Controllers\Admin\Config;

class Mail{

  /** Send email to the reciepient
  *@param string $subject
  *@param array $to
  *@param string $body
  *@param  string $part
  *@return void
  */
  public static function sendMail(string $subject, array $to, string $body, string $part){

      // initialze config variables
      $config = new Config();
      // Create the Transport
      $transport = (new \Swift_SmtpTransport($config->SMTP_DOMAIN, $config->SMTP_PORT))
        ->setUsername($config->MAIL_USER)
        ->setPassword($config->SMTP_PASSWORD)
      ;

      // Create the Mailer using your created Transport
      $mailer = new \Swift_Mailer($transport);
      // Create a message
      $message = (new \Swift_Message($subject));

    try {
          $message->setFrom([$config->SMTP_USERNAME => $config->SENDER_NAME]);
          $message->setTo($to);
          $message->setBody($body, 'text/html');
          $message->addPart($part, 'text/plain');
      } catch (\Swift_RfcComplianceException $e) {
          echo("Address ".$email." seems invalid" . $e->getMessage());
      }

      try {

          $result = $mailer->send($message);

          return $result;

        } catch (\Swift_TransportException $Ste) {
          echo("Transport failed");
        }




      return false;
  }

}
