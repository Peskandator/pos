<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Entity\OrderItemPayment;
use App\Entity\Product;
use App\Order\ORM\OrderRepository;
use App\Presenters\BaseCompanyPresenter;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Doctrine\ORM\EntityManagerInterface;
use App\Product\Services\QrCodeGenerator;

final class OrderPaymentPresenter extends BaseCompanyPresenter
{
    private OrderRepository $orderRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
    }

    public function actionDefault(int $orderId): void
    {
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            $this->flashMessage("Objednávka nebyla nalezena.", "danger");
            $this->redirect(":Admin:Orders:default");
        }

        $this->getComponent("breadcrumb")->addItem(
            new BreadcrumbItem(
                "Objednávky",
                $this->lazyLink(":Admin:Orders:default")
            )
        );
        $this->getComponent("breadcrumb")->addItem(
            new BreadcrumbItem((string) $order->getInventoryNumber(), null)
        );

        $paidAmount = 0;
        $unpaidItems = [];

        /** @var OrderItem $item */
        foreach ($order->getOrderItems() as $item) {
            if ($item->isPaid()) {
                $paidAmount += $item->getPriceIncludingVat() * $item->getQuantity();
            } else {
                $unpaidItems[] = $item;
            }
        }
        $remainingAmount = $order->calculateTotalAmount() - $paidAmount;

        $this->template->order = $order;
        $this->template->paidAmount = $paidAmount;
        $this->template->remainingAmount = $remainingAmount;
        $this->template->unpaidItems = $unpaidItems;
        $this->template->bankAccount = $this->currentCompany->getBankAccount();
        $this->template->currentCompanyId = $this->currentCompany->getId();
    }

    protected function createComponentPaymentForm(): Form
    {
        $form = new Form();

        $unpaidItems = [];
        foreach ($this->template->order->getOrderItems() as $item) {
            if (!$item->isPaid()) {
                $unpaidItems[$item->getId()] = $item->getProductName() . " (" . $item->getPriceIncludingVat() . " Kč)";
            }
        }

        $form->addCheckboxList('items', 'Vyberte položky k platbě:', $unpaidItems);

        $paymentMethods = [
            'cash' => 'Hotovost',
        ];
    
        if ($this->template->bankAccount) {
            $paymentMethods['qr'] = 'QR Platba';
        }
    
        $form->addSelect('paymentMethod', 'Způsob platby:', $paymentMethods)
            ->setRequired();

        $form->addSubmit('pay', 'Zaplatit');

        $form->onSuccess[] = [$this, 'processPayment'];

        return $form;
    }

    private function getUnpaidItemsList(): array
    {
        $order = $this->template->order;
        $unpaidItemsList = [];

        /** @var OrderItem $item */
        foreach ($order->getOrderItems() as $item) {
            if (!$item->isPaid()) {
                $unpaidItemsList[$item->getId()] =
                    $item->getProductName() . " (" . $item->getPriceIncludingVat() . " Kč)";
            }
        }

        return $unpaidItemsList;
    }

    public function processPayment(Form $form, \stdClass $values): void
    {
        $order = $this->template->order;
        $selectedItems = [];
        $quantities = $this->getHttpRequest()->getPost('quantities') ?? [];

        foreach ($order->getOrderItems() as $item) {
            $itemId = $item->getId();
            $requestedQuantity = isset($quantities[$itemId]) ? (int)$quantities[$itemId] : 0;

            if ($requestedQuantity > 0) {
                $selectedItems[] = $itemId;
            }
        }

        if (empty($selectedItems)) {
            $this->flashMessage("Musíte vybrat alespoň jednu položku k platbě.", "danger");
            return;
        }

        $totalToPay = 0;

        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setPaymentTime(new \DateTimeImmutable());
        $payment->setPaymentMethod($values->paymentMethod);

        foreach ($order->getOrderItems() as $item) {
            $itemId = $item->getId();

            if (in_array($itemId, $selectedItems)) {
                $requestedQuantity = isset($quantities[$itemId]) ? (int)$quantities[$itemId] : 0;

                if ($requestedQuantity <= 0 || $requestedQuantity > $item->getQuantity() - $this->getPaidQuantityForItem($item)) {
                    continue;
                }

                $orderItemPayment = null;
                foreach ($item->getOrderItemPayments() as $existingPayment) {
                    if ($existingPayment->getPayment() === $payment) {
                        $orderItemPayment = $existingPayment;
                        break;
                    }
                }

                if (!$orderItemPayment) {
                    $orderItemPayment = new OrderItemPayment();
                    $orderItemPayment->setOrderItem($item);
                    $orderItemPayment->setPayment($payment);
                    $this->entityManager->persist($orderItemPayment);
                }

                $orderItemPayment->setPaidQuantity($orderItemPayment->getPaidQuantity() + $requestedQuantity);
                $this->entityManager->persist($orderItemPayment);

                $totalToPay += $item->getPriceIncludingVat() * $requestedQuantity;
            }
        }

        foreach ($order->getOrderItems() as $item) {
            $totalPaidQuantity = $this->getPaidQuantityForItem($item);

            if ($totalPaidQuantity >= $item->getQuantity()) {
                $item->markAsPaid();
            }
        }

        if ($totalToPay <= 0) {
            $this->flashMessage("Neplatná částka k úhradě.", "danger");
            return;
        }

        $payment->setAmount($totalToPay);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->flashMessage("Platba byla úspěšně zpracována.", "success");
        $this->redirect("this");
    }

    private function getPaidQuantityForItem(OrderItem $item): int
    {
        $totalPaidQuantity = 0;
        foreach ($item->getOrderItemPayments() as $payment) {
            $totalPaidQuantity += $payment->getPaidQuantity();
        }
        return $totalPaidQuantity;
    }


    public function actionGenerateQrCode(int $orderId, int $currentCompanyId, string $amount): void
    {
        if (!is_numeric($amount)) {
            $this->sendJson(['error' => 'Nespravne mnozsvi']);
            return;
        }

        $amount = (float) $amount;

        $company = $this->currentCompany;

        if (!$company || !$company->getBankAccount()) {
            $this->sendJson(['error' => 'Firma nema nastaveny IBAN.']);
            return;
        }

        $qrCodeGenerator = new QrCodeGenerator();
        $qrCodeData = $qrCodeGenerator->generate($company->getBankAccount(), $amount, "Platba za objednavku: #{$orderId}");

        $this->sendJson(['qrCode' => $qrCodeData]);
    }

}
