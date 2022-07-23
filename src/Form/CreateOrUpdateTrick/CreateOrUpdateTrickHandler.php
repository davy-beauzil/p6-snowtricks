<?php

declare(strict_types=1);

namespace App\Form\CreateOrUpdateTrick;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Exception\CreateTrickException;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Services\ScalewayService;
use App\Services\SecurityService;
use App\Services\TransformUrlService;
use Symfony\Component\Security\Core\Security;
use function array_key_exists;
use function is_array;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class CreateOrUpdateTrickHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private TrickRepository $trickRepository,
        private ImageRepository $imageRepository,
        private ScalewayService $scalewayService,
        private SluggerInterface $slugger,
        private Security $security,
    ) {
    }

    public function prepare(Trick $trick, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed('create_trick', CreateOrUpdateTrickForm::class, $trick, $options);
    }

    public function handleCreate(FormInterface $form, Request $request): ?Trick
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var Trick $trick */
                $trick = $form->getData();
                $this->setMainImage($request, $trick);
                $this->setSecondaryImages($request, $trick);
                $this->setSlug($trick);
                $this->setVideos($request, $trick);
                $this->setAuthor($trick);
//                $this->imageRepository->add($mainImageEntity, true);
                $this->trickRepository->add($trick, true);

                return $trick;
            } catch (CreateTrickException $e){
                $form->addError(new FormError($e->getMessage()));
                return null;
            }catch (\Throwable $e) {
                $form->addError(new FormError('Une erreur est survenue lors de l’enregistrement de la figure'));
                return null;
            }
        }

        return null;
    }

    public function handleUpdate(FormInterface $form, Request $request): ?Trick
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var Trick $trick */
                $trick = $form->getData();
                $this->setMainImage($request, $trick);
                $this->setSecondaryImages($request, $trick);
                $this->setSlug($trick, true);
                $this->setVideos($request, $trick);
                $this->trickRepository->add($trick, true);
                return $trick;
            } catch (CreateTrickException $e){
                $form->addError(new FormError($e->getMessage()));
                return null;
            } catch (\Throwable) {
                $form->addError(new FormError('Une erreur est survenue lors de la modification de la figure'));
                return null;
            }
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    private function setMainImage(Request $request, Trick $trick): void
    {
        $mainImage = is_array($request->files->get('create_trick')) && array_key_exists(
            'mainImage',
            $request->files->get('create_trick')
        )
            ? $request->files->get('create_trick')['mainImage']
            : null;

        // On vérifie que l'image est présente
        if (! $mainImage instanceof UploadedFile && $trick->getMainImage() === null) {
            throw new \Exception('Vous devez obligatoirement ajouter une image principale.');
        }

        // Si l'image doit être remplacée, on supprime l'ancienne
        if ($mainImage instanceof UploadedFile && $trick->getMainImage() !== null) {
            $this->scalewayService->removeFile($trick->getMainImage()->getPath());
        }

        if($mainImage instanceof UploadedFile){
            $stream = fopen($mainImage->getPathname(), 'r');
            $path = sprintf(
                'tricks/%s-%s.%s',
                bin2hex(random_bytes(16)),
                time(),
                $mainImage->getClientOriginalExtension()
            );
            $this->scalewayService->uploadImage($path, $stream);
            $mainImageEntity = new Image();
            $mainImageEntity->setPath($path);
            $this->imageRepository->add($mainImageEntity, true);
            $trick->setMainImage($mainImageEntity);
        }
    }

    private function setSlug(Trick $trick, bool $isUpdate = false): void
    {
        $slug = $this->slugger->slug($trick->getName())
            ->toString()
        ;
        if ($this->trickRepository->trickExists($slug, $isUpdate ? $trick->getId() : null)) {
            throw new CreateTrickException('Le nom que vous avez donné à la figure est déjà utilisé.');
        }
        $trick->setSlug($slug);
    }

    private function setSecondaryImages(Request $request, Trick $trick): void
    {
        $images = is_array($request->files->get('create_trick')) && array_key_exists(
            'images',
            $request->files->get('create_trick')
        )
            ? $request->files->get('create_trick')['images']
            : [];

        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $stream = fopen($image->getPathname(), 'r');
                $path = sprintf(
                    'tricks/%s-%s.%s',
                    bin2hex(random_bytes(16)),
                    time(),
                    $image->getClientOriginalExtension()
                );
                if ($this->scalewayService->uploadImage($path, $stream)) {
                    $imageEntity = new Image();
                    $imageEntity->setPath($path);
                    $trick->addImage($imageEntity);
                }
            }
        }
    }

    private function setAuthor(Trick $trick): void
    {
        $currentUser = $this->security->getUser();
        if(!$currentUser instanceof User){
            throw new CreateTrickException('Une erreur est survenue lors de l’enregistrement de la figure');
        }
        $trick->setAuthor($currentUser);
    }

    private function setVideos(Request $request, Trick $trick): void
    {
        $urls = is_array($request->get('create_trick')) && array_key_exists('videos', $request->get('create_trick'))
            ? $request->get('create_trick')['videos']
            : [];

        // On enregistrement les liens de vidéos
        foreach ($urls as $url) {
            if (! TransformUrlService::isValid($url)) {
                throw  new CreateTrickException('Les vidéos doivent seulement provenir de Youtube ou Daylimotion.');
            }
            $video = new Video();
            $video->setUrl($url);
            $video->setTrick($trick);
            $trick->addVideo($video);
        }
    }
}
