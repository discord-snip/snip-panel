<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\LanguageRepository;
use App\Repository\SnippetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private LanguageRepository $languageRepository;

    private SnippetRepository $snippetRepository;

    public function __construct(LanguageRepository $languageRepository, SnippetRepository $snippetRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->snippetRepository = $snippetRepository;
    }

    #[Route('/api/{language}/{name}', name: 'code')]
    public function code(string $language, string $name): Response
    {
        $snippet = $this->snippetRepository->findOneBy([
            'language' => $this->languageRepository->findOneByName($language),
            'name' => $name,
        ]);

        if (! $snippet) {
            throw $this->createNotFoundException('This snippet does not exist');
        }

        return $this->render('api/index.html.twig', [
            'snippet' => $snippet,
        ]);
    }
}
