<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Services\SecurityService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private SluggerInterface $slugger,
    ) {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        Factory::create();

        $users = $this->fillUsers();
        $groups = $this->fillGroups();
        $tricks = $this->fillTricks($groups, $users);
        $videos = $this->fillVideos($tricks);
        $comments = $this->fillComments($tricks, $users);

        $toPersist = array_merge($users, $groups, $tricks, $videos, $comments);
        foreach ($toPersist as $object) {
            $manager->persist($object);
        }

        $manager->flush();
    }

    /**
     * @return User[]
     */
    private function fillUsers(): array
    {
        $users = [];

        $admin = new User();
        $admin->setEmail('admin@test.fr')
            ->setUsername('admin')
            ->setPassword($this->hasher->hashPassword($admin, 'admin'))
            ->setConfirmedAt(new DateTimeImmutable())
            ->setForgotPasswordToken(SecurityService::generateRamdomId())
            ->setConfirmationToken(SecurityService::generateRamdomId())
        ;
        $users[] = $admin;

        for ($i = 0; $i < 25; ++$i) {
            $user = new User();
            $user->setEmail($this->faker->email())
                ->setUsername($this->faker->userName)
                ->setPassword($this->hasher->hashPassword($user, $this->faker->password()))
                ->setConfirmedAt(new DateTimeImmutable())
                ->setForgotPasswordToken(SecurityService::generateRamdomId())
                ->setConfirmationToken(SecurityService::generateRamdomId())
            ;
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @return Group[]
     */
    private function fillGroups(): array
    {
        $groups = [];
        $groupsName = [
            'Grabs',
            'Rotations',
            'Flips',
            'Rotations désaxées',
            'Slides',
            'One foot tricks',
            'Japan air',
            'seat belt',
            'truck driver',
            '180',
            '360',
            '540',
            '720',
            '900',
            '1080',
            'front flip',
            'back flip',
        ];
        foreach ($groupsName as $groupName) {
            $group = new Group();
            $group->setName($groupName);
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * @param Group[] $groups
     * @param User[]  $users
     *
     * @return Trick[]
     */
    private function fillTricks(array $groups, array $users): array
    {
        $tricks = [];
        $tricksName = [
            'Mute',
            'Sad',
            'Indy',
            'Stalefish',
            'Tail grab',
            'Nose Grab',
            'Japan air',
            'seat belt',
            'truck driver',
            '180',
            '360',
            '540',
            '720',
            '900',
            '1080',
            'front flip',
            'back flip',
        ];
        foreach ($tricksName as $trickName) {
            $trick = new Trick();
            /** @var string $description */
            $description = $this->faker->sentences(asText: true);
            $trick->setName($trickName)
                ->setTrickGroup($groups[array_rand($groups)])
                ->setAuthor($users[array_rand($users)])
                ->setSlug($this->slugger->slug($trickName)->toString())
                ->setDescription($description)
            ;
            $tricks[] = $trick;
        }

        return $tricks;
    }

    /**
     * @param Trick[] $tricks
     *
     * @return Video[]
     */
    private function fillVideos(array $tricks): array
    {
        $videos = [];
        $links = [
            'https://www.youtube.com/watch?v=GBR0dlVgiiQ',
            'https://www.youtube.com/watch?v=Er8uhAymsR4',
            'https://www.youtube.com/watch?v=axNnKy-jfWw',
            'https://www.youtube.com/watch?v=M_BOfGX0aGs&t',
            'https://www.youtube.com/watch?v=T7TYv1QGp7s&t',
            'https://www.youtube.com/watch?v=PxhfDec8Ays&t',
            'https://www.youtube.com/watch?v=P-HnC7Ej9mw',
            'https://www.youtube.com/watch?v=_OMar04NRZw',
            'https://www.youtube.com/watch?v=GBR0dlVgiiQ',
            'https://www.youtube.com/watch?v=Er8uhAymsR4',
            'https://www.youtube.com/watch?v=axNnKy-jfWw',
            'https://www.youtube.com/watch?v=M_BOfGX0aGs&t',
            'https://www.youtube.com/watch?v=T7TYv1QGp7s&t',
            'https://www.youtube.com/watch?v=PxhfDec8Ays&t',
            'https://www.youtube.com/watch?v=P-HnC7Ej9mw',
            'https://www.youtube.com/watch?v=_OMar04NRZw',
        ];

        foreach ($links as $link) {
            $video = new Video();
            $video->setUrl($link)
                ->setTrick($tricks[array_rand($tricks)])
            ;
            $videos[] = $video;
        }

        return $videos;
    }

    /**
     * @param Trick[] $tricks
     * @param User[]  $users
     *
     * @return Comment[]
     */
    private function fillComments(array $tricks, array $users): array
    {
        $comments = [];

        for ($i = 0; $i < 100; ++$i) {
            $comment = new Comment();
            /** @var string $commentText */
            $commentText = $this->faker->sentences(random_int(1, 3), true);
            $comment->setComment($commentText)
                ->setTrick($tricks[array_rand($tricks)])
                ->setAuthor($users[array_rand($users)])
            ;
            $comments[] = $comment;
        }

        return $comments;
    }
}
