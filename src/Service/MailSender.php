<?php


namespace App\Service;


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