<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CreateGroup\CreateGroupHandler;
use App\Form\CreateTrick\CreateTrickHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/tricks')]
class TrickController extends AbstractController
{
    public function __construct(
        private CreateGroupHandler $createGroupHandler,
        private CreateTrickHandler $createTrickHandler,
    ) {
    }

    #[Route(path: '/create', name: 'tricks_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        if (! $this->getUser() instanceof User) {
            $this->addFlash('danger', 'Vous devez être connecté pour créer de nouvelles figures.');

            return $this->redirectToRoute('home');
        }

        $group = new Group();
        $createGroupForm = $this->createGroupHandler->prepare($group);
        $isCreatedGroup = $this->createGroupHandler->handle($createGroupForm, $request);

        if ($isCreatedGroup === true) {
            $this->addFlash('success', 'Le groupe a bien été créé');
        } elseif ($isCreatedGroup === false) {
            $this->addFlash('warning', 'Le groupe existe déjà');
        }

        $trick = new Trick();
        $createTrickForm = $this->createTrickHandler->prepare($trick);
        $this->createTrickHandler->handle($createTrickForm, $request);

        return $this->renderForm('tricks/create.html.twig', [
            'createGroupForm' => $createGroupForm,
            'createTrickForm' => $createTrickForm,
        ]);
    }
}
