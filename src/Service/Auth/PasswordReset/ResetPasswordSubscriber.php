<?php

declare(strict_types=1);

namespace App\Service\Auth\PasswordReset;

use App\Exception\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordSubscriber implements EventSubscriberInterface
{
    private const RESET_PASSWORD_EMAIL_ALREADY_SENT_MESSAGE = 'На вашу почту уже выслано письмо с инструкциями по восстановлению пароля, воспользуйтесь им';

    private MailerInterface $mailer;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private UrlGeneratorInterface $urlGenerator;
    private ContainerBagInterface $containerBag;

    public function __construct(
        MailerInterface $mailer,
        ResetPasswordHelperInterface $resetPasswordHelper,
        UrlGeneratorInterface $urlGenerator,
        ContainerBagInterface $containerBag
    ) {
        $this->mailer = $mailer;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->urlGenerator = $urlGenerator;
        $this->containerBag = $containerBag;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserForgotPasswordEvent::class => ['sendResetPasswordLink']
        ];
    }

    /**
     * @throws ResetPasswordExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ValidationException
     */
    public function sendResetPasswordLink(UserForgotPasswordEvent $event): void
    {
        $user = $event->getUser();

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (TooManyPasswordRequestsException $e) {
            throw new ValidationException(self::RESET_PASSWORD_EMAIL_ALREADY_SENT_MESSAGE);
        }

        $resetUrl = $this->urlGenerator->generate(
            'api-v1-reset-password',
            ['token' => $resetToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = new TemplatedEmail();
        $email->from($this->containerBag->get('app.email.address'))
            ->to($user->getEmail())
            ->subject('Восстановление пароля')
            ->htmlTemplate('password_reset/reset_password_email.html.twig')
            ->context([
                'expired_at' => $resetToken->getExpiresAt()->format('d.m.Y H:i'),
                'reset_url' => $resetUrl,
            ]);

        $this->mailer->send($email);
    }
}