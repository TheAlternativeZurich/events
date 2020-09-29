<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\User;
use App\Service\Interfaces\EmailServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailService implements EmailServiceInterface
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    private $request;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $mailerFromEmail;

    /**
     * EmailService constructor.
     */
    public function __construct(RequestStack $requestStack, ManagerRegistry $managerRegistry, TranslatorInterface $translator, LoggerInterface $logger, MailerInterface $mailer, string $mailerFromEmail)
    {
        $this->manager = $managerRegistry->getManager();
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->mailerFromEmail = $mailerFromEmail;
    }

    /**
     * @return false
     *
     * @throws Exception
     */
    public function sendAuthenticateLink(User $user): bool
    {
        $user->generateAuthenticationHash();

        $message = (new TemplatedEmail())
            ->subject($this->translator->trans('email.send_authentication_link.subject', ['%page%' => $this->request->getHttpHost()], 'email'))
            ->from($this->mailerFromEmail)
            ->to($user->getEmail());

        //construct plain body
        $message->textTemplate('email/authentication_link.txt.twig');
        $message->htmlTemplate('email/authentication_link.html.twig');

        $message->context(['user' => $user]);

        try {
            $this->mailer->send($message);

            $this->manager->persist($user);
            $this->manager->flush();

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('email send failed', ['exception' => $exception]);

            return false;
        }
    }
}
