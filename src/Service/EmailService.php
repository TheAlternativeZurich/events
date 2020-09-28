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

use App\Entity\Email;
use App\Entity\User;
use App\Enum\EmailType;
use App\Service\Email\SendService;
use App\Service\Interfaces\EmailServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var SendService
     */
    private $sendService;

    /**
     * @var Environment
     */
    private $twig;

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
     *
     * @param ObjectManager $regsitry
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger, RequestStack $request, UrlGeneratorInterface $urlGenerator, ManagerRegistry $registry, SendService $sendService, Environment $twig, MailerInterface $mailer, string $mailerFromEmail)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->request = $request;
        $this->urlGenerator = $urlGenerator;
        $this->manager = $registry->getManager();
        $this->sendService = $sendService;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->mailerFromEmail = $mailerFromEmail;
    }

    /**
     * @return false
     *
     * @throws Exception
     */
    public function sendRegisterConfirmLink(User $user): bool
    {
        $user->generateAuthenticationHash();

        // construct email
        $email = new Email();
        $email->setEmailType(EmailType::ACTION_EMAIL);
        $email->setReceiver($user->getEmail());
        $email->setSubject($this->translator->trans('register.email.subject', ['%page%' => $this->request->getCurrentRequest()->getHttpHost()], 'security'));
        $email->setBody($this->translator->trans('register.email.body', [], 'security'));
        $email->setActionText($this->translator->trans('register.email.action_text', [], 'security'));
        $email->setActionLink($this->urlGenerator->generate('register_confirm', ['authenticationHash' => $user->getAuthenticationHash()], UrlGeneratorInterface::ABSOLUTE_URL));

        if ($this->sendEmail($email)) {
            $this->manager->persist($user);

            return true;
        }

        return false;
    }

    private function sendEmail(Email $email): bool
    {
        $message = (new TemplatedEmail())
            ->subject($email->getSubject())
            ->from($this->mailerFromEmail)
            ->to($email->getReceiver());

        //set reply to
        if ($email->getSenderEmail()) {
            $message->addReplyTo($email->getSenderEmail());
        } else {
            $message->addReplyTo($this->mailerFromEmail);
        }

        //construct plain body
        $message->textTemplate('email/content.txt.twig');

        //construct html body if applicable
        if (EmailType::PLAIN_EMAIL !== $email->getEmailType()) {
            $message->htmlTemplate('email/email_template.html.twig');
        }

        $email->generateIdentifier();

        $context = $this->getTemplateContext($email);
        $message->context($context);

        try {
            $this->mailer->send($message);

            $email->confirmSent();

            $this->manager->persist($email);
            $this->manager->flush();

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('email send failed', ['exception' => $exception]);

            return false;
        }
    }

    public function sendRecoverConfirmLink(User $user): bool
    {
        $user->generateAuthenticationHash();

        $email = new Email();
        $email->setEmailType(EmailType::ACTION_EMAIL);
        $email->setReceiver($user->getEmail());
        $email->setSubject($this->translator->trans('recover.email.reset_password.subject', ['%page%' => $this->request->getCurrentRequest()->getHttpHost()], 'security'));
        $email->setBody($this->translator->trans('recover.email.reset_password.message', [], 'security'));
        $email->setActionText($this->translator->trans('recover.email.reset_password.action_text', [], 'security'));
        $email->setActionLink($this->urlGenerator->generate('recover_confirm', ['authenticationHash' => $user->getAuthenticationHash()], UrlGeneratorInterface::ABSOLUTE_URL));

        if ($this->sendEmail($email)) {
            $this->manager->persist($user);

            return true;
        }

        return false;
    }

    public function getTemplateContext(Email $email): array
    {
        $context = ['body' => $email->getBody(), 'identifier' => $email->getIdentifier()];
        if (EmailType::ACTION_EMAIL === $email->getEmailType()) {
            $context['action_text'] = $email->getActionText();
            $context['action_link'] = $email->getActionLink();
        }

        if ($email->getSenderName()) {
            $context['sender_name'] = $email->getSenderName();
            $context['sender_email'] = $email->getSenderEmail();
        }

        return $context;
    }
}
