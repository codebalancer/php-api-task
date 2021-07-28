<?php

namespace App\Tests;

use App\Entity\Item;
use App\Entity\User;
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

    /**
     * unauthenticated user should not see any items at all
     */
    public function testGetNoItemsIfNotAuthenticated()
    {
        $client = static::createClient();
        $client->request('GET', '/item');

        $this->assertSame(401, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('<!DOCTYPE html>', $client->getResponse()->getContent());
    }

    /**
     * for authenticated new user show empty items data but as json response
     */
    public function testAuthenticatedNewUserHasNoItems()
    {
        $client = static::createClient();
        $client->loginUser($this->getOneUserByUsername('john'));
        $client->request('GET', '/item');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertEmpty(
            json_decode($client->getResponse()->getContent(), TRUE)
        );
    }
    /**
     * user should only see their own items
     */
    public function testAuthenticatedUserGetsOwnItemsOnly()
    {
        $this->markTestIncomplete();
    }
    /**
     * create item with data and verify in the db
     */
    public function testCreate()
    {
        $client = static::createClient();

        /**
         * @var $itemRepository ItemRepository
         */
        $itemRepository = $this->getItemRepository();

        $user = $this->getOneUserByUsername('john');

        $client->loginUser($user);
        
        $data = 'very secure new item data';
        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $response = $client->getResponse();

        // response should be ok = 200
        $this->assertSame(200, $response->getStatusCode());

        // response should be empty json
        $this->assertSame('[]', $response->getContent());

        // try to find the new item in json response, should be the latest = last = only one
        $client->request('GET', '/item');
        $this->assertResponseIsSuccessful();
        $responseJSON = $client->getResponse()->getContent();
        $data = json_decode($responseJSON, TRUE);
        $this->assertNotEmpty($data, 'json response seems empty');
        $last = $data[array_key_last($data)];

        $this->assertSame('very secure new item data', $last['data']);

        // actually find the new item in db and compare
        $criteria = ['user' => $user->getId()];
        $items = $itemRepository->findBy($criteria);
        // there should only be one by now
        $this->assertSame(1, sizeof($items));

        /**
         * @var Item
         */
        $item = $items[0];

        // both should be the same item(id) and content
        $this->assertSame($item->getId(), $last['id']);
        $this->assertSame($item->getData(), $last['data']);
    }

    public function testDelete()
    {
        $client = static::createClient();
        $user  = $this->getOneUserByUsername('john');
        $user2 = $this->getOneUserByUsername('jane');
        $client->loginUser($user);

        $data = 'secure data to be deleted';
        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $response = $client->getResponse();

        // get the id
        $client->request('GET', '/item');
        $responseData = json_decode($client->getResponse()->getContent(), TRUE);

        $lastItem = $responseData[array_key_last($responseData)];
        $itemId = $lastItem['id'];

        // logout does not exist, so login as someone else
        $client->loginUser($user2);

        // try to delete someone elses data should not work now
        $client->request('DELETE', '/item/' . $itemId );
        $this->assertNotSame(200, $client->getResponse()->getStatusCode());
        $responseData2 = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey('error', $responseData2);
        #$this->assertSame('No item', $responseData2['error']);

        // go back to correct user
        $client->loginUser($user);

        // now it should work
        $client->request('DELETE', '/item/' . $itemId);
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // and should be gone from collection
        $client->request('GET', '/item');
        $responseData3 = json_decode($client->getResponse()->getContent(), TRUE);

        foreach ($responseData3 as $item3){
            if ($itemId == $item3['id']) {
                $this->fail('item was not successfully deleted');
            }
        }

    }

    /**
     * test updating existing item
     */
    public function testUpdate()
    {
        $client = static::createClient();
        $userRepository = $this->getUserRepository();
        $user = $userRepository->findOneByUsername('john');

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @param $name string
     * @return User
     */
    private function getOneUserByUsername($name)
    {
        $user = $this->getUserRepository()->findOneByUsername($name);

        if (!$user instanceof User) {
            $this->throwException('user for test not found');
        }

        return $user;
    }
    private function getUserRepository() : UserRepository
    {
        return static::$container->get(UserRepository::class);
    }

    private function getItemRepository() : ItemRepository
    {
        return static::$container->get(ItemRepository::class);
    }

}
