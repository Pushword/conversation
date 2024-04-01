<?php

namespace Pushword\Conversation\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Pushword\Conversation\Form\ConversationFormInterface;
use Pushword\Conversation\Repository\MessageRepository;
use Pushword\Core\Component\App\AppPool;

use function Safe\json_encode;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;

final class ConversationFormController extends AbstractController
{
    private ?ConversationFormInterface $form = null;

    /**
     * @var string[]
     */
    private array $possibleOrigins = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly AppPool $apps,
        private readonly Twig $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RouterInterface $router,
        private readonly ManagerRegistry $doctrine,
        private readonly string $env,
        private readonly MessageRepository $messageRepo,
    ) {
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     *
     * @return class-string<ConversationFormInterface>
     */
    private function getFormManagerClass(string $type): string
    {
        $param = 'conversation_form_'.str_replace('-', '_', $type);

        if (! $this->apps->get()->has($param)) {
            throw new \Exception('`'.$type."` does'nt exist (not configured).");
        }

        $class = \strval($this->apps->get()->getStr($param));

        if (! class_exists($class)
            || ! (new \ReflectionClass($class))->implementsInterface(ConversationFormInterface::class)) {
            throw new \Exception('`'.$type."` does'nt exist.");
        }

        /** @var class-string<ConversationFormInterface> $class */

        return $class;
    }

    /**
     * Return current form manager depending on `type` (request).
     */
    private function getFormManager(string $type, Request $request): ConversationFormInterface
    {
        if (null !== $this->form) {
            return $this->form;
        }

        $class = $this->getFormManagerClass($type);

        return $this->form = new $class(
            $request,
            $this->doctrine,
            $this->tokenStorage,
            $this->formFactory,
            $this->twig,
            $this->router,
            $this->translator,
            $this->apps,
            $this->messageRepo,
        );
    }

    /**
     * @return mixed[]
     */
    private function getPossibleOrigins(Request $request): array
    {
        // $host = $request->getHost();
        $app = $this->apps->get();

        if ([] !== $this->possibleOrigins) {
            return $this->possibleOrigins;
        }

        /** @var string[]|string */
        $convertsationPossibleOrigins = $app->get('conversation_possible_origins');

        if (\is_string($convertsationPossibleOrigins)) {
            $this->possibleOrigins = explode(' ', $convertsationPossibleOrigins);
        }

        if ('dev' === $this->env) {
            $this->possibleOrigins[] = 'http://'.$request->getHost();
            $this->possibleOrigins[] = 'https://'.$request->getHost();
            $this->possibleOrigins[] = 'http://'.$request->getHost().':8000';
            $this->possibleOrigins[] = 'http://'.$request->getHost().':8001';
            $this->possibleOrigins[] = 'http://'.$request->getHost().':8002';
        }

        foreach ($app->getHosts() as $host) {
            $this->possibleOrigins[] = 'https://'.$host;
        }

        return $this->possibleOrigins;
    }

    private function initResponse(Request $request): Response
    {
        $response = new Response();

        if (! \in_array($request->headers->get('origin'), $this->getPossibleOrigins($request), true)) {
            throw new \ErrorException('origin sent is not authorized ('.($request->headers->get('origin') ?? '').') '.json_encode($this->getPossibleOrigins($request)).'.');
        }

        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

        return $response;
    }

    public function show(Request $request, string $type, ?string $host = null): Response
    {
        // $host = $host ?? $request->getHost();
        if (null !== $host) {
            $this->apps->switchCurrentApp($host);
        }

        $response = $this->initResponse($request);

        $form = $this->getFormManager($type, $request)->getCurrentStep()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            return $response->setContent($this->getFormManager($type, $request)->validCurrentStep($form));
        }

        return $response->setContent($this->getFormManager($type, $request)->showForm($form));
    }
}
