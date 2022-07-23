<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/videos')]
class VideoController extends BaseController
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    #[Route('/{id}/delete', name: 'videos_delete', methods: Request::METHOD_GET)]
    public function delete(string $id, Request $request): Response
    {
        try {
            $video = $this->videoRepository->findOneBy([
                'id' => $id,
            ]);
            if (! $video instanceof Video) {
                throw new Exception();
            }
            $this->allowAccessOnlyUser($video->getTrick()->getAuthor());
            $this->videoRepository->remove($video, true);
            $this->addFlash('success', 'La vidéo a bien été supprimée');
        } catch (Throwable) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de la vidéo');
        }

        return $this->redirectToLastPage();
    }
}
