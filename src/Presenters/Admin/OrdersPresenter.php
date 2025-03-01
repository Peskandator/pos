<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Order\Forms\OrderFormFactory;
use App\Presenters\BaseCompanyPresenter;
use Nette\Application\UI\Form;

final class OrdersPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly OrderFormFactory $orderFormFactory,
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
        return $this->orderFormFactory->create($this->currentCompany, null);
    }
}