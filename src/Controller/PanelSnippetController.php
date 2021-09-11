<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Snippet;
use App\Form\SnippetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanelSnippetController extends PanelController
{
    #[Route('/panel/snippet/{snippet}/details', name: 'snippet_details')]
    public function details(Snippet $snippet, Request $request): Response
    {
        $this->login($request);
        if ($this->contributor['hasAccess']) {
            return $this->render('panel_snippet/index.html.twig', [
                'snippet' => $snippet,
            ]);
        }

        return $this->render('panel/forbidden.html.twig', [
            'user' => $this->user,
        ]);
    }

    #[Route('/panel/snippet/{snippet}/edit', name: 'edit_snippet')]
    public function edit(Snippet $snippet, Request $request): Response
    {
        $this->login($request);
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

    #[Route('/panel/snippet/add', name: 'snippet_add')]
    public function add(Request $request): Response
    {
        $this->login($request);
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
}
