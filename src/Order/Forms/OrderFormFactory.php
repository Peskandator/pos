<?php

namespace App\Order\Forms;

use App\Entity\Company;
use App\Entity\DiningTable;
use App\Entity\Order;
use App\Order\Action\CreateOrderAction;
use App\Order\Action\EditOrderAction;
use App\Order\Requests\CreateOrderRequest;
use App\Product\ORM\DiningTableRepository;
use App\Product\ORM\ProductRepository;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class OrderFormFactory
{
    public function __construct(
        private readonly CreateOrderAction $createOrderAction,
        private readonly EditOrderAction $editOrderAction,
        private readonly ProductRepository $productRepository,
        private readonly DiningTableRepository $diningTableRepository,
    )
    {
    }

    public function create(Company $company, ?Order $editedOrder): Form
    {
        $form = new Form;

        $editing = ($editedOrder instanceof Order);

        $form
            ->addText('description', 'Popis')
            ->setRequired()
            ->addRule($form::MAX_LENGTH,'Maximální délka je 200 znaků', 200)
        ;

        $form
            ->addInteger('inventory_number', 'Inventární číslo')
            ->setRequired()
        ;

        $diningTables = $company->getDiningTables();
        $diningTablesSelect = $this->getTablesCollectionForSelect($diningTables);
        $form
            ->addSelect('dining_table', 'Číslo stolu', $diningTablesSelect)
        ;

        $form
            ->addHidden('order_items')
        ;

        $submitText = $editing ? 'Upravit' : 'Přidat';
        $form->addSubmit('send', $submitText);

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company, $editedOrder) {
            $diningTable = $this->diningTableRepository->find($values->dining_table);
            if ($diningTable === null) {
                $errMsg = 'Je nutné vyplnit platné číslo stolu.';
                $form->addError($errMsg);
                $form->getPresenter()->flashMessage($errMsg, FlashMessageType::ERROR);
            }

            if (!$this->isInventoryNumberAvailable($company, $values->inventory_number, $editedOrder)) {
                $errMsg = 'Objednávka s tímto inventárním číslem již existuje.';
                $form['inventory_number']->addError($errMsg);
                $form->addError($errMsg);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company, $editing, $editedOrder) {
            $diningTable = $this->diningTableRepository->find($values->dining_table);

            $request = new CreateOrderRequest(
                $diningTable,
                $values->description,
                $values->inventory_number,
            );

            $orderItems = [];

            if (isset($values->order_items)) {
                $orderItemsData = json_decode($values->order_items, true);
                foreach ($orderItemsData as $orderItemData) {
                    if ((int)$orderItemData['product'] === 0) {
                        continue;
                    }
                    $product = $this->productRepository->find($orderItemData['product']);
                    if ($product === null) {
                        continue;
                    }
                    if ($product->getCompany()->getId() !== $company->getId()) {
                        continue;
                    }

                    $orderItems[] = $orderItemData;
                }
            }

            $message = 'Objednávka byla přidána.';
            if ($editing) {
                $message = 'Objednávka byla upravena.';
                $this->editOrderAction->__invoke($company, $editedOrder, $request, $orderItems);
            } else {
                $this->createOrderAction->__invoke($company, $request, $orderItems);
            }
            $form->getPresenter()->flashMessage($message, FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }

    public function fillInForm(Form $form, Order $order): Form
    {
        $form->setDefaults([
            'description' => $order->getDescription(),
            'inventory_number' => $order->getInventoryNumber(),
        ]);

        $form->setValues(array(
            'dining_table' => $order->getDiningTable()?->getId(),
        ));

        return $form;
    }


    protected function isInventoryNumberAvailable(Company $company, int $number, ?Order $editedOrder): bool
    {
        $orders = $company->getOrders();
        /**
         * @var Order $order
         */
        foreach ($orders as $order) {
            if ($editedOrder !== null && $editedOrder->getId() === $order->getId()) {
                continue;
            }
            if ($order->getInventoryNumber() === $number) {
                return false;
            }
        }
        return true;
    }

    protected function getTablesCollectionForSelect(array $diningTables): array
    {
        $items = [];
        $items[0] = 'Vyberte ...';
        /**
         * @var DiningTable $diningTable
         */
        foreach ($diningTables as $diningTable) {
            $items[$diningTable->getId()] = $diningTable->getNumber();
        }

        return $items;
    }
}