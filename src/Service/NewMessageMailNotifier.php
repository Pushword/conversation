<?php

namespace Pushword\Conversation\Service;

use DateInterval;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Pushword\Conversation\Entity\MessageInterface;
use Pushword\Core\Component\App\AppPool;
use Pushword\Core\Utils\LastTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewMessageMailNotifier
{
    private \Symfony\Component\Mailer\MailerInterface $mailer;

    private \Pushword\Core\Component\App\AppPool $apps;

    private \Doctrine\ORM\EntityManagerInterface $em;

    private \Symfony\Contracts\Translation\TranslatorInterface $translator;

    private string $emailTo;

    private string $emailFrom;

    private string $appName;

    private string $projectDir;

    private string $interval;

    /**
     * @var class-string<MessageInterface>
     */
    private string $message;

    private string $host;

    /**
     .
     *
     * @param class-string<MessageInterface> $message Entity
     */
    public function __construct(
        string $message,
        MailerInterface $mailer,
        AppPool $appPool,
        string $projectDir,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->apps = $appPool;
        $this->emailTo = \strval($this->apps->get()->get('conversation_notification_email_to'));
        $this->emailFrom = \strval($this->apps->get()->get('conversation_notification_email_from'));
        $this->interval = \strval($this->apps->get()->get('conversation_notification_interval'));
        $this->appName = \strval($this->apps->get()->get('name'));
        $this->host = $this->apps->get()->getMainHost();
        $this->projectDir = $projectDir;
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->message = $message;
    }

    /**
     * @return MessageInterface[]
     */
    protected function getMessagesPostedSince(DateTimeInterface $datetime)
    {
        $query = 'SELECT m FROM '.$this->message.' m WHERE m.host = :host AND m.createdAt > :lastNotificationTime';
        $query = $this->em->createQuery($query)
            ->setParameter('lastNotificationTime', $datetime, 'datetime')
            ->setParameter('host', $this->host);

        return $query->getResult(); // @phpstan-ignore-line
    }

    /**
     * @return bool|void
     */
    public function send()
    {
        if ('' === $this->emailTo) {
            return;
        }

        $lastTime = new LastTime($this->projectDir.'/var/lastNewMessageNotification');
        if (! $lastTime->wasRunSince(new DateInterval($this->interval))) {
            return;
        }

        if (($since = $lastTime->get($this->interval)) === null) {
            throw new LogicException();
        }

        $messages = $this->getMessagesPostedSince($since);
        if ([] === $messages) {
            return;
        }

        $templatedEmail = (new TemplatedEmail())
            ->subject(
                $this->translator->trans(
                    'admin.conversation.notification.title.'.(\count($messages) > 1 ? 'plural' : 'singular'),
                    ['%appName%' => $this->appName]
                )
            )
            ->from($this->emailFrom)
            ->to($this->emailTo)
            ->htmlTemplate('@PushwordConversation/notification.html.twig')
            ->context([
                'appName' => $this->appName,
                'messages' => $messages,
            ]);

        $lastTime->set();
        $this->mailer->send($templatedEmail);

        return true;
    }
}
