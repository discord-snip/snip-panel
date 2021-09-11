<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\LanguageRepository;
use App\Repository\SnippetRepository;
use App\Service\AuthenticationService;
use App\Service\DiscordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PanelController extends AbstractController
{
    protected const OAUTH2_CLIENT_ID = '866013874109284402';

    protected const OAUTH2_URL = 'https://discordapp.com/api/oauth2/authorize';

    protected const OAUTH2_TOKEN_URL = 'https://discordapp.com/api/oauth2/token';

    protected const DISCORD_API_URL = 'https://discordapp.com/api';

    protected ?array $user = null;

    protected ?array $contributor = null;

    protected EntityManagerInterface $entityManager;

    protected DiscordService $discordService;

    protected AuthenticationService $authenticationService;

    protected RequestStack $requestStack;

    protected SnippetRepository $snippetRepository;

    protected LanguageRepository $languageRepository;

    public function __construct(
        DiscordService $discordService,
        AuthenticationService $authenticationService,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        SnippetRepository $snippetRepository,
        LanguageRepository $languageRepository
    ) {
        $this->discordService = $discordService;
        $this->authenticationService = $authenticationService;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->snippetRepository = $snippetRepository;
        $this->languageRepository = $languageRepository;
    }

    #[Route('/panel', name: 'panel')]
    public function index(Request $request): Response
    {
        $this->login($request);
        $snippets = $this->snippetRepository->findAll();

        return $this->render('panel/index.html.twig', [
            'user' => $this->user,
            'snippets' => $snippets,
        ]);
    }

    #[Route('/panel/logout', name: 'logout')]
    public function logout(): Response
    {
        $session = $this->requestStack->getSession();
        $session->clear();

        return $this->redirectToRoute('main');
    }

    public function login(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $session->start();

        if ($request->query->has('code')) {
            try {
                $token = $this->discordService->apiRequest(self::OAUTH2_TOKEN_URL, [
                    'grant_type' => 'authorization_code',
                    'client_id' => self::OAUTH2_CLIENT_ID,
                    'client_secret' => $this->getParameter('discord.oauth2_client_secret'),
                    'redirect_uri' => $this->generateUrl('panel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'code' => $request->query->get('code'),
                ]);
            } catch (\JsonException $e) {
                die($e->getMessage());
            }

            $session->set('access_token', $token['access_token']);

            return $this->redirectToRoute('panel');
        }

        if ($session->get('access_token', false)) {
            try {
                $this->user = $this->discordService->apiRequest(self::DISCORD_API_URL . '/users/@me');
                $this->contributor = $this->authenticationService->checkPermissions($this->user['id']);
            } catch (\JsonException $e) {
                die($e->getMessage());
            }

            if (! $this->contributor['hasAccess']) {
                return $this->render('panel/forbidden.html.twig', [
                    'user' => $this->user,
                ]);
            }
        }

        // Fallback to default action - redirect to Discord Oauth2
        return $this->redirect(self::OAUTH2_URL . '?' . http_build_query([
            'client_id' => self::OAUTH2_CLIENT_ID,
            'redirect_uri' => $this->generateUrl('panel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'scope' => 'identify',
        ]));
    }
}
