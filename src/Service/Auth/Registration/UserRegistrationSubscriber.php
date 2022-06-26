<?php

declare(strict_types=1);

namespace App\Service\Auth\Registration;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserRegistrationSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private ContainerBagInterface $containerBag;

    public function __construct(
        MailerInterface $mailer,
        VerifyEmailHelperInterface $verifyEmailHelper,
        ContainerBagInterface $containerBag
    ) {
        $this->mailer = $mailer;
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->containerBag = $containerBag;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserPassedRegistrationEvent::class => [
                'sendVerificationEmail'
            ]
        ];
    }

    /**
     * @TODO: async sending in queue
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendVerificationEmail(UserPassedRegistrationEvent $event): void
    {
        $user = $event->getUser();

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'api-auth-verify-email',
            $user->getUserIdentifier(),
            $user->getEmail()
        );

        $email = new TemplatedEmail();
        $email->from($this->containerBag->get('app.email.address'))
            ->to($user->getEmail())
            ->subject('Подтверждение электронной почты')
            ->htmlTemplate('registration/confirmation_email.html.twig')
            ->context(
                [
                    'signed_url' => $signatureComponents->getSignedUrl(),
                    'username' => $user->getUserIdentifier(),
                    'confirmation_email' => $user->getEmail(),
                    'expire_hours' => $signatureComponents->getExpiresAtIntervalInstance()->format('H'),
                ]
            );

        $this->mailer->send($email);
    }
}