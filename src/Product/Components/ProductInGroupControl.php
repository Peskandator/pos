<?php

namespace App\Product\Components;

use Nette\Forms\Controls\BaseControl;
use Nette\Localization\Translator;
use Nette\Utils\Html;

class ProductInGroupControl extends BaseControl
{
    private array $subInputs;
    private array $omittedFromValidation;

    public function __construct(
        array $subInputs,
        array $omittedFromValidation,
        $caption = null
    )
    {
        parent::__construct($caption);
        $this->subInputs = $subInputs;
        $this->omittedFromValidation = $omittedFromValidation;
        $this->value = [];
    }

    public function validate(): void
    {
        foreach ($this->value as $record) {
            if (!$this->isValidRecord($record)) {
                $this->addError('error');
            }
        }
    }

    private function isValidRecord(array $record): bool
    {
        foreach ($record as $key => $value) {
            if (in_array($key, $this->omittedFromValidation, true)) {
                continue;
            }
            if (!$value || !is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    public function loadHttpData(): void
    {
        $parsed = [];
        foreach ($this->subInputs as $input) {
            $array = $this->getHttpData(\Nette\Forms\Form::DATA_LINE, "[{$input}][]");
            foreach ($array as $key => $value) {
                $parsed[$key][$input] = $value;
            }
        }

        $filtered = array_filter($parsed, function ($row) {
            return \count(array_filter($row));
        });

        $this->setValue($filtered);
    }

    public function setValue($value): ProductInGroupControl
    {
        $this->value = (array)$value;
        return $this;
    }

    public function getControl()
    {
        $output = '';
        foreach ($this->value as $index => $surface) {
            $output .= $this->getRow($surface);
        }
        $output .= $this->getRow();

        return $output;
    }

    public function getRow(array $values = []): Html
    {
        $isInvalid = !$this->isValidRecord($values);
        $output = '';
        foreach ($this->subInputs as $key => $subInput) {
            $output .= $this->getPart($key, $values[$subInput] ?? null, $isInvalid);
        }

        $buttonTitle = $this->translator->translate('Odstranit produtk ze skupiny');

        return Html::el('div', ['class' => 'row mt-3'])->addHtml($output)->addHtml('
            <div class="col-2 mt-2">
            <i class="fas fa-trash-alt delete-icon text-danger clickable-icon icon-size js-remove-product-in-group" title="' . $buttonTitle . '"></i>
            </div>
        ');
    }

    private function getPart($key, $value, bool $isInvalid): Html
    {
        $name = $this->getHtmlName();

        $attrs = [
            'name' => "{$name}[{$this->subInputs[$key]}][]",
            'type' => 'number',
            'min' => '0',
            'class' => 'form-control' . ($isInvalid ? ' is-invalid' : ''),
            'value' => $value,
        ];
        $control = Html::el('input', $attrs);
        if ($this->disabled) {
            $control->setAttribute('disabled', $this->disabled);
        }

        return Html::el('div', ['class' => 'col-5'])->addHtml($control);
    }
}
