<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use App\Repository\LanguageRepository;
use App\Service\AuthenticationService;
use App\Service\DiscordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanelLanguageController extends AbstractController
{
    private const DISCORD_API_URL = 'https://discordapp.com/api';

    private ?array $user = null;

    private ?array $contributor = null;

    private EntityManagerInterface $entityManager;

    private DiscordService $discordService;

    private AuthenticationService $authenticationService;

    private RequestStack $requestStack;

    private LanguageRepository $languageRepository;

    public function __construct(
        DiscordService $discordService,
        AuthenticationService $authenticationService,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        LanguageRepository $languageRepository
    ) {
        $this->discordService = $discordService;
        $this->authenticationService = $authenticationService;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->languageRepository = $languageRepository;
    }

    #[Route('/panel/languages', name: 'language_panel')]
    public function language(): Response
    {
        $session = $this->requestStack->getSession();
        $session->start();
        if ($session->get('access_token', false)) {
            try {
                $this->user = $this->discordService->apiRequest(self::DISCORD_API_URL . '/users/@me');
                $this->contributor = $this->authenticationService->checkPermissions($this->user['id']);
            } catch (\JsonException $e) {
                die($e->getMessage());
            }

            if ($this->contributor['hasAccess']) {
                $languages = $this->languageRepository->findBy([], ['name' => 'ASC']);

                return $this->render('panel_language/index.html.twig', [
                    'user' => $this->user,
                    'languages' => $languages,
                ]);
            }

            return $this->render('panel/forbidden.html.twig', [
                'user' => $this->user,
            ]);
        }

        // Fallback to default action - redirect to Discord Oauth2
        return $this->redirectToRoute('login');
    }

    #[Route('/panel/language/{language}/edit', name: 'language_edit')]
    public function edit(Request $request, Language $language): Response
    {
        $session = $this->requestStack->getSession();
        $session->start();
        if ($session->get('access_token', false)) {
            try {
                $this->user = $this->discordService->apiRequest(self::DISCORD_API_URL . '/users/@me');
                $this->contributor = $this->authenticationService->checkPermissions($this->user['id']);
            } catch (\JsonException $e) {
                die($e->getMessage());
            }

            if ($this->contributor['hasAccess']) {
                $form = $this->createForm(LanguageType::class, $language);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Language has been updated!');

                    return $this->redirectToRoute('language_panel');
                }

                return $this->render('panel_language/edit.html.twig', [
                    'language' => $language,
                    'form' => $form->createView(),
                ]);
            }

            return $this->render('panel/forbidden.html.twig', [
                'user' => $this->user,
            ]);
        }

        // Fallback to default action - redirect to Discord Oauth2
        return $this->redirectToRoute('login');
    }

    #[Route('/panel/language/add', name: 'language_add')]
    public function add(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $session->start();
        if ($session->get('access_token', false)) {
            try {
                $this->user = $this->discordService->apiRequest(self::DISCORD_API_URL . '/users/@me');
                $this->contributor = $this->authenticationService->checkPermissions($this->user['id']);
            } catch (\JsonException $e) {
                die($e->getMessage());
            }

            if ($this->contributor['hasAccess']) {
                $language = new Language();
                $form = $this->createForm(LanguageType::class, $language);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->entityManager->persist($language);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'New language added!');

                    return $this->redirectToRoute('language_panel');
                }

                return $this->render('panel_language/add.html.twig', [
                    'language' => $language,
                    'form' => $form->createView(),
                ]);
            }

            return $this->render('panel/forbidden.html.twig', [
                'user' => $this->user,
            ]);
        }

        // Fallback to default action - redirect to Discord Oauth2
        return $this->redirectToRoute('login');
    }
}
