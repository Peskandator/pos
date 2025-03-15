<?php

namespace App\Product\Forms;

use App\Entity\Company;
use App\Entity\Product;
use App\Product\Action\AddProductAction;
use App\Product\Action\EditProductAction;
use App\Product\ORM\CategoryRepository;
use App\Product\ORM\ProductRepository;
use App\Product\Requests\CreateProductRequest;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class ProductFormFactory
{
    public function __construct(
        private readonly AddProductAction $addProductAction,
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
        private readonly EditProductAction $editProductAction,
    )
    {
    }

    public function create(Company $company, ?Product $editedProduct): Form
    {
        $form = new Form;

        $editing = ($editedProduct instanceof Product);

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
            ->addRule($form::Max, 'DPH nemůže být vyšší než 100%', 100)
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

        $submitText = $editing ? 'Upravit' : 'Přidat';
        $form->addSubmit('send', $submitText);

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company, $editedProduct) {
            if ($values->category !== null && $values->category !== 0) {
                $category = $this->categoryRepository->find($values->category);
                if ($category === null) {
                    $errMsg = 'Kategorie nebyla nalezena.';
                    $form->addError($errMsg);
                    $form->getPresenter()->flashMessage($errMsg,FlashMessageType::ERROR);
                }
            }

            if (!$this->isInventoryNumberAvailable($company, $values->inventory_number, $editedProduct)) {
                $errMsg = 'Produkt s tímto inventárním číslem již existuje.';
                $form['inventory_number']->addError($errMsg);
                $form->addError($errMsg);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company, $editing, $editedProduct) {
            $category = $this->categoryRepository->find($values->category);

            $isGroup = $values->is_group;

            $request = new CreateProductRequest(
                $values->name,
                $values->inventory_number,
                $values->manufacturer,
                $category,
                $isGroup,
                $values->price,
                $values->vat_rate,
                $values->description,
                new \DateTimeImmutable(),
            );

            $productsInGroup = [];

            if ($isGroup && isset($values->products_in_group)) {
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

                    if ($productInGroupData['quantity'] === '') {
                        $productInGroupData['quantity'] = 1;
                    }

                    $productsInGroup[] = $productInGroupData;
                }
            }

            $message = 'Produkt byl přidán.';
            if ($editing) {
                $message = 'Produkt byl upraven.';
                $this->editProductAction->__invoke($company, $editedProduct, $request, $productsInGroup);
            } else {
                $this->addProductAction->__invoke($company, $request, $productsInGroup);
            }
            $form->getPresenter()->flashMessage($message, FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }

    public function fillInForm(Form $form, Product $product): Form
    {
        $form->setDefaults([
            'name' => $product->getName(),
            'manufacturer' => $product->getManufacturer(),
            'description' => $product->getDescription(),
            'is_group' => $product->isGroup(),
            'price' => $product->getPrice(),
            'vat_rate' => $product->getVatRate(),
            'inventory_number' => $product->getInventoryNumber(),
        ]);

        $form->setValues(array(
            'category' => $product->getCategory()?->getId(),
        ));

        return $form;
    }


    protected function isInventoryNumberAvailable(Company $company, int $number, ?Product $editedProduct): bool
    {
        $products = $company->getAllProducts();
        /**
         * @var Product $product
         */
        foreach ($products as $product) {
            if ($editedProduct !== null && $editedProduct->getId() === $product->getId()) {
                continue;
            }
            if ($product->getInventoryNumber() === $number) {
                return false;
            }
        }
        return true;
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