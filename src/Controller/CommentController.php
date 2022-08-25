<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comments')]
class CommentController extends BaseController
{
    public function __construct(
        private CommentRepository $commentRepository,
    ) {
    }

    #[Route('/{id}/delete', name: 'comments_delete', methods: Request::METHOD_GET)]
    public function delete(string $id): Response
    {
        $comment = $this->commentRepository->findOneBy([
            'id' => $id,
        ]);
        $currentUser = $this->getUser();

        if ($comment instanceof Comment && $comment->getAuthor() === $currentUser) {
            $comment->getTrick();
            $this->commentRepository->remove($comment, true);
            $this->addFlash('success', 'Votre commentaire a bien été supprimé.');
        } else {
            $this->addFlash('danger', 'Impossible de supprimer ce commentaire.');
        }

        return $this->redirectToLastPage();
    }
}
