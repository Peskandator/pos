<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Entity\Payment;
use App\Order\ORM\OrderRepository;
use App\Presenters\BaseCompanyPresenter;
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
        $selectedItems = $values->items;

        if (empty($selectedItems)) {
            $this->flashMessage("Musíte vybrat alespoň jednu položku k platbě.", "danger");
            return;
        }

        $totalToPay = 0;
        foreach ($order->getOrderItems() as $item) {
            if (in_array($item->getId(), $selectedItems) && !$item->isPaid()) {
                $item->markAsPaid();
                $totalToPay += $item->getPriceIncludingVat() * $item->getQuantity();

            }
        }

        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setAmount($totalToPay);
        $payment->setPaymentTime(new \DateTimeImmutable());
        $payment->setPaymentMethod($values->paymentMethod);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->flashMessage("Platba byla úspěšně zpracována.", "success");
        $this->redirect("this");
    }

    public function handleGenerateQrCode(int $orderId, float $amount): void
    {
        $order = $this->orderRepository->find($orderId);
    
        if (!$order) {
            $this->sendJson(['error' => 'Order not found']);
            return;
        }
    
        $company = $this->currentCompany;
        $iban = $company->getBankAccount();
    
        $qrCodeGenerator = new QrCodeGenerator();
        $qrCodeDataUri = $qrCodeGenerator->generate($iban, $amount, "Platba za objednavku: {$orderId}");
    
        $this->sendJson([
            'qrCodeDataUri' => $qrCodeDataUri,
            'amount' => $amount,
            'iban' => $iban
        ]);
    }

}
