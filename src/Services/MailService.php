<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;

class MailService {

    protected $from;
    protected $fromName;
    protected $to;
    protected $toName;
    protected $bbc;
    protected $replayTo;
    protected $subject;
    protected $htmlTemplate;
    protected $context;

    public function __construct($from,$fromName,$to,$toName,$bbc,$replayTo,$subject,$htmlTemplate,$context)
    {   
            $this->from         = $from;
            $this->fromName     = $fromName;
            $this->to           = $to;
            $this->toName       = $toName;
            $this->bbc          = $bbc;
            $this->replayTo     = $replayTo;
            $this->subject      = $subject;
            $this->htmlTemplate = $htmlTemplate;
            $this->context      = $context;
    }


    public function sendEmail(MailerInterface $mailer)
    {
        date_default_timezone_set("Europe/Belgrade");

        $email = (new TemplatedEmail())
        ->from(new Address($this->from,$this->fromName))
        ->to(new Address($this->to,$this->toName))
        ->bcc($this->bbc)
        ->replyTo($this->replayTo)
        ->subject($this->subject)
        ->htmlTemplate($this->htmlTemplate)
        ->context(['orders' => $this->context]);

        $mailer->send($email);
    }
}