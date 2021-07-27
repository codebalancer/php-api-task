<?php

namespace App\Tests;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ItemControllerTest
 * @package App\Tests
 * @group functional
 */
class ItemControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);
        $entityManager = static::$container->get(EntityManagerInterface::class);

        $user = $userRepository->findOneByUsername('john');

        if (!$user instanceof UserInterface) {
            $this->fail('user for test missing');
        }

        $client->loginUser($user);
        
        $data = 'very secure new item data';

        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('very secure new item data', $client->getResponse()->getContent());

        $userRepository->findOneByData($data);
    }
}
