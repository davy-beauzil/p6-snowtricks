<?php

declare(strict_types=1);

namespace App\Form\CreateGroup;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateGroupHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private GroupRepository $groupRepository,
        private ManagerRegistry $doctrine,
    ) {
    }

    public function prepare(Group $group, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed('create_group', CreateGroupForm::class, $group, $options);
    }

    public function handle(FormInterface $form, Request $request): ?bool
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Group $group */
            $group = $form->getData();
            if (! $this->groupRepository->exists($group->getName())) {
                $em = $this->doctrine->getManager();
                $em->persist($group);
                $em->flush();

                return true;
            }

            return false;
        }

        return null;
    }
}
