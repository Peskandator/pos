<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;

final class StatisticsPresenter extends BaseCompanyPresenter
{

    public function __construct(
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Statistika',
                null)
        );
    }

//    protected function createComponentEditProductForm(): Form
//    {
//        $product = $this->template->product;
//        $form = $this->productFormFactory->create($this->currentCompany, $product);
//
//        $this->productFormFactory->fillInForm($form, $product);
//        return $form;
//    }
}