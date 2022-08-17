<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Exception;
use http\Exception\BadUrlException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

#[Route('/videos')]
class VideoController extends BaseController
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    #[Route('/{id}/delete', name: 'videos_delete', methods: Request::METHOD_GET)]
    public function delete(string $id): Response
    {
        try {
            $video = $this->videoRepository->findOneBy([
                'id' => $id,
            ]);
            if (! $video instanceof Video) {
                throw new BadUrlException('Une erreur est survenue lors de la suppression de la vidéo');
            }
            $this->allowAccessOnlyUser($video->getTrick()->getAuthor());
            $this->videoRepository->remove($video, true);
            $this->addFlash('success', 'La vidéo a bien été supprimée');
        } catch (BadUrlException $e){
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToLastPage();
    }
}
