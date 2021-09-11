<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanelLanguageController extends PanelController
{
    #[Route('/panel/languages', name: 'panel_language')]
    public function language(Request $request): Response
    {
        $this->login($request);
        $languages = $this->languageRepository->findAll();

        return $this->render('panel_language/index.html.twig', [
            'user' => $this->user,
            'languages' => $languages,
        ]);
    }

    #[Route('/panel/language/{language}/edit', name: 'language_edit')]
    public function languageEdit(Request $request, Language $language): Response
    {
        $this->login($request);
        $form = $this->createForm(LanguageType::class, $language);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Language has been updated!');

            return $this->redirectToRoute('panel_language');
        }

        return $this->render('panel_language/edit.html.twig', [
            'language' => $language,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/panel/language/add', name: 'language_add')]
    public function languageAdd(Request $request): Response
    {
        $this->login($request);
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($language);
            $this->entityManager->flush();
            $this->addFlash('success', 'New language added!');

            return $this->redirectToRoute('panel_language');
        }

        return $this->render('panel_language/add.html.twig', [
            'language' => $language,
            'form' => $form->createView(),
        ]);
    }
}
