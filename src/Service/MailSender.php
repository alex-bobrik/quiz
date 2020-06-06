<?php


namespace App\Service;


use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunSmtpTransport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class MailSender
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(string $title, string $to, $body)
    {
        $message = (new \Swift_Message($title))
            ->setFrom('example@example.com')
            ->setTo($to)
            ->setBody($body);

        $this->mailer->send($message);

    }
}