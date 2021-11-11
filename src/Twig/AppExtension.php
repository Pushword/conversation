<?php

namespace Pushword\Conversation\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pushword\Conversation\Repository\MessageRepository;
use Pushword\Core\Component\App\AppPool;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private \Doctrine\ORM\EntityManagerInterface $em;

    private \Pushword\Core\Component\App\AppConfig $app;

    private \Pushword\Core\Component\App\AppPool $apps;

    private string $messageEntity;

    private \Symfony\Component\Routing\RouterInterface $router;

    public function __construct(EntityManagerInterface $entityManager, string $messageEntity, AppPool $appPool, RouterInterface $router)
    {
        $this->em = $entityManager;
        $this->apps = $appPool;
        $this->app = $appPool->get();
        $this->messageEntity = $messageEntity;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('showConversation', [$this, 'showConversation'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('conversation', [$this, 'getConversationRoute']),
        ];
    }

    public function getConversationRoute($type)
    {
        $page = $this->apps->getCurrentPage();
        if (null === $page) {
            throw new Exception('A page must be defined...');
        }

        return $this->router->generate('pushword_conversation', [
            'type' => $type,
            'referring' => $type.'-'.$page->getRealSlug(),
            'host' => $page->getHost(),
        ]);
    }

    public function showConversation(
        Twig $twig,
        string $referring,
        string $orderBy = 'createdAt ASC',
        $limit = 0,
        string $view = '/conversation/messages_list.html.twig'
    ) {
        /** @var MessageRepository $msgRepo */
        $msgRepo = $this->em->getRepository($this->messageEntity);

        $messages = $msgRepo->getMessagesPublishedByReferring($referring, $orderBy, $limit);

        $view = $this->app->getView($view, '@PushwordConversation');

        return $twig->render($view, ['messages' => $messages]);
    }
}
