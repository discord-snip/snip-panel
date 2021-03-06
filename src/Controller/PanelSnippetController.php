<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Snippet;
use App\Form\SnippetType;
use App\Repository\SnippetRepository;
use App\Service\AuthenticationService;
use App\Service\DiscordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanelSnippetController extends AbstractController
{
    private const DISCORD_API_URL = 'https://discordapp.com/api';

    private ?array $user = null;

    private ?array $contributor = null;

    private EntityManagerInterface $entityManager;

    private DiscordService $discordService;

    private AuthenticationService $authenticationService;

    private RequestStack $requestStack;

    private SnippetRepository $snippetRepository;

    public function __construct(
        DiscordService $discordService,
        AuthenticationService $authenticationService,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        SnippetRepository $snippetRepository,
    ) {
        $this->discordService = $discordService;
        $this->authenticationService = $authenticationService;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->snippetRepository = $snippetRepository;
    }

    #[Route('/panel/snippets', name: 'snippet_panel')]
    public function snippet(): Response
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
                $snippets = $this->snippetRepository->findAll();

                return $this->render('panel_snippet/index.html.twig', [
                    'user' => $this->user,
                    'snippets' => $snippets,
                ]);
            }

            return $this->render('panel/forbidden.html.twig', [
                'user' => $this->user,
            ]);
        }

        // Fallback to default action - redirect to Discord Oauth2
        return $this->redirectToRoute('login');
    }

    #[Route('/panel/snippet/{snippet}/details', name: 'snippet_details')]
    public function details(Snippet $snippet): Response
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
                return $this->render('panel_snippet/details.html.twig', [
                    'snippet' => $snippet,
                ]);
            }

            return $this->render('panel/forbidden.html.twig', [
                'user' => $this->user,
            ]);
        }

        // Fallback to default action - redirect to Discord Oauth2
        return $this->redirectToRoute('login');
    }

    #[Route('/panel/snippet/{snippet}/edit', name: 'snippet_edit')]
    public function edit(Snippet $snippet, Request $request): Response
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
                $form = $this->createForm(SnippetType::class, $snippet);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Snippet has been updated!');

                    return $this->redirectToRoute('snippet_details', [
                        'snippet' => $snippet->getId(),
                    ]);
                }

                return $this->render('panel_snippet/edit.html.twig', [
                    'snippet' => $snippet,
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

    #[Route('/panel/snippet/{snippet}/delete', name: 'snippet_delete')]
    public function delete(Snippet $snippet, Request $request): Response
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
                $form = $this->createFormBuilder()
                    ->getForm();

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->entityManager->remove($snippet);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Snippet has been removed!');

                    return $this->redirectToRoute('snippet_panel');
                }

                return $this->render('panel_snippet/delete.html.twig', [
                    'snippet' => $snippet,
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

    #[Route('/panel/snippet/add', name: 'snippet_add')]
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
                $snippet = new Snippet();
                $form = $this->createForm(SnippetType::class, $snippet);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->entityManager->persist($snippet);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Snippet has been created!');

                    return $this->redirectToRoute('snippet_details', [
                        'snippet' => $snippet->getId(),
                    ]);
                }

                return $this->render('panel_snippet/add.html.twig', [
                    'snippet' => $snippet,
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
