<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanelLanguageController extends AbstractController
{
    #[Route('/panel/language', name: 'panel_language')]
    public function index(): Response
    {
        return $this->render('panel_language/index.html.twig', [
            'controller_name' => 'PanelLanguageController',
        ]);
    }
}
