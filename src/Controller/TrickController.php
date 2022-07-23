<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CreateGroup\CreateGroupHandler;
use App\Form\CreateOrUpdateTrick\CreateOrUpdateTrickHandler;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/tricks')]
class TrickController extends AbstractController
{
    public function __construct(
        private CreateGroupHandler         $createGroupHandler,
        private CreateOrUpdateTrickHandler $createTrickHandler,
        private TrickRepository            $trickRepository,
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
        $this->createTrickHandler->handleCreate($createTrickForm, $request);

        return $this->renderForm('tricks/create.html.twig', [
            'groupForm' => $createGroupForm,
            'trickForm' => $createTrickForm,
        ]);
    }

    #[Route('/{slug}', name: 'tricks_details', methods: Request::METHOD_GET)]
    public function details(string $slug): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if(!$trick instanceof Trick){
            $this->addFlash('danger', 'Une erreur est survenue lors de la récupération de la figure');
            $this->redirectToRoute('home');
        }
        return $this->render('tricks/details.html.twig', [
            'trick' => $trick
        ]);
    }


    #[Route('/{slug}/delete', name: 'tricks_delete', methods: Request::METHOD_GET)]
    public function delete(string $slug): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if($trick instanceof Trick && $trick->getAuthor() === $this->getUser()){
            $this->trickRepository->remove($trick, true);
            $this->addFlash('success', 'La figure a bien été supprimée');
        }else{
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de la figure');
        }
        return $this->redirectToRoute('home');
    }

    #[Route('/{slug}/edit', name: 'tricks_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(string $slug, Request $request): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if(!$trick instanceof Trick){
            $this->addFlash('danger', 'Une erreur est survenue lors de la récupération de la figure');
            return $this->redirectToRoute('home');
        }
        if($trick->getAuthor() !== $this->getUser()){
            $this->addFlash('danger', 'Vous n’avez pas accès à cette ressource.');
            return $this->redirectToRoute('home');
        }

        $trickForm = $this->createTrickHandler->prepare($trick, []);
        $trick = $this->createTrickHandler->handleUpdate($trickForm, $request);

        if($trick instanceof Trick){
            return $this->redirectToRoute('tricks_details', ['slug' => $trick->getSlug()]);
        }

        return $this->renderForm('tricks/edit.html.twig', [
            'trickForm' => $trickForm
        ]);
    }
}
