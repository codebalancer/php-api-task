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
     * create new Item for the user with some data
     * @param User $user
     * @param string $data
     */
    public function create(User $user, string $data): void
    {
        $item = $this->createItem();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
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
    public function prepareRepositoryItemsForJsonResponse($items)
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




} 