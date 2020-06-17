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
            ->from('quiz.mailer@quizzes.com')
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($title)
            ->html($body);

        $this->mailer->send($email);

    }
}