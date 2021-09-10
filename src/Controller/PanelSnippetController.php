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
    #[Route('/panel/{snippet}', name: 'details')]
    public function details(Snippet $snippet): Response
    {
        return $this->render('panel_snippet/index.html.twig', [
            'snippet' => $snippet,
        ]);
    }

    #[Route('/panel/{snippet}/edit', name: 'edit_snippet')]
    public function edit(Snippet $snippet, Request $request): Response
    {
        $form = $this->createForm(SnippetType::class, $snippet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Snippet has been updated!');

            return $this->redirectToRoute('details', [
                'snippet' => $snippet->getId(),
            ]);
        }

        return $this->render('panel_snippet/edit.html.twig', [
            'snippet' => $snippet,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/panel/snippet/add', name: 'add_snippet')]
    public function add(Request $request): Response
    {
        $snippet = new Snippet();
        $form = $this->createForm(SnippetType::class, $snippet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($snippet);
            $this->entityManager->flush();
            $this->addFlash('success', 'Snippet has been created!');

            return $this->redirectToRoute('details', [
                'snippet' => $snippet->getId(),
            ]);
        }

        return $this->render('panel_snippet/add.html.twig', [
            'snippet' => $snippet,
            'form' => $form->createView(),
        ]);
    }
}
