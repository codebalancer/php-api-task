<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ItemService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * create and persist new Item for the user with some data
     * @param User $user
     * @param string $data
     */
    public function create(User $user, string $data): Item
    {
        $item = $this->createItem();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }

    /**
     * updates existing item
     * @param int $id the item id
     * @param string $data the item data
     */
    public function update(int $id, string $data) : void
    {
        $item = $this->getItemById($id);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    /**
     * deletes item
     * @param $item
     */
    public function deleteItem(Item $item) : void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    /**
     * factory method that is mockable
     * @return Item
     */
    public function createItem() : Item
    {
        return new Item();
    }

    /**
     * helper: prepare Items from repository to be displayed as json data
     * @param $items
     * @return array
     */
    public function prepareRepositoryItemsForJsonResponse($items) : array
    {
        $allItems = [];
        foreach ($items as $item) {
            $oneItem = [];
            $oneItem['id'] = $item->getId();
            $oneItem['data'] = $item->getData();
            $oneItem['created_at'] = $item->getCreatedAt();
            $oneItem['updated_at'] = $item->getUpdatedAt();
            $allItems[] = $oneItem;
        }

        return $allItems;
    }

    /**
     * get items by user prepared ready for json response
     * @param UserInterface $user
     * @return array
     */
    public function getPreparedItemsForJsonResponseByUser(User $user) : array
    {
        $items = $this->getItemsForUser($user);
        return $this->prepareRepositoryItemsForJsonResponse($items);
    }

    /**
     * gets all items for the user
     * @param User $user
     * @return mixed
     */
    public function getItemsForUser(User $user)
    {
        return $this->entityManager->getRepository(Item::class)->findByUser($user);
    }

    /**
     * @param int $id
     * @return Item
     */
    public function getItemById(int $id) : Item
    {
        return $this->entityManager->getRepository(Item::class)->find($id);
    }


} 