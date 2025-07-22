<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return new JsonResponse('OK');
    }

    #[Route('/misc', name: 'app_misc')]
    public function misc(
    ): Response {
        return new JsonResponse('Miscellaneous endpoint');
    }
}
