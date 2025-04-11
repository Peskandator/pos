<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Order\Forms\OrderFormFactory;
use App\Order\ORM\OrderRepository;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Action\DeleteOrderAction;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class OrdersPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly OrderFormFactory  $orderFormFactory,
        private readonly OrderRepository   $orderRepository,
        private readonly DeleteOrderAction $deleteOrderAction,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Objednávky',
                null)
        );

        $this->template->orders = $this->currentCompany->getOrders();
    }

    public function actionCreateNew(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Objednávky',
                $this->lazyLink(':Admin:Orders:default'))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Přidání objednávky',
                null)
        );

        $this->template->orderItemOptions = $this->currentCompany->getProducts();
    }

    protected function createComponentCreateOrderForm(): Form
    {
        $nextInventoryNumber = $this->getNextInventoryNumber();

        $form = $this->orderFormFactory->create($this->currentCompany, null);

        $form->setDefaults([
            'inventory_number' => $nextInventoryNumber,
        ]);

        return $form;
    }

    protected function createComponentDeleteOrderForm(): Form
    {
        $form = new Form;

        $form
            ->addHidden('id')
            ->setRequired(true)
        ;
        $form->addSubmit('send');

        $form->onValidate[] = function (Form $form, \stdClass $values) {
            $order = $this->orderRepository->find((int)$values->id);

            if (!$order) {
                $form->addError('Objednávka nebyla nalezena.');
                $this->flashMessage('Objednávka nebyla nalezena.', FlashMessageType::ERROR);
                return;
            }
            $entity = $order->getCompany();
            $form = $this->checkAccessToElementsCompany($form, $entity);
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $order = $this->orderRepository->find((int)$values->id);
            $this->deleteOrderAction->__invoke($order);
            $this->flashMessage('Objednávka byla smazána.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }

    private function getNextInventoryNumber(): int
    {
        $orders = $this->currentCompany->getOrders();

        $highest = 1;

        /** @var Order $order */
        foreach ($orders as $order) {
            $inventoryNumber = $order->getInventoryNumber();
            if ($inventoryNumber > $highest) {
                $highest = $inventoryNumber;
            }
        }

        return $highest + 1;
    }
}