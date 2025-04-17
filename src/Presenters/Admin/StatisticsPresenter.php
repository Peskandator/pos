<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Presenters\BaseCompanyPresenter;

final class StatisticsPresenter extends BaseCompanyPresenter
{

    public function __construct(
    )
    {
        parent::__construct();
    }

    public function actionDefault(?int $year = null, ?int $month = null): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Statistika',
                null)
        );

        $orders = $this->getOrdersForYear($year);


        $this->template->products = $this->getProductsDataForYear($orders);
        $this->template->totalPriceForYear = $this->getTotalPriceForYear($orders);


//        foreach ($products as $productId => $productData) {
//
//            $productData = [
//                'totalPrice' => 15000,
//                'quantity' => 10
//            ];
//        }
    }

    private function getOrdersForYear(int $year): array
    {
        $orders = $this->currentCompany->getOrders();
        $filteredOrders = [];

        foreach ($orders as $order) {
            // TODO filter year
//            bdump($order);
        }

        return $filteredOrders;
    }

    private function getTotalPriceForYear(array $orders): float
    {
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getTotalPrice();
        }

        return $totalPrice;
    }

    private function getProductsDataForYear(array $orders): array
    {
        $products = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            $orderItems = $order->getOrderItems();

            /** @var OrderItem $orderItem */
            foreach ($orderItems as $orderItem) {
                $product = $orderItem->getProduct();

                $products[$product->getId()]['totalPrice'] += $orderItem->getTotalPrice();
                $products[$product->getId()]['quantity'] += $orderItem->getQuantity();
            }
        }

        return $products;
    }
}