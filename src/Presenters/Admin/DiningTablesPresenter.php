<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Company\Enums\CompanyUserRoles;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Action\DeleteDiningTableAction;
use App\Product\Forms\AddDiningTableFormFactory;
use App\Product\Forms\EditDiningTableFormFactory;
use App\Product\ORM\DiningTableRepository;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class DiningTablesPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly AddDiningTableFormFactory $addDiningTableFormFactory,
        private readonly EditDiningTableFormFactory $editDiningTableFormFactory,
        private readonly DiningTableRepository $diningTableRepository,
        private readonly DeleteDiningTableAction $deleteDiningTableAction,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $permittedRoles = $this->checkPermissionsForUser(CompanyUserRoles::getAllRoles());

        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Číselníky',
                null)
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Stoly',
                null)
        );

        $diningTables = $this->currentCompany->getDiningTables();
        $this->template->diningTables = $diningTables;
        $this->template->isEditor = in_array(CompanyUserRoles::EDTIOR, $permittedRoles, true);
    }

    protected function createComponentAddDiningTableForm(): Form
    {
        return $this->addDiningTableFormFactory->create($this->currentCompany);
    }

    protected function createComponentEditDiningTableForm(): Form
    {
        return $this->editDiningTableFormFactory->create($this->currentCompany);
    }

    protected function createComponentDeleteDiningTableForm(): Form
    {
        $form = new Form;

        $form
            ->addHidden('id')
            ->setRequired(true)
        ;
        $form->addSubmit('send');

        $form->onValidate[] = function (Form $form, \stdClass $values) {
            $this->checkPermissionsForUser([CompanyUserRoles::EDTIOR]);

            $diningTable = $this->diningTableRepository->find((int)$values->id);

            if (!$diningTable) {
                $form->addError('Stůl nebyl nalezen.');
                $this->flashMessage('Stůl nebyl nalezen.', FlashMessageType::ERROR);
                return;
            }
            $entity = $diningTable->getCompany();
            $form = $this->checkAccessToElementsCompany($form, $entity);
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $diningTable = $this->diningTableRepository->find((int)$values->id);
            $this->deleteDiningTableAction->__invoke($diningTable);
            $this->flashMessage('Stůl byl smazán.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }
}