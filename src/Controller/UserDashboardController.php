<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\ScalewayService;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use function is_string;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/dashboard/users')]
class UserDashboardController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private ManagerRegistry $manager,
        private ScalewayService $scalewayService,
    ) {
    }

    #[Route('/', name: 'dashboard_users', methods: 'GET')]
    public function usersDashboard(): Response
    {
        if (! $this->currentUserIsAdmin()) {
            return $this->redirectToRoute('home');
        }

        $users = $this->userRepository->findAll();

        return $this->render('dashboard/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route(path: '/{id}/toggle-block', name: 'dashboard_users_toggle_block', methods: 'GET')]
    public function toggleBlock(Request $request): Response
    {
        if (! $this->currentUserIsAdmin()) {
            return $this->redirectToRoute('home');
        }

        try {
            if ($request->get('id') === null || ! is_string($request->get('id'))) {
                throw new Exception('Le paramètre "id" n’est pas au bon format');
            }

            $user = $this->userRepository->findOneById($request->get('id'));
            if (! $user instanceof User) {
                throw new Exception('Une erreur est survenue lors de la récupération de l’utilisateur');
            }

            if ($user->getBlockedAt() === null) {
                $user->setBlockedAt(new DateTimeImmutable());
            } else {
                $user->setBlockedAt(null);
            }
            $em = $this->manager->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Le changement a bien été effectué');
        } catch (Exception) {
            $this->addFlash('danger', 'Une erreur s’est produite lors du changement');
        }

        return $this->redirectToRoute('dashboard_users');
    }

    #[Route(path: '/{id}/remove', name: 'dashboard_users_remove', methods: 'GET')]
    public function remove(Request $request): Response
    {
        if (! $this->currentUserIsAdmin()) {
            return $this->redirectToRoute('home');
        }

        try {
            if ($request->get('id') === null || ! is_string($request->get('id'))) {
                throw new Exception('Le paramètre "id" n’est pas au bon format');
            }

            $user = $this->userRepository->findOneById($request->get('id'));
            if (! $user instanceof User) {
                throw new Exception('Une erreur est survenue lors de la récupération de l’utilisateur');
            }

            if ($user->getProfilePicture() !== null) {
                $this->scalewayService->removeFile($user->getProfilePicture());
            }

            $em = $this->manager->getManager();
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'La suppression a bien été effectuée');
        } catch (Exception) {
            $this->addFlash('danger', 'Une erreur s’est produite lors de la suppression');
        }

        return $this->redirectToRoute('dashboard_users');
    }

    private function currentUserIsAdmin(): bool
    {
        $user = $this->getUser();

        return $user instanceof User && $user->isAdmin();
    }
}
