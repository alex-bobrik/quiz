<?php


namespace App\Service;


use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunSmtpTransport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MailSender
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(string $title, string $to, $body)
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to($to)
            ->subject($title)
            ->text($body)
            ->html($body);

        $this->mailer->send($email);
    }
}