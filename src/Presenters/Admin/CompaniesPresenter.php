<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Company\Action\AddCompanyUserAction;
use App\Company\Action\CreateCompanyAction;
use App\Company\Action\DeleteCompanyUserAction;
use App\Company\Action\EditCompanyAction;
use App\Company\ORM\CompanyUserRepository;
use App\Company\Requests\CreateCompanyRequest;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Company\Enums\CompanyUserRoles;
use App\Entity\Company;
use App\Entity\CompanyUser;
use App\Entity\User;
use App\Presenters\BaseAdminPresenter;
use App\User\ORM\UserRepository;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;
use Iban\Validation\Iban;
use Iban\Validation\Validator;

final class CompaniesPresenter extends BaseAdminPresenter
{

    public function __construct(
        private readonly CreateCompanyAction $createCompanyAction,
        private readonly EditCompanyAction $editCompanyAction,
        private readonly UserRepository $userRepository,
        private readonly AddCompanyUserAction $addCompanyUserAction,
        private readonly CompanyUserRepository $companyUserRepository,
        private readonly DeleteCompanyUserAction $deleteCompanyUserAction,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Přehled firem',
                null)
        );

        $currentUser = $this->getCurrentUser();
        $this->template->companies = $this->getCompaniesForUser($currentUser);
        $currentCompanyId = 0;

        if (isset($this->currentCompanyId)) {
            $currentCompanyId = $this->currentCompanyId;
        }
        $this->template->currentCompanyId = $currentCompanyId;
        $this->template->signedUser = $currentUser;
    }

    public function actionCreateNew(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Firmy',
                $this->lazyLink(':Admin:Companies:default'))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Nová firma',
                null)
        );
    }

    public function actionEdit(int $companyId): void
    {
        $this->checkCompanyAdmin();
        $editedCompany = $this->companyRepository->find($companyId);

        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Firmy',
                $this->lazyLink(':Admin:Companies:default'))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                $editedCompany->getName(),
                null)
        );
        $this->template->company = $editedCompany;
    }

    public function actionManageUsers(int $companyId): void
    {
        $editedCompany = $this->companyRepository->find($companyId);
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Firmy',
                $this->lazyLink(':Admin:Companies:default'))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                $editedCompany->getName(),
                $this->lazyLink(':Admin:Companies:edit', $editedCompany->getId()))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Správa uživatelů',
                null)
        );


        $currentUser = $this->getCurrentUser();
        $this->template->company = $editedCompany;
        $this->template->companyUsers = $editedCompany->getCompanyUsers();
        $this->template->signedUser = $currentUser;
        $this->template->isCompanyAdmin = $currentUser->isCompanyAdmin($editedCompany);
        $this->template->userRolesTranslations = CompanyUserRoles::getAllRolesWithTransAssoc();
    }

    protected function createComponentCreateCompanyForm(): Form
    {
        $form = new Form;
        $form
            ->addText('name', 'Název')
            ->setRequired()
        ;
        $form
            ->addText('company_id', 'IČO')
        ;

        $form
            ->addText('bank_account', 'Číslo bankovního účtu (IBAN)')
        ;

        $form
            ->addText('country', 'Stát')
            ->setRequired()
        ;
        $form
            ->addText('zip_code', 'PSČ')
            ->setRequired()
        ;
        $form
            ->addText('city', 'Město')
            ->setRequired()
        ;
        $form
            ->addText('street', 'Ulice')
            ->setRequired()
        ;

        $form->addSubmit('send', 'Vytvořit firmu');

        $form->onValidate[] = function (Form $form, \stdClass $values) {
            if (!empty($values->bank_account)) {
                $validator = new Validator();
                $iban = new Iban($values->bank_account);

                if (!$validator->validate($iban)) {
                    $form->addError('Zadaný IBAN není platný.');
                }
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $request = new CreateCompanyRequest(
                $values->name,
                $values->company_id,
                $values->bank_account,
                $values->country,
                $values->city,
                $values->zip_code,
                $values->street
            );
            $newCompanyId = $this->createCompanyAction->__invoke($request);
            $this->flashMessage('Firma byla vytvořena', FlashMessageType::SUCCESS);
            $this->redirect(':Admin:Companies:default', ['currentCompanyId' => $newCompanyId]);
        };

        return $form;
    }

    protected function createComponentEditCompanyForm(): Form
    {
        /**
         * @var Company $company
         */
        $company = $this->template->company;

        $form = new Form;
        $form
            ->addText('name', 'Název')
            ->setRequired()
            ->setDefaultValue($company->getName())
        ;
        $form
            ->addText('company_id', 'IČO')
            ->setDefaultValue($company->getCompanyId())
        ;
        $form
            ->addText('bank_account', 'Číslo bankovního účtu (IBAN)')
            ->setDefaultValue($company->getBankAccount())
        ;
        $form
            ->addText('country', 'Stát')
            ->setRequired()
            ->setDefaultValue($company->getCountry())
        ;
        $form
            ->addText('zip_code', 'PSČ')
            ->setRequired()
            ->setDefaultValue($company->getZipCode())
        ;
        $form
            ->addText('city', 'Město')
            ->setRequired()
            ->setDefaultValue($company->getCity())
        ;
        $form
            ->addText('street', 'Ulice')
            ->setRequired()
            ->setDefaultValue($company->getStreet())
        ;
        $form->addSubmit('send', 'Uložit');

        $form->onValidate[] = function (Form $form, \stdClass $values) {
            if (!empty($values->bank_account)) {
                $validator = new Validator();
                $iban = new Iban($values->bank_account);

                if (!$validator->validate($iban)) {
                    $form->addError('Zadaný IBAN není platný.');
                }
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company) {
            $request = new CreateCompanyRequest(
                $values->name,
                $values->company_id,
                $values->bank_account,
                $values->country,
                $values->city,
                $values->zip_code,
                $values->street
            );
            $this->editCompanyAction->__invoke($request, $company);
            $this->flashMessage('Firma byla upravena', FlashMessageType::SUCCESS);
            $this->redirect(':Admin:Companies:default');
        };

        return $form;
    }

    protected function createComponentAddCompanyUserForm(): Form
    {
        $isCompanyAdmin = $this->template->isCompanyAdmin;

        /**
         * @var Company $editedCompany
         */
        $editedCompany = $this->template->company;

        $form = new Form;
        $form
            ->addEmail('email', 'E-mailová adresa')
            ->setRequired()
        ;

        $roles = CompanyUserRoles::getAllRolesWithoutAdmin();
        $rolesTransnlations = CompanyUserRoles::getAllRolesWithoutAdminTranslations();
        $roles = array_combine($roles, $rolesTransnlations);
        $form
            ->addCheckboxList('roles', 'Role', $roles)
            ->setRequired('Vyberte prosím alespoň jednu roli')
        ;

        $form->addSubmit('send', 'Přidat uživatele');

        $form->onValidate[] = function (Form $form, \stdClass $values) use($editedCompany, $isCompanyAdmin) {
            $usersWithAccess = $editedCompany->getCompanyUsers();
            $addingUser = $this->userRepository->findByEmail($values->email);

            if (!$isCompanyAdmin) {
                $this->addNoPermissionError();
            }

            if ($addingUser === null) {
                $form->addError('Uživatel se zadanou e-mailovou adresou není zaregistrován.');
                $this->flashMessage('Uživatel se zadanou e-mailovou adresou není zaregistrován.', FlashMessageType::ERROR);
                return;
            }

            /**
             * @var CompanyUser $userWithAccess
             */
            foreach ($usersWithAccess as $userWithAccess) {
                if ($userWithAccess->getUser()->getEmail() === $values->email) {
                    $form->addError('Uživatel již má přístup k této firmě.');
                    $this->flashMessage('Uživatel již má přístup k této firmě.', FlashMessageType::ERROR);
                    return;
                }
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use($editedCompany) {

            $addingUser = $this->userRepository->findByEmail($values->email);
            if ($addingUser === null) {
                return;
            }

            $this->addCompanyUserAction->__invoke($editedCompany, $addingUser, $values->roles);

            $this->flashMessage('Uživateli byl přidán přístup.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }

    protected function createComponentDeleteCompanyUserForm(): Form
    {
        $isCompanyAdmin = $this->template->isCompanyAdmin;

        $form = new Form;
        $form->addHidden('company_user_id')
            ->setRequired();
        $form
            ->addSubmit('send');

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($isCompanyAdmin) {
            $companyUser = $this->companyUserRepository->find($values->company_user_id);

            if (!$isCompanyAdmin) {
                $this->addNoPermissionError();
            }

            if (!$companyUser) {
                $form->addError('Uživatel již nemá k této akci přístup.');
                $this->flashMessage('Uživatel již nemá k této akci přístup.', FlashMessageType::ERROR);
                return;
            }

            if ($companyUser->getUser()->getId() === $this->getCurrentUser()->getId()) {
                $form->addError('Uživatele nelze smazat.');
                $this->flashMessage('Uživatele nelze smazat.', FlashMessageType::ERROR);
                return;
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $companyUser = $this->companyUserRepository->find($values->company_user_id);
            if ($companyUser === null) {
                return;
            }
            $this->deleteCompanyUserAction->__invoke($companyUser);
            $this->flashMessage('Uživateli byl odebrán přístup.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }

    protected function getCompaniesForUser(User $user): array
    {
        $entities = [];
        $companyUsers = $user->getCompanyUsers();

        /**
         * @var CompanyUser $companyUser
         */
        foreach ($companyUsers as $companyUser) {
            $entities[] = $companyUser->getCompany();
        }

        return $entities;
    }
}