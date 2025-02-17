<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\AddTableFormFactory;
use App\Product\Forms\EditTableFormFactory;
use Nette\Application\UI\Form;

final class TablesPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly AddTableFormFactory $addTableFormFactory,
        private readonly EditTableFormFactory $editTableFormFactory,
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

        $tables = $this->currentCompany->getAllTables();
        bdump($tables);

        $this->template->tables = $tables;
    }

    protected function createComponentAddTableForm(): Form
    {
        return $this->addTableFormFactory->create($this->currentCompany);
    }

    protected function createComponentEditTableForm(): Form
    {
        return $this->editTableFormFactory->create($this->currentCompany);
    }
}