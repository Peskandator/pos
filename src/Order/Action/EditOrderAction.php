<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Order\Requests\CreateOrderRequest;
use App\Order\Services\OrderItemHelper;
use App\Utils\FlashMessageType;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\IPresenter;

class EditOrderAction
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderItemHelper $orderItemHelper,
        private readonly CreateOrderItemAction $createOrderItemAction,
    ) {
    }

    public function __invoke(IPresenter $presenter, Company $company, Order $order, CreateOrderRequest $request, array $orderItems): void
    {
        $order->updateFromRequest($request);

        $mergedOrderItems = $this->orderItemHelper->mergeDuplicateOrderItems($orderItems);

        $currentUpdatedItems = [];
        $currentItems = $order->getOrderItems();

        /**
         * @var OrderItem $item
         */
        foreach ($currentItems as $item) {
            $productId = $item->getProduct()->getId();
            if (array_key_exists($productId, $mergedOrderItems)) {
                $currentUpdatedItems[] = $productId;
                $item->setQuantity($mergedOrderItems[$productId]);
                continue;
            }

            if ($item->getOrderItemPayments()->count() > 0) {
                // TODO: enable more flash messages
//                $presenter->flashMessage('Některé z položek nelze smazat, protože jsou již zaplacené.', FlashMessageType::WARNING);
                continue;
            }
            $order->removeOrderItem($item);
            $this->entityManager->remove($item);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        foreach ($mergedOrderItems as $productId => $quantity) {
            if (!in_array($productId, $currentUpdatedItems, true)) {
                $this->createOrderItemAction->create($order, $productId, $quantity);
            }
        }

        $this->entityManager->flush();
    }
}
