<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\DiscordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PanelController extends AbstractController
{
    private const OAUTH2_CLIENT_ID = '866013874109284402';
    private const OAUTH2_URL = 'https://discordapp.com/api/oauth2/authorize';
    private const OAUTH2_TOKEN_URL = 'https://discordapp.com/api/oauth2/token';
    private const DISCORD_API_URL = 'https://discordapp.com/api';

    private DiscordService $discordService;
    private AuthenticationService $authenticationService;
    private RequestStack $requestStack;

    public function __construct(DiscordService        $discordService,
                                AuthenticationService $authenticationService,
                                RequestStack          $requestStack)
    {
        $this->discordService = $discordService;
        $this->authenticationService = $authenticationService;
        $this->requestStack = $requestStack;
    }

    #[Route('/panel', name: 'panel')]
    public function index(Request $request): Response
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
                    'code' => $request->query->get('code')
                ]);
            } catch (\JsonException $e) {
                die($e->getMessage());
            }
            $session->set('access_token', $token->access_token);

            return $this->redirectToRoute('panel');
        }

        if ($session->get('access_token', false)) {
            try {
                $user = $this->discordService->apiRequest(self::DISCORD_API_URL . '/users/@me');
                $contributor = $this->authenticationService->checkPermissions(
                    $user->id,
                    $this->getParameter('discord.role_id'),
                    $this->getParameter('discord.guild_id')
                );
            } catch (\JsonException $e) {
                die($e->getMessage());
            }

            if ($contributor->hasAccess) {
                return $this->render('panel/index.html.twig', [
                    'username' => $user->username,
                    'discriminator' => $user->discriminator,
                ]);
            }

            return $this->render('panel/forbidden.html.twig', [
                'username' => $user->username,
                'discriminator' => $user->discriminator,
            ]);
        }

        // Fallback to default action - redirect to Discord Oauth2
        $params = [
            'client_id' => self::OAUTH2_CLIENT_ID,
            'redirect_uri' => $this->generateUrl('panel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'scope' => 'identify'
        ];
        return $this->redirect(self::OAUTH2_URL . '?' . http_build_query($params));
    }

    #[Route('/panel/logout', name: 'logout')]
    public function logout(): Response
    {
        $session = $this->requestStack->getSession();
        $session->clear();
        return $this->redirectToRoute('main');
    }
}
