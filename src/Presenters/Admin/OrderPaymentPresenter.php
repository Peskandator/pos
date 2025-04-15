<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Entity\OrderItemPayment;
use App\Presenters\BaseCompanyPresenter;
use App\Utils\FlashMessageType;
use App\Utils\PriceFilter;
use Nette\Application\UI\Form;
use Doctrine\ORM\EntityManagerInterface;
use App\Product\Services\QrCodeGenerator;

final class OrderPaymentPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PriceFilter $priceFilter,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $orderId): void
    {
        $order = $this->findOrderById($orderId);

        $this->getComponent("breadcrumb")->addItem(
            new BreadcrumbItem(
                "Objednávky",
                $this->lazyLink(":Admin:Orders:default")
            )
        );
        $this->getComponent("breadcrumb")->addItem(
            new BreadcrumbItem((string) $order->getInventoryNumber(), null)
        );

        $this->template->order = $order;
        $this->template->totalPrice = $order->getTotalPrice();
        $this->template->paidAmount = $order->getTotalPaidAmount();
        $this->template->remainingAmount = $order->getRemainingAmountToPay();
        $this->template->bankAccount = $this->currentCompany->getBankAccount();
        $this->template->currentCompanyId = $this->currentCompany->getId();
    }

    protected function createComponentPaymentForm(): Form
    {
        $form = new Form();

        $unpaidItems = [];

        /** @var OrderItem $item */
        foreach ($this->template->order->getOrderItems() as $item) {
            if (!$item->isPaid()) {
                $unpaidItems[$item->getId()] =
                    $item->getProductName()
                    . " (" . $this->priceFilter->__invoke($item->getPrice())
                    . ")";
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

    public function processPayment(Form $form, \stdClass $values): void
    {
        $order = $this->template->order;
        $selectedItems = [];
        $quantities = $this->getHttpRequest()->getPost('quantities') ?? [];

        /** @var OrderItem $item */
        foreach ($order->getOrderItems() as $item) {
            $itemId = $item->getId();
            $requestedQuantity = isset($quantities[$itemId]) ? (int)$quantities[$itemId] : 0;

            if ($requestedQuantity > 0) {
                $selectedItems[] = $itemId;
            }
        }

        if (empty($selectedItems)) {
            $this->flashMessage("Musíte vybrat alespoň jednu položku k platbě.", FlashMessageType::ERROR);
            return;
        }

        $totalToPay = 0;

        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setPaymentTime(new \DateTimeImmutable());
        $payment->setPaymentMethod($values->paymentMethod);

        foreach ($order->getOrderItems() as $item) {
            $itemId = $item->getId();

            if (in_array($itemId, $selectedItems, true)) {
                $requestedQuantity = isset($quantities[$itemId]) ? (int)$quantities[$itemId] : 0;

                if ($requestedQuantity <= 0 || $requestedQuantity > $item->getQuantity() - $item->getPaidQuantity()) {
                    continue;
                }

                $orderItemPayment = null;

                /** @var OrderItemPayment $existingPayment */
                foreach ($item->getOrderItemPayments() as $existingPayment) {
                    if ($existingPayment->getPayment() === $payment) {
                        $orderItemPayment = $existingPayment;
                        break;
                    }
                }

                if (!$orderItemPayment) {
                    $orderItemPayment = new OrderItemPayment($item, $payment);
                    $this->entityManager->persist($orderItemPayment);
                }

                $orderItemPayment->setPaidQuantity($orderItemPayment->getPaidQuantity() + $requestedQuantity);
                $this->entityManager->persist($orderItemPayment);

                $totalToPay += $item->getPrice() * $requestedQuantity;
            }
        }

        if ($totalToPay <= 0) {
            $this->flashMessage("Neplatná částka k úhradě.", FlashMessageType::ERROR);
            return;
        }

        $payment->setAmount($totalToPay);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->flashMessage("Platba byla úspěšně zpracována.", FlashMessageType::SUCCESS);
        $this->redirect("this");
    }

    public function actionGenerateQrCode(int $orderId, int $currentCompanyId, string $amount): void
    {
        if (!is_numeric((float)$amount)) {
            $this->sendJson(['error' => 'Nesprávné množství']);
        }

        $amount = (float) $amount;

        $company = $this->currentCompany;

        if (!$company->getBankAccount()) {
            $this->sendJson(['error' => 'Firma nemá nastavený IBAN.']);
        }

        $qrCodeGenerator = new QrCodeGenerator();
        $qrCodeData = $qrCodeGenerator->generate($company->getBankAccount(), $amount, "Platba za objednávku: #{$orderId}");

        $this->sendJson(['qrCode' => $qrCodeData]);
    }
}
