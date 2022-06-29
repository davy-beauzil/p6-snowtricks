<?php

declare(strict_types=1);

namespace App\Form\CreateTrick;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Repository\TrickRepository;
use App\Services\ScalewayService;
use App\Services\SecurityService;
use App\Services\TransformUrlService;
use function array_key_exists;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use function is_array;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class CreateTrickHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private TrickRepository $trickRepository,
        private ScalewayService $scalewayService,
        private SluggerInterface $slugger,
    ) {
    }

    public function prepare(Trick $trick, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed('create_trick', CreateTrickForm::class, $trick, $options);
    }

    public function handle(FormInterface $form, Request $request): ?Trick
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Trick $trick */
            $trick = $form->getData();
            $urls = is_array($request->get('create_trick')) && array_key_exists('videos', $request->get('create_trick'))
                ? $request->get('create_trick')['videos']
                : [];
            $mainImage = is_array($request->files->get('create_trick')) && array_key_exists(
                'mainImage',
                $request->files->get('create_trick')
            )
                ? $request->files->get('create_trick')['mainImage']
                : null;
            $images = is_array($request->files->get('create_trick')) && array_key_exists(
                'images',
                $request->files->get('create_trick')
            )
                ? $request->files->get('create_trick')['images']
                : [];

            // On créé et enregistre le slug
            $slug = $this->slugger->slug($trick->getName())
                ->toString()
            ;
            if ($this->trickRepository->trickExists($slug)) {
                $form->addError(new FormError('Le nom que vous avez donné à la figure est déjà utilisé.'));

                return null;
            }
            $trick->setSlug($slug);

            // On enregistrement les liens de vidéos
            foreach ($urls as $url) {
                if (! TransformUrlService::isValid($url)) {
                    $form->addError(new FormError('Les vidéos doivent seulement provenir de Youtube ou Daylimotion.'));

                    return null;
                }
                $video = new Video();
                $video->setUrl($url);
                $video->setTrick($trick);
                $trick->addVideo($video);
            }

            // On enregiste l'image principale
            if (! $mainImage instanceof UploadedFile) {
                $form->addError(
                    new FormError('Une erreur est survenue lors de l’enregistrement de l’image principale.')
                );

                return null;
            }
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
            $mainImageEntity->setTrick($trick);
            $trick->setMainImage($mainImageEntity);

            // On enregistre les images secondaires
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

            // On enregistre en BDD la nouvelle figure
            try {
                $trick->setId(SecurityService::generateRamdomId());
                $this->trickRepository->add($trick, true);

                return $trick;
            } catch (OptimisticLockException|ORMException) {
                $form->addError(new FormError('Une erreur est survenue lors de l’enregistrement de la figure'));

                return null;
            }
        }

        return null;
    }
}
