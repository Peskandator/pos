<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Order\ORM\OrderRepository;
use App\Presenters\BaseCompanyPresenter;
use App\Order\Forms\OrderFormFactory;
use Nette\Application\UI\Form;

final class EditOrderPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly OrderFormFactory $orderFormFactory,
        private readonly OrderRepository $orderRepository,
    )
    {
        parent::__construct();
    }

    public function actionDefault(int $orderId): void
    {
        $order = $this->orderRepository->find($orderId);

        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Objednávky',
                $this->lazyLink(':Admin:Orders:default'))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                (string)$order->getInventoryNumber(),
                null)
        );

        $this->template->order = $order;
        $this->template->orderItemOptions = $this->currentCompany->getProducts();
    }

    protected function createComponentEditOrderForm(): Form
    {
        $order = $this->template->order;
        $form = $this->orderFormFactory->create($this->currentCompany, $order);

        $this->orderFormFactory->fillInForm($form, $order);
        return $form;
    }
}