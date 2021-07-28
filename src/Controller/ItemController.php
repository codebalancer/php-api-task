<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends AbstractController
{
    /**
     * list all user items after authentication
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(ItemService $itemService) : JsonResponse
    {
        $allItems = $itemService->getPreparedItemsForJsonResponseByUser($this->getUser());
        return $this->json($allItems);
    }

    /**
     * create item with data for user
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService) : JsonResponse
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $itemService->create($this->getUser(), $data);

        return $this->json([]);
    }

    /**
     * delete item
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id) : JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'id parameter missing'], Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Item $item
         */
        $item = $this->getDoctrine()->getRepository(Item::class)->find($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        // cant delete someone else's data!
        if ($item->getUser()->getId() != $this->getUser()->getId()){
            return $this->json(['error' => ''], Response::HTTP_FORBIDDEN);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($item);
        $manager->flush();

        return $this->json([]);
    }

    /**
     * update existing item with new data for user
     * @Route("/item/{id}", name="item_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, int $id, ItemService $itemService) : JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'id parameter missing'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->get('data');
        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->find($id);
        if (!$item instanceof Item) {
            return $this->json(['error' => 'item with id does not exist'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if (empty($user)) {
            //should not happen due to authentication but well ...
            return $this->json(['error' => 'user not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //make sure it's the user's item
        if ($item->getUser()->getId() != $user->getId()) {
            return $this->json(['error' => 'user not found'], Response::HTTP_FORBIDDEN);
        }

        //update the item data
        $item->setData($data);

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        return $this->json([]);
    }

}
