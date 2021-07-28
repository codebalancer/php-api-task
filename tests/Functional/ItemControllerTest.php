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
     * create items with data and get/verify afterwards
     */
    public function testAuthenticatedUserCreatesItemsAndGetsOwnItemsOnly()
    {
        $client = static::createClient();
        $itemRepository = $this->getItemRepository();
        $user = $this->getOneUserByUsername('john');

        $client->loginUser($user);
        
        $data = 'very secure new item data';
        $newItemData = ['data' => $data];

        $data2 = 'even more important data';
        $newItemData2 = ['data' => $data2];

        $client->request('POST', '/item', $newItemData);
        $response = $client->getResponse();

        // response should be ok = 200
        $this->assertSame(200, $response->getStatusCode());

        // response should be empty json
        $this->assertSame('[]', $response->getContent());

        // add another one
        $client->request('POST', '/item', $newItemData2);


        // try to find the new items in json response, should be the only ones
        $client->request('GET', '/item');
        $this->assertResponseIsSuccessful();
        $responseJSON = $client->getResponse()->getContent();
        $responseData = json_decode($responseJSON, TRUE);
        $this->assertNotEmpty($responseData, 'json response seems empty');

        // we should have 2 items and they are what we added
        $this->assertSame(2, sizeof($responseData));

        $responseItem1 = $responseData[0];
        $responseItem2 = $responseData[1];

        $this->assertSame('very secure new item data', $responseItem1['data']);
        $this->assertSame('even more important data',  $responseItem2['data']);
    }

    /**
     * create and delete item
     */
    public function testCreateAndDelete()
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

        // go back to original user
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
     *
     */
    public function testCreateUnauthenticatedUserFails()
    {
        $client = static::createClient();
        $client->request('PUT', '/item/1');

        $this->assertNotEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * test updating existing item
     */
    public function testCreateAndUpdateItem()
    {
        $client = static::createClient();
        $userRepository = $this->getUserRepository();
        $user = $userRepository->findOneByUsername('john');
        $client->loginUser($user);

        $data = 'newly created item data';
        $itemData = ['data' => $data];
        $client->request('POST', '/item', $itemData);

        // retrieve the id for update
        $client->request('GET', '/item');
        $responseData = json_decode($client->getResponse()->getContent(), TRUE);

        $this->assertSame(1, sizeof($responseData));
        $this->assertSame($data, $responseData[0]['data']);

        $itemId = $responseData[0]['id'];

        $updateData = 'updated item data is so fresh';
        $updateItem = ['data', $updateData];

        $client->request('PUT', '/item/' . $itemId, $updateItem);

        $client->request('GET', '/item');
        $updateResponseData = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertSame(1, sizeof($updateResponseData));

        $this->assertSame('updated item data is so fresh', $updateResponseData[0]['data']);
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
