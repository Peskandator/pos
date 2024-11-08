<?php

namespace App\Product\Forms;

use App\Entity\Company;
use App\Product\Action\AddProductAction;
use App\Product\ORM\CategoryRepository;
use App\Product\ORM\ProductRepository;
use App\Product\Requests\CreateProductRequest;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class AddProductFormFactory
{
    public function __construct(
        private readonly AddProductAction $addProductAction,
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
    )
    {
    }

    public function create(Company $company): Form
    {
        $form = new Form;

        $form
            ->addText('name', 'Název')
            ->setRequired()
        ;
        $form
            ->addInteger('inventory_number', 'Inventární číslo')
            ->setRequired()
        ;
        $form
            ->addText('manufacturer', 'Výrobce')
            ->setNullable()
        ;

        $categories = $company->getCategories();
        $categoriesSelect = $this->getCollectionForSelect($categories);
        $form
            ->addSelect('category', 'Kategorie', $categoriesSelect)
        ;

        $form
            ->addText('price', 'Cena')
            ->addRule($form::FLOAT, 'Zadejte číslo')
            ->setNullable()
            ->addRule($form::MIN, 'Cena musí být nejméně 0', 0)
            ->setRequired()
        ;

        $form
            ->addInteger('vat_rate')
            ->setNullable()
        ;

        $form
            ->addText('description', 'Popis')
            ->setNullable()
        ;

        $form
            ->addCheckbox('is_group', '')
            ->setDefaultValue(false)
        ;

        $form
            ->addHidden('products_in_group')
        ;

        $form->addSubmit('send', 'Přidat');

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company) {
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company) {
            $category = $this->categoryRepository->find($values->category);
            if ($category === null) {
                $errMsg = 'Kategorie nebyla nalezena.';
                $form->addError($errMsg);
                $form->getPresenter()->flashMessage($errMsg,FlashMessageType::ERROR);
            }

            $request = new CreateProductRequest(
                $values->name,
                $values->inventory_number,
                $values->manufacturer,
                $category,
                $values->is_group,
                $values->price,
                $values->vat_rate,
                $values->description,
                new \DateTimeImmutable(),
            );

            $productsInGroup = [];
            if (isset($values->products_in_group)) {
                $products = json_decode($values->products_in_group, true);
                foreach ($products as $productInGroupData) {
                    if ((int)$productInGroupData['product'] === 0) {
                        continue;
                    }
                    $product = $this->productRepository->find($productInGroupData['product']);
                    if ($product === null) {
                        continue;
                    }
                    if ($product->getCompany()->getId() !== $company->getId()) {
                        continue;
                    }

                    $productsInGroup[] = $productInGroupData;
                }
            }

            $this->addProductAction->__invoke($company, $request, $productsInGroup);
            $form->getPresenter()->flashMessage('Produkt byl přidán.', FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }

    protected function getCollectionForSelect(array $array): array
    {
        $items = [];
        $items[0] = 'Vyberte ...';
        foreach ($array as $item) {
            $items[$item->getId()] = $this->createSelectOptionFromItem($item);
        }

        return $items;
    }

    protected function createSelectOptionFromItem($item): string
    {
        return $item->getCode() . ' ' . $item->getName();
    }
}