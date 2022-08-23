<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Trick;
use App\Form\CommentAdd\CommentAddHandler;
use App\Form\CreateGroup\CreateGroupHandler;
use App\Form\CreateOrUpdateTrick\CreateOrUpdateTrickHandler;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route(path: '/tricks')]
class TrickController extends BaseController
{
    public function __construct(
        private CreateGroupHandler $createGroupHandler,
        private CreateOrUpdateTrickHandler $createTrickHandler,
        private TrickRepository $trickRepository,
        private CommentAddHandler $commentAddHandler,
        private CommentRepository $commentRepository,
    ) {
    }

    #[Route(path: '/create', name: 'tricks_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        $newTrick = $this->createTrickHandler->handleCreate($createTrickForm, $request);

        if ($newTrick instanceof Trick) {
            $this->addFlash('success', 'La figure a bien été créée');

            return $this->redirectToRoute('tricks_details', [
                'slug' => $newTrick->getSlug(),
            ]);
        }
        if ($newTrick === false) {
            $this->addFlash('warning', 'Une erreur est survenue durant la création de la figure');
        }

        return $this->renderForm('tricks/create.html.twig', [
            'groupForm' => $createGroupForm,
            'trickForm' => $createTrickForm,
        ]);
    }

    #[Route('/{slug}/delete', name: 'tricks_delete', methods: Request::METHOD_GET)]
    public function delete(string $slug): Response
    {
        $trick = $this->trickRepository->findOneBy([
            'slug' => $slug,
        ]);
        if (! $trick instanceof Trick) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la récupération de la figure');

            return $this->redirectToRoute('home');
        }
        $this->allowAccessOnlyUser($trick->getAuthor());

        $this->trickRepository->remove($trick, true);
        $this->addFlash('success', 'La figure a bien été supprimée');

        return $this->redirectToRoute('home');
    }

    #[Route('/{slug}/edit', name: 'tricks_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(string $slug, Request $request): Response
    {
        $trick = $this->trickRepository->findOneBy([
            'slug' => $slug,
        ]);
        if (! $trick instanceof Trick) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la récupération de la figure');

            return $this->redirectToRoute('home');
        }
        $this->allowAccessOnlyUser($trick->getAuthor());

        $trickForm = $this->createTrickHandler->prepare($trick, []);
        $trick = $this->createTrickHandler->handleUpdate($trickForm, $request);

        if ($trick instanceof Trick) {
            return $this->redirectToRoute('tricks_details', [
                'slug' => $trick->getSlug(),
            ]);
        }

        return $this->renderForm('tricks/edit.html.twig', [
            'trickForm' => $trickForm,
        ]);
    }

    #[Route('/{slug}/{comments_page}', name: 'tricks_details', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function details(string $slug, Request $request, int $comments_page = 1): Response
    {
        $trick = $this->trickRepository->findOneBy([
            'slug' => $slug,
        ]);
        if (! $trick instanceof Trick) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la récupération de la figure');

            return $this->redirectToRoute('home');
        }

        $comment = new Comment();
        $commentForm = $this->commentAddHandler->prepare($comment, []);
        $this->commentAddHandler->handle($commentForm, $request, $trick);

        $comments = $this->commentRepository->getCommentWithPage($trick, $comments_page);
        $countPages = $this->commentRepository->countPages($trick);
        if ($comments_page > 1) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('tricks/comments.stream.html.twig', [
                'trick' => $trick,
                'comments' => $comments,
                'currentPage' => $comments_page,
                'countPages' => $countPages,
            ]);
        }

        return $this->renderForm('tricks/details.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'currentPage' => $comments_page,
            'countPages' => $countPages,
            'commentForm' => $commentForm,
        ]);
    }
}
