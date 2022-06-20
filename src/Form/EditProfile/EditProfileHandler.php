<?php

declare(strict_types=1);

namespace App\Form\EditProfile;

use App\Entity\User;
use App\Services\ScalewayService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use function in_array;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class EditProfileHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private ManagerRegistry $doctrine,
        private ScalewayService $scalewayService,
        private LoggerInterface $logger,
        private Security $security,
    ) {
    }

    public function prepare(User $user, array $options = []): FormInterface
    {
        return $this->formFactory->create(EditProfileForm::class, $user, $options);
    }

    public function handle(FormInterface $form, Request $request): ?User
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $userToUpdate */
            $userToUpdate = $form->getData();

            $picture = $form->get('picture')
                ->getData()
            ;
            if ($picture instanceof UploadedFile) {
                if (! in_array($picture->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg'], true)) {
                    throw new Exception('Le format de l’image doit être PNG, JPG ou JPEG');
                }
                $stream = fopen($picture->getPathname(), 'r');
                $path = sprintf(
                    'users/%s-%s.%s',
                    bin2hex(random_bytes(16)),
                    time(),
                    $picture->getClientOriginalExtension()
                );

                if (! $this->scalewayService->uploadImage($path, $stream)) {
                    throw new FileException(
                        'Votre profil n’a pas pu être mis à jour car votre photo de profil n’a pas pu être enregistrée. Veuillez réessayer plus tard.'
                    );
                }
                // On supprime l'ancienne image si on en enregistre une nouvelle
                $user = $this->security->getUser();
                if ($user instanceof User && $user->getProfilePicture() !== null) {
                    $removed = $this->scalewayService->removeFile($user->getProfilePicture());
                    if ($removed === false) {
                        $this->logger->error(
                            sprintf(
                                'Le fichier "%s" n’a pas pu être supprimé de Scaleway',
                                $user->getProfilePicture()
                            )
                        );
                    }
                }

                $userToUpdate->setProfilePicture($path);
            }

            /*
             * Enregistrement des données en BDD
             */
            $em = $this->doctrine->getManager();
            $em->persist($userToUpdate);
            $em->flush();
        }

        return null;
    }
}
