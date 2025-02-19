<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\AddDiningTableFormFactory;
use App\Product\Forms\EditDiningTableFormFactory;
use Nette\Application\UI\Form;

final class DiningTablesPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly AddDiningTableFormFactory $addDiningTableFormFactory,
        private readonly EditDiningTableFormFactory $editDiningTableFormFactory,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Správa produktů',
                null)
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Stoly',
                null)
        );

        $diningTables = $this->currentCompany->getDiningTables();
        $this->template->diningTables = $diningTables;
    }

    protected function createComponentAddDiningTableForm(): Form
    {
        return $this->addDiningTableFormFactory->create($this->currentCompany);
    }

    protected function createComponentEditDiningTableForm(): Form
    {
        return $this->editDiningTableFormFactory->create($this->currentCompany);
    }
}