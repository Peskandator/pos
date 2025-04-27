<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Company\Enums\CompanyUserRoles;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Product;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\ProductFormFactory;
use App\Product\Action\DeleteProductAction;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class ProductsPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly ProductFormFactory $addProductFormFactory,
        private readonly DeleteProductAction $deleteProductAction,
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
                'Produkty',
                null)
        );

        $this->template->products = $this->currentCompany->getProducts();
        $this->template->singleProductsOptions = $this->currentCompany->getSingleProducts();
        $this->template->isEditor = in_array(CompanyUserRoles::EDTIOR, $permittedRoles, true);
    }

    protected function createComponentAddProductForm(): Form
    {
        return $this->addProductFormFactory->create($this->currentCompany, null);
    }

    protected function createComponentDeleteProductForm(): Form
    {
        $form = new Form;

        $form
            ->addHidden('id')
            ->setRequired(true)
        ;
        $form->addSubmit('send');

        $form->onValidate[] = function (Form $form, \stdClass $values) {
            $this->checkPermissionsForUser([CompanyUserRoles::EDTIOR]);

            $product = $this->productRepository->find((int)$values->id);

            if (!$product) {
                $form->addError('Produkt nebyl nalezen.');
                $this->flashMessage('Produkt nebyl nalezen.', FlashMessageType::ERROR);
                return;
            }
            $entity = $product->getCompany();
            $form = $this->checkAccessToElementsCompany($form, $entity);
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            /** @var Product $product */
            $product = $this->productRepository->find((int)$values->id);
            $this->deleteProductAction->__invoke($product);
            $this->flashMessage('Produkt byl smazán.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }
}