<?php

namespace App\Template;

use App\Utils\FloatFilter;
use App\Utils\YearsTextFilter;
use App\Utils\PriceFilter;
use App\Utils\SrcDir;
use Nette\Bridges\ApplicationLatte\Template;

class TemplateDecorator
{
    public function __construct(
        private bool $debugMode,
        private SrcDir $srcDir
    ) {
    }

    public function onCreate(Template $template)
    {
        $template->debugMode = $this->debugMode;
        $template->srcDir = $this->srcDir->getDir();
        $template->layout = $this->srcDir->getDir() . '/Presenters/templates/@layout.latte';
        $template->baselayoutPath = $this->srcDir->getDir() . '/Presenters/templates/layout-base.latte';
        $template->adminlayoutPath = $this->srcDir->getDir() . '/Presenters/templates/layout-admin.latte';
        $template->addFilter('yearsText', new YearsTextFilter());
        $template->addFilter('float', new FloatFilter());
        $template->addFilter('price', new PriceFilter());
    }
}
