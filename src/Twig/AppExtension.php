<?php

namespace Pushword\Conversation\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Pushword\Conversation\Entity\Message;
use Pushword\Conversation\Repository\MessageRepository;
use Pushword\Core\Component\App\AppConfig;
use Pushword\Core\Component\App\AppPool;
use Pushword\Core\Entity\Page;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private readonly AppConfig $app;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppPool $apps,
        private readonly RouterInterface $router
    ) {
        $this->app = $apps->get();
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('showConversation', $this->showConversation(...), ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('conversation', $this->getConversationRoute(...)),
        ];
    }

    public function getConversationRoute(string $type): string
    {
        $page = $this->apps->getCurrentPage();
        if (! $page instanceof Page) {
            throw new \Exception('A page must be defined...');
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
        int $limit = 0,
        string $view = '/conversation/messages_list.html.twig'
    ): string {
        /** @var MessageRepository $msgRepo */
        $msgRepo = $this->em->getRepository(Message::class);

        $messages = $msgRepo->getMessagesPublishedByReferring($referring, $orderBy, $limit);

        $view = $this->app->getView($view, '@PushwordConversation');

        return $twig->render($view, ['messages' => $messages]);
    }
}
