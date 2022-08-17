<?php

declare(strict_types=1);

namespace App\Form\CommentAdd;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Repository\CommentRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CommentAddHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Security $security,
        private CommentRepository $commentRepository,
    ) {
    }

    public function prepare(Comment $comment, array $options = []): FormInterface
    {
        return $this->formFactory->create(CommentAddForm::class, $comment, $options);
    }

    public function handle(FormInterface $form, Request $request, Trick $trick): ?bool
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->security->getUser();
            if(!$author instanceof User){
                return false;
            }
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setTrick($trick);
            $comment->setAuthor($author);
            $this->commentRepository->add($comment, true);
            return true;
        }

        return null;
    }
}
