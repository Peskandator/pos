<?php

namespace App\Presenters;

use App\Entity\Order;
use App\Entity\Product;
use App\Utils\FlashMessageType;

abstract class BaseCompanyPresenter extends BaseAdminPresenter
{
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        if (isset($this->currentCompanyId)) {
            $currentCompany = $this->findCompanyById();
            if ($currentCompany === null) {
                $this->redirect(':Admin:Dashboard:default', ['$currentCompanyId' => null]);
            }
        } else {
            $this->redirect(':Admin:Dashboard:default', ['$currentCompanyId' => null]);
        }
    }

    public function findProductById(int $productId): Product
    {
        if (!$this->currentCompany) {
            $this->addNoPermissionError();
        }

        $product = $this->productRepository->find($productId);

        if (!$product) {
            $this->flashMessage(
                'Produkt nebyl nalezen',
                FlashMessageType::ERROR
            );
            $this->redirect(':Admin:Products:default');
        }

        if ($product->getCompany()->getId() !== $this->currentCompany->getId()) {
            $this->addNoPermissionError();
        }

        return $product;
    }

    public function findOrderById(int $orderId): Order
    {
        if (!$this->currentCompany) {
            $this->addNoPermissionError();
        }

        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            $this->flashMessage(
                'Objednávka nebyla nalezena',
                FlashMessageType::ERROR
            );
            $this->redirect(':Admin:Orders:default');
        }

        if ($order->getCompany()->getId() !== $this->currentCompany->getId()) {
            $this->addNoPermissionError();
        }

        return $order;
    }
}