<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Services\ScalewayService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/images')]
class ImageController extends AbstractController
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ScalewayService $scalewayService,
    ){}

    #[Route('/{id}/delete', name: 'images_delete', methods: Request::METHOD_GET)]
    public function delete(string $id, Request $request): Response
    {
        $user = $this->get('security.token_storage')->getToken();
        $this->getUser();
        dd($user);
        try{
            $image = $this->imageRepository->findOneBy(['id' => $id]);
            if($image instanceof Image && $image->getTrick()->getAuthor() === $this->getUser()){
                $this->imageRepository->remove($image, true);
                $this->scalewayService->removeFile($image->getPath());
                $this->addFlash('success', 'L’image a bien été supprimée');
            }else{
                throw new \Exception();
            }
        }catch(\Throwable $e){
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de l’image');
        }
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }
}