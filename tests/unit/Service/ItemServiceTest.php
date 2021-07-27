<?php

namespace App\Tests\Unit;

use App\Entity\Item;
use App\Entity\User;
use App\Service\ItemService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ItemServiceTest
 * @package App\Tests\Unit
 * @group unit
 */
class ItemServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var ItemService
     */
    private $itemService;

    public function setUp(): void
    {
        /** @var EntityManagerInterface */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->itemService = new ItemService($this->entityManager);
    }

    public function testCreate(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $data = 'secret data';

        $expectedObject = new Item();
        $expectedObject->setUser($user);
        $expectedObject->setData($data);

        $this->entityManager->expects($this->once())->method('persist')->with($expectedObject);

        $this->itemService->create($user, $data);
    }

    public function testPrepareRepositoryItemsForJsonResponse() : void
    {
        $items = [];

        $item1 = $this->createMock(Item::class);
        $item1->method('getId')->willReturn(1);
        $item1->method('getData')->willReturn('secret 1 data');

        $date1Created = new \DateTime('2021-01-21 18:27:59 UTC');
        $date1Updated  = new \DateTime('2021-01-22 11:25:11 UTC');
        $item1->method('getCreatedAt')->willReturn($date1Created);
        $item1->method('getUpdatedAt')->willReturn($date1Updated);
        $items[] = $item1;

        $item2 = $this->createMock(Item::class);
        $item2->method('getId')->willReturn(2);
        $item2->method('getData')->willReturn('more secret 2 data in here');

        $date2Created = new \DateTime('2014-12-27 09:45:02');
        $date2Updated = new \DateTime('2015-01-22 13:34:59');
        $item2->method('getCreatedAt')->willReturn($date2Created);
        $item2->method('getUpdatedAt')->willReturn($date2Updated);
        $items[] = $item2;

        $results = $this->itemService->prepareRepositoryItemsForJsonResponse($items);
        $this->assertSame(2, sizeof($results));

        $result1 = $results[0];
        $result2 = $results[1];

        $expected1 = [
            'id' => 1,
            'data' => 'secret 1 data',
            'created_at' => '',
            'update_at' => ''
        ];

        $expected2 = [
            'id' => 2,
            'data' => 'more secret 2 data in here',
            'created_at' => '',
            'update_at' => ''
        ];

        $this->assertEquals($expected1, $result1);
        $this->assertEquals($expected2, $result2);

    }

}
