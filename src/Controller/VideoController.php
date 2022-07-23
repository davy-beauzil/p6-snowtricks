<?php

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/videos')]
class VideoController extends AbstractController
{
    public function __construct(
        private VideoRepository $videoRepository,
    ){}

    #[Route('/{id}/delete', name: 'videos_delete', methods: Request::METHOD_GET)]
    public function delete(string $id, Request $request): Response
    {
        dd($this->getUser());
        try{
            $video = $this->videoRepository->findOneBy(['id' => $id]);
            if($video instanceof Video && $video->getTrick()->getAuthor() === $this->getUser()){
                $this->videoRepository->remove($video, true);
            }
            $this->addFlash('success', 'La vidéo a bien été supprimée');
        }catch(\Throwable $e){
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de la vidéo');
        }
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }
}