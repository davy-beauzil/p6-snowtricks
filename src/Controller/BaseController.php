<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use function is_string;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class BaseController extends AbstractController
{
    public function redirectToLastPage(): RedirectResponse
    {
        try {
            /** @var Request $request */
            $request = $this->container->get('request_stack')
                ->getCurrentRequest()
            ;
            $lastPage = $request->server->get('HTTP_REFERER');
            if (is_string($lastPage)) {
                return $this->redirect($lastPage);
            }
        } catch (Throwable) {
        }

        return $this->redirectToRoute('home');
    }

    public function allowAccessOnlyUser(User $user): void
    {
        if ($this->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n’avez pas accès à cette ressource.');
        }
    }
}
