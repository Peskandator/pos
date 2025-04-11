<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\ProductFormFactory;
use App\Product\Action\DeleteProductAction;
use App\Product\ORM\ProductRepository;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class ProductsPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly ProductFormFactory $addProductFormFactory,
        protected ProductRepository $productRepository, // verify
        private readonly DeleteProductAction $deleteProductAction,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
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
            $product = $this->productRepository->find((int)$values->id);
            $this->deleteProductAction->__invoke($product);
            $this->flashMessage('Produkt byl smazán.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }
}