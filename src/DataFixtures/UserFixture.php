<?php

namespace App\DataFixtures;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('john');
        $user->setPassword($this->encoder->encodePassword($user, 'maxsecure'));

        $user2 = new User();
        $user2->setUsername('jane');
        $user2->setPassword($this->encoder->encodePassword($user2, 'minsecure'));

        $manager->persist($user);
        $manager->persist($user2);
         
        $manager->flush();
    }
}
