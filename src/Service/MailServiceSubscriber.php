<?php

namespace App\Service;

use App\Event\EmailEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailServiceSubscriber implements EventSubscriberInterface
{
    const SENDER = 'no-reply@tristan-lefevre.fr';

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    public static function getSubscribedEvents() : array
    {
        return [
            'register.newEmail' => [
                ['sendValidationMail',0],
            ],
            'changeEmail.update' => [
                ['sendEmailUpdateMail', 0],
            ],
            'login.emailRecall' => [
                ['sendRecallValidationMail',0]
            ],
            'login.changePassword' => [
                ['sendChangePasswordMail',0]
            ]
        ];
    }

    /**
     * send email validation email
     * @param EmailEvent $event event for email data
     */
    public function sendValidationMail(EmailEvent $event) : void
    {
        $mail = new TemplatedEmail();
        $mail->from(self::SENDER)
        ->to($event->getEmail())
        ->subject("Confirmer l'adresse mail")
        ->htmlTemplate("email/validEmail.html.twig")
        ->context([
            'link' => 'check_email',
            'param' => ['token' => $event->getToken()]
        ]);
        $this->mailer->send($mail);
    }
    
    /**
     * send email update validation email
     * @param EmailEvent $event event for email data
     */
    public function sendEmailUpdateMail(EmailEvent $event) : void
    {
        $mail = new TemplatedEmail();
        $mail->from(self::SENDER)
        ->to($event->getEmail())
        ->subject("Confirmer l'adresse mail")
        ->htmlTemplate("email/emailUpdate.html.twig")
        ->context([
            'link' => 'check_email',
            'param' => ['token' => $event->getToken()]
        ]);
        $this->mailer->send($mail);
    }

    /**
     * send email validation recall email
     * @param EmailEvent $event event for email data
     */
    public function sendRecallValidationMail(EmailEvent $event) : void
    {
        $mail = new TemplatedEmail();
        $mail->from(self::SENDER)
        ->to($event->getEmail())
        ->subject("Confirmer l'adresse mail")
        ->htmlTemplate("email/validEmailRecall.html.twig")
        ->context([
            'link' => 'check_email',
            'param' => ['token' => $event->getToken()]
        ]);
        $this->mailer->send($mail);
    }

    /**
     * send change password email
     * @param EmailEvent $event event for email data
     */
    public function sendChangePasswordMail(EmailEvent $event) : void
    {
        $mail = new TemplatedEmail();
        $mail->from(self::SENDER)
        ->to($event->getEmail())
        ->subject("Confirmer l'adresse mail")
        ->htmlTemplate("email/validEmailRecall.html.twig")
        ->context([
            'link' => 'change_password',
            'param' => ['token' => $event->getToken()]
        ]);
        $this->mailer->send($mail);
    }
}
