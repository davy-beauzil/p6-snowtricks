<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Services\ScalewayService;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/image')]
class ImageController extends BaseController
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ScalewayService $scalewayService,
    ) {
    }

    #[Route('/{id}/delete', name: 'images_delete', methods: Request::METHOD_GET)]
    public function delete(string $id, Request $request): Response
    {
        try {
            $image = $this->imageRepository->findOneBy([
                'id' => $id,
            ]);
            if (! $image instanceof Image) {
                throw new Exception();
            }
            /** @var User $author */
            $author = $image->getMainImageTrick()?->getAuthor() ?? $image->getTrick()?->getAuthor();
            $this->allowAccessOnlyUser($author);
            $this->imageRepository->remove($image, true);
            $this->scalewayService->removeFile($image->getPath());
            $this->addFlash('success', 'L’image a bien été supprimée');
        } catch (Throwable) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de l’image');
        }

        return $this->redirectToLastPage();
    }
}
