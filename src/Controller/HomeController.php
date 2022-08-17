<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private TrickRepository $trickRepository,
    ) {
    }

    #[Route(path: '/', name: 'home', methods: Request::METHOD_GET)]
    public function index(): Response
    {
        $tricks = $this->trickRepository->findAll();

        return $this->render('home/home.html.twig', [
            'tricks' => $tricks,
        ]);
    }
}
